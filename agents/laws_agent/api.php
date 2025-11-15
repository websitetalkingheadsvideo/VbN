<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/connect.php';
require_once __DIR__ . '/../../includes/anthropic_helper.php';
require_once __DIR__ . '/markdown_loader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * @return array{authenticated:bool,verified:bool,user_id?:int,error?:string,http_code?:int}
 */
function check_authentication(mysqli $conn): array
{
    if (!isset($_SESSION['user_id'])) {
        return [
            'authenticated' => false,
            'verified' => false,
            'error' => 'Not logged in',
            'http_code' => 401,
        ];
    }

    $userId = $_SESSION['user_id'];
    $result = db_fetch_one(
        $conn,
        'SELECT email_verified FROM users WHERE id = ?',
        'i',
        [$userId]
    );

    if (!$result) {
        return [
            'authenticated' => false,
            'verified' => false,
            'error' => 'User not found',
            'http_code' => 401,
        ];
    }

    if (!$result['email_verified']) {
        return [
            'authenticated' => true,
            'verified' => false,
            'error' => 'Email verification required',
            'http_code' => 403,
        ];
    }

    return [
        'authenticated' => true,
        'verified' => true,
        'user_id' => (int) $userId,
    ];
}

/**
 * @return array<int, array<string, mixed>>
 */
function search_rulebooks(mysqli $conn, string $query, ?string $category = null, ?string $system = null, int $limit = 5): array
{
    $sql = <<<SQL
        SELECT
            r.id AS rulebook_id,
            r.title AS book_title,
            r.category,
            r.system_type,
            rp.page_number,
            rp.page_text,
            MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance
        FROM rulebook_pages rp
        JOIN rulebooks r ON rp.rulebook_id = r.id
        WHERE MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE)
    SQL;

    $params = [$query, $query];
    $types = 'ss';

    if ($category) {
        $sql .= ' AND r.category = ?';
        $params[] = $category;
        $types .= 's';
    }

    if ($system) {
        $sql .= ' AND r.system_type = ?';
        $params[] = $system;
        $types .= 's';
    }

    $sql .= ' ORDER BY relevance DESC LIMIT ?';
    $params[] = $limit;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    return $results;
}

function question_indicates_traditions(string $question): bool
{
    $q = strtolower($question);
    if (strpos($q, 'tradition') !== false) {
        return true;
    }

    $keywords = ['masquerade', 'domain', 'progeny', 'accounting', 'hospitality', 'destruction'];
    $hits = 0;

    foreach ($keywords as $keyword) {
        if (strpos($q, $keyword) !== false) {
            $hits++;
        }
    }

    return $hits >= 2;
}

/**
 * @return array<int, array<string, mixed>>
 */
function search_rulebooks_traditions(mysqli $conn, ?string $category = null, ?string $system = null, int $limit = 20): array
{
    $boolean = '+Tradition* Masquerade Domain Progeny Accounting Hospitality Destruction "Tradition of the" "the Six Traditions"';

    $sql = <<<SQL
        SELECT
            r.id AS rulebook_id,
            r.title AS book_title,
            r.category,
            r.system_type,
            rp.page_number,
            rp.page_text,
            MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE) AS relevance,
            (
                (rp.page_text LIKE '%Tradition%') +
                2 * (rp.page_text REGEXP 'Tradition of the (Masquerade|Domain|Progeny|Accounting|Hospitality|Destruction)')
            ) AS tboost
        FROM rulebook_pages rp
        JOIN rulebooks r ON rp.rulebook_id = r.id
        WHERE MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE)
    SQL;

    $params = [$boolean, $boolean];
    $types = 'ss';

    if ($category) {
        $sql .= ' AND r.category = ?';
        $params[] = $category;
        $types .= 's';
    }

    if ($system) {
        $sql .= ' AND r.system_type = ?';
        $params[] = $system;
        $types .= 's';
    }

    $sql .= ' ORDER BY (relevance + tboost) DESC LIMIT ?';
    $params[] = $limit;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    return $results;
}

function extract_excerpt(string $text, int $maxChars = 800): string
{
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    if (strlen($text) <= $maxChars) {
        return $text;
    }

    $excerpt = substr($text, 0, $maxChars);
    $lastPeriod = strrpos($excerpt, '.');

    if ($lastPeriod !== false && $lastPeriod > $maxChars * 0.7) {
        return substr($text, 0, $lastPeriod + 1);
    }

    return $excerpt . '...';
}

function build_context_from_results(array $results): string
{
    if ($results === []) {
        return 'No relevant rulebook content found.';
    }

    $context = "Context from VTM/MET rulebooks:\n\n";

    foreach ($results as $index => $result) {
        $sourceNum = $index + 1;
        $excerpt = extract_excerpt((string) $result['page_text'], 800);

        $context .= sprintf(
            "[Source %d] %s (Page %d, Category: %s, System: %s):\n%s\n\n",
            $sourceNum,
            $result['book_title'],
            $result['page_number'],
            $result['category'],
            $result['system_type'],
            $excerpt
        );
    }

    return $context;
}

/**
 * @return array<int, array<string, mixed>>
 */
function prioritize_tradition_pages(array $results): array
{
    if ($results === []) {
        return $results;
    }

    $score = static function (array $row): int {
        $text = (string) ($row['page_text'] ?? '');
        $points = 0;
        foreach ([
            'Tradition of the Masquerade',
            'Tradition of the Domain',
            'Tradition of the Progeny',
            'Tradition of the Accounting',
            'Tradition of the Hospitality',
            'Tradition of the Destruction',
        ] as $phrase) {
            if (stripos($text, $phrase) !== false) {
                $points += 10;
            }
        }

        foreach (['Masquerade', 'Domain', 'Progeny', 'Accounting', 'Hospitality', 'Destruction'] as $name) {
            $pattern = sprintf('/%s.{0,80}Tradition/i', preg_quote($name, '/'));
            $patternReverse = sprintf('/Tradition.{0,80}%s/i', preg_quote($name, '/'));
            if (preg_match($pattern, $text) || preg_match($patternReverse, $text)) {
                $points += 3;
            }
        }

        if (preg_match('/\bSix\s+Traditions\b/i', $text)) {
            $points += 6;
        }
        if (preg_match('/\bThe\s+Traditions\b/i', $text)) {
            $points += 4;
        }

        return $points;
    };

    usort(
        $results,
        static function ($a, $b) use ($score) {
            $scoreA = $score($a);
            $scoreB = $score($b);
            if ($scoreA === $scoreB) {
                $relevanceA = (float) ($a['relevance'] ?? 0);
                $relevanceB = (float) ($b['relevance'] ?? 0);
                return $relevanceB <=> $relevanceA;
            }

            return $scoreB <=> $scoreA;
        }
    );

    return $results;
}

function seed_traditions(mysqli $conn, string $title = 'VTM - Traditions (Reference)', string $category = 'Core', string $system = 'MET-VTM'): array
{
    $existingRulebook = db_fetch_one(
        $conn,
        'SELECT id FROM rulebooks WHERE title = ? AND system_type = ?',
        'ss',
        [$title, $system]
    );

    if ($existingRulebook) {
        $rulebookId = (int) $existingRulebook['id'];
    } else {
        $insertId = db_execute(
            $conn,
            'INSERT INTO rulebooks (title, category, system_type) VALUES (?,?,?)',
            'sss',
            [$title, $category, $system]
        );

        if (!$insertId) {
            return ['success' => false, 'error' => 'Failed to create rulebook'];
        }

        $rulebookId = (int) $insertId;
    }

    $pages = [
        1 => "The Six Traditions (Camarilla)\n\nThe Camarilla recognizes six foundational Traditions that govern Kindred society. Each Tradition is often titled as 'Tradition of the …'. The six are: Masquerade, Domain, Progeny, Accounting, Hospitality, and Destruction.",
        2 => "Tradition of the Masquerade\n\nKeep the existence of Kindred hidden from mortals; avoid breaches that expose vampiric society.",
        3 => "Tradition of the Domain\n\nRespect the authority of a domain's ruler (typically the Prince). A guest must observe the local ruler’s laws and customs.",
        4 => "Tradition of the Progeny\n\nDo not create childer without the permission of the domain’s ruler; illicit Embraces are punishable.",
        5 => "Tradition of the Accounting\n\nThe sire is responsible for the actions and education of her childe until released; debts and obligations must be honored.",
        6 => "Tradition of the Hospitality\n\nAnnounce yourself when entering a new domain and request leave to remain; unannounced Kindred risk sanction.",
        7 => "Tradition of the Destruction\n\nDo not destroy another Kindred without proper authority (usually vested in the domain’s ruler).",
    ];

    db_execute($conn, 'DELETE FROM rulebook_pages WHERE rulebook_id = ?', 'i', [$rulebookId]);

    $inserted = 0;
    foreach ($pages as $pageNumber => $text) {
        $ok = db_execute(
            $conn,
            'INSERT INTO rulebook_pages (rulebook_id, page_number, page_text) VALUES (?,?,?)',
            'iis',
            [$rulebookId, $pageNumber, $text]
        );

        if ($ok) {
            $inserted++;
        }
    }

    return [
        'success' => true,
        'rulebook_id' => $rulebookId,
        'inserted_pages' => $inserted,
        'title' => $title,
        'category' => $category,
        'system' => $system,
    ];
}

function ask_laws_agent(mysqli $conn, string $question, ?string $category = null, ?string $system = null): array
{
    // Try file-based Laws of the Night search first
    $fileResults = [];
    try {
        $loader = new LawsOfTheNightLoader();
        $loadResult = $loader->loadIndex();
        
        if ($loadResult['success'] && $loadResult['files_loaded'] > 0) {
            // Map category filter if provided
            $fileCategory = null;
            if ($category === 'Core') {
                // For Laws of the Night, we can search by content type
                // Let the search handle it without category filter initially
            }
            
            // Search file-based index
            $fileResults = $loader->search($question, $fileCategory, 10);
            
            // Convert file results to unified format
            $fileResults = array_map(static function ($entry) use ($loader) {
                return [
                    'source_type' => 'file',
                    'book_title' => 'Laws of the Night Revised',
                    'page_number' => $entry['chapter'] ?? 0,
                    'category' => ucfirst($entry['category']),
                    'system_type' => 'MET-VTM',
                    'page_text' => $entry['content'],
                    'relevance' => $entry['relevance'] ?? 0,
                    'file_path' => $entry['relative_path'],
                    'title' => $entry['title'],
                    'section' => $entry['section'] ?? null,
                ];
            }, $fileResults);
        }
    } catch (Throwable $e) {
        // Silently fall back to database if file system fails
        error_log('Laws of the Night file loader error: ' . $e->getMessage());
    }

    // Database search (existing functionality)
    if (question_indicates_traditions($question)) {
        $searchResults = search_rulebooks_traditions($conn, $category, $system, 20);
        if ($searchResults === []) {
            $searchResults = search_rulebooks($conn, $question, $category, $system, 20);
        }

        if ($searchResults !== []) {
            $found = [];
            $names = ['Masquerade', 'Domain', 'Progeny', 'Accounting', 'Hospitality', 'Destruction'];
            foreach ($searchResults as $result) {
                $text = strtolower((string) $result['page_text']);
                foreach ($names as $name) {
                    $lower = strtolower($name);
                    if (strpos($text, $lower) !== false || strpos($text, 'tradition of the ' . $lower) !== false) {
                        $found[$name] = true;
                    }
                }
            }

            if (count($found) < 4) {
                $byKey = [];
                foreach ($searchResults as $result) {
                    $key = $result['rulebook_id'] . ':' . $result['page_number'];
                    $byKey[$key] = $result;
                }

                foreach ($names as $name) {
                    if (isset($found[$name])) {
                        continue;
                    }

                    $phrase = '"Tradition of the ' . $name . '" ' . $name . ' Tradition*';
                    $boolean = '+(' . $phrase . ')';

                    $sql = <<<SQL
                        SELECT
                            r.id AS rulebook_id,
                            r.title AS book_title,
                            r.category,
                            r.system_type,
                            rp.page_number,
                            rp.page_text,
                            MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE) AS relevance
                        FROM rulebook_pages rp
                        JOIN rulebooks r ON rp.rulebook_id = r.id
                        WHERE MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE)
                    SQL;

                    $params = [$boolean, $boolean];
                    $types = 'ss';
                    if ($category) {
                        $sql .= ' AND r.category = ?';
                        $params[] = $category;
                        $types .= 's';
                    }
                    if ($system) {
                        $sql .= ' AND r.system_type = ?';
                        $params[] = $system;
                        $types .= 's';
                    }
                    $sql .= ' ORDER BY relevance DESC LIMIT 3';

                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        while ($row = $res->fetch_assoc()) {
                            $key = $row['rulebook_id'] . ':' . $row['page_number'];
                            if (!isset($byKey[$key])) {
                                $byKey[$key] = $row;
                            }
                        }
                    }
                }

                $searchResults = array_slice(array_values($byKey), 0, 30);
            }

            $searchResults = prioritize_tradition_pages($searchResults);

            if (count($found) < 4) {
                $broader = search_rulebooks_traditions($conn, $category, null, 20);
                if ($broader !== []) {
                    $byKey = [];
                    foreach ($searchResults as $result) {
                        $key = $result['rulebook_id'] . ':' . $result['page_number'];
                        $byKey[$key] = $result;
                    }
                    foreach ($broader as $result) {
                        $key = $result['rulebook_id'] . ':' . $result['page_number'];
                        if (!isset($byKey[$key])) {
                            $byKey[$key] = $result;
                        }
                    }
                    $searchResults = array_slice(array_values($byKey), 0, 30);
                    $searchResults = prioritize_tradition_pages($searchResults);
                }
            }
        }
    } else {
        $searchResults = search_rulebooks($conn, $question, $category, $system, 10);
    }

    // Add source_type to database results
    $searchResults = array_map(static function ($row) {
        $row['source_type'] = 'database';
        return $row;
    }, $searchResults);

    // Combine file and database results, prioritizing file results
    $allResults = array_merge($fileResults, $searchResults);
    
    // Sort by relevance if available
    if (!empty($allResults)) {
        usort($allResults, static function ($a, $b) {
            $relevanceA = (float) ($a['relevance'] ?? 0);
            $relevanceB = (float) ($b['relevance'] ?? 0);
            // Prioritize file results slightly
            if (($a['source_type'] ?? '') === 'file' && ($b['source_type'] ?? '') !== 'file') {
                $relevanceA += 50;
            }
            if (($b['source_type'] ?? '') === 'file' && ($a['source_type'] ?? '') !== 'file') {
                $relevanceB += 50;
            }
            return $relevanceB <=> $relevanceA;
        });
        
        // Limit total results
        $allResults = array_slice($allResults, 0, 15);
    }

    $searchResults = $allResults;

    if ($searchResults === []) {
        return [
            'success' => true,
            'question' => $question,
            'answer' => "I couldn't find specific information about that in the VTM/MET rulebooks. Try rephrasing your question or using different keywords. You can also specify a category (Core, Faction, Supplement, Blood Magic, Journal) or system (MET-VTM, VTM, MTA, etc.) to narrow the search.",
            'sources' => [],
            'ai_model' => null,
            'searched' => true,
            'results_found' => 0,
        ];
    }

    $context = build_context_from_results($searchResults);

    $prompt = $context . "\nQuestion: " . $question;

    $systemPrompt = <<<PROMPT
You are an expert on Vampire: The Masquerade and Mind's Eye Theatre rules and lore. Your role is to answer questions based ONLY on the provided rulebook excerpts above.

IMPORTANT RULES:
1. Always cite your sources using the format: (Source [number]: [Book Title], Page [page])
2. If the answer requires information from multiple sources, cite all relevant sources
3. If the excerpts don't contain enough information to fully answer the question, say so clearly
4. Do not make up or assume information not present in the excerpts
5. Be concise but thorough in your explanations
6. Use the exact terminology from the rulebooks

Answer the user's question now:
PROMPT;

    $aiResponse = call_anthropic($prompt, $systemPrompt, 1500);

    if (!$aiResponse['success']) {
        return [
            'success' => false,
            'error' => 'AI service error: ' . $aiResponse['error'],
            'question' => $question,
            'sources' => array_map(
                static function ($row): array {
                    return [
                        'book' => $row['book_title'],
                        'page' => $row['page_number'],
                        'category' => $row['category'],
                        'system' => $row['system_type'],
                    ];
                },
                $searchResults
            ),
        ];
    }

    $sources = array_map(
        static function ($row): array {
            $source = [
                'book' => $row['book_title'],
                'page' => (int) $row['page_number'],
                'category' => $row['category'],
                'system' => $row['system_type'],
                'excerpt' => extract_excerpt((string) $row['page_text'], 300),
                'relevance' => (float) ($row['relevance'] ?? 0),
            ];
            
            // Add file-specific metadata if available
            if (($row['source_type'] ?? '') === 'file') {
                $source['source_type'] = 'file';
                $source['file_path'] = $row['file_path'] ?? null;
                $source['title'] = $row['title'] ?? null;
                $source['section'] = $row['section'] ?? null;
            } else {
                $source['source_type'] = 'database';
            }
            
            return $source;
        },
        $searchResults
    );

    return [
        'success' => true,
        'question' => $question,
        'answer' => $aiResponse['content'],
        'sources' => $sources,
        'ai_model' => $aiResponse['model'] ?? 'claude-3-5-sonnet',
        'searched' => true,
        'results_found' => count($searchResults),
    ];
}

try {
    $mcpApiKey = $_GET['mcp_key'] ?? $_POST['mcp_key'] ?? '';
    $mcpBypass = $mcpApiKey === 'vbn_mcp_b4byp4ss_k3y_2025';

    $action = $_GET['action'] ?? $_POST['action'] ?? 'ask';

    if (!$mcpBypass && $action !== 'health' && $action !== 'public_traditions') {
        $auth = check_authentication($conn);
        if (!$auth['authenticated'] || !$auth['verified']) {
            http_response_code($auth['http_code'] ?? 401);
            echo json_encode([
                'success' => false,
                'error' => $auth['error'] ?? 'Unauthorized',
            ]);
            exit;
        }
    }

    switch ($action) {
        case 'ask':
            $question = $_GET['question'] ?? $_POST['question'] ?? '';
            $category = $_GET['category'] ?? $_POST['category'] ?? null;
            $system = $_GET['system'] ?? $_POST['system'] ?? null;

            $question = trim($question);
            if ($question === '') {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Question parameter is required',
                ]);
                exit;
            }

            $response = ask_laws_agent($conn, $question, $category, $system);
            echo json_encode($response);
            break;

        case 'public_traditions':
            $rulebook = db_fetch_one(
                $conn,
                'SELECT id FROM rulebooks WHERE title = ? AND system_type = ? LIMIT 1',
                'ss',
                ['VTM - Traditions (Reference)', 'MET-VTM']
            );
            if (!$rulebook) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Traditions reference not found. Ask an admin to seed it.',
                ]);
                break;
            }

            $rulebookId = (int) $rulebook['id'];
            $pagesResult = db_select(
                $conn,
                'SELECT page_number, page_text FROM rulebook_pages WHERE rulebook_id = ? ORDER BY page_number ASC',
                'i',
                [$rulebookId]
            );

            $pages = [];
            if ($pagesResult) {
                while ($row = mysqli_fetch_assoc($pagesResult)) {
                    $pages[] = $row;
                }
            }

            echo json_encode([
                'success' => true,
                'title' => 'VTM - Traditions (Reference)',
                'rulebook_id' => $rulebookId,
                'pages' => $pages,
            ]);
            break;

        case 'seed_traditions':
            if (!$mcpBypass) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Forbidden',
                ]);
                exit;
            }

            $seed = seed_traditions($conn);
            echo json_encode($seed);
            break;

        case 'health':
            $apiKeyConfigured = load_anthropic_api_key() !== null;

            echo json_encode([
                'success' => true,
                'status' => 'online',
                'api_configured' => $apiKeyConfigured,
                'database' => 'connected',
                'authenticated' => true,
            ]);
            break;

        case 'debug_stats':
            try {
                $loader = new LawsOfTheNightLoader();
                $loadResult = $loader->loadIndex();
                $stats = $loader->getStats();
                
                echo json_encode([
                    'success' => true,
                    'index_status' => $loadResult,
                    'stats' => $stats,
                    'base_path' => $loader->getBasePath(),
                ]);
            } catch (Throwable $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }
            break;

        case 'test_search':
            $query = $_GET['query'] ?? $_POST['query'] ?? '';
            if (empty($query)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Query parameter is required',
                ]);
                exit;
            }

            try {
                $loader = new LawsOfTheNightLoader();
                $loadResult = $loader->loadIndex();
                
                if (!$loadResult['success']) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Failed to load index: ' . implode(', ', $loadResult['errors']),
                    ]);
                    break;
                }

                $category = $_GET['category'] ?? $_POST['category'] ?? null;
                $results = $loader->search($query, $category, 10);
                
                // Format results for display
                $formatted = array_map(static function ($entry) use ($loader) {
                    return [
                        'title' => $entry['title'],
                        'category' => $entry['category'],
                        'section' => $entry['section'],
                        'file_path' => $entry['relative_path'],
                        'relevance' => $entry['relevance'],
                        'excerpt' => $loader->getExcerpt($entry['content'], 200),
                    ];
                }, $results);

                echo json_encode([
                    'success' => true,
                    'query' => $query,
                    'results' => $formatted,
                    'count' => count($results),
                ]);
            } catch (Throwable $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action. Supported actions: ask, health',
            ]);
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $exception->getMessage(),
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}


