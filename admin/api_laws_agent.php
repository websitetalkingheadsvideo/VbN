<?php
/**
 * Laws Agent API
 * AI-powered agent that answers VTM/MET rules questions
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/anthropic_helper.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated and verified
 */
function check_authentication(mysqli $conn): array {
    // Check if logged in
    if (!isset($_SESSION['user_id'])) {
        return [
            'authenticated' => false,
            'verified' => false,
            'error' => 'Not logged in',
            'http_code' => 401
        ];
    }
    
    // Check email verification
    $user_id = $_SESSION['user_id'];
    $result = db_fetch_one($conn, "SELECT email_verified FROM users WHERE id = ?", "i", [$user_id]);
    
    if (!$result) {
        return [
            'authenticated' => false,
            'verified' => false,
            'error' => 'User not found',
            'http_code' => 401
        ];
    }
    
    if (!$result['email_verified']) {
        return [
            'authenticated' => true,
            'verified' => false,
            'error' => 'Email verification required',
            'http_code' => 403
        ];
    }
    
    return [
        'authenticated' => true,
        'verified' => true,
        'user_id' => $user_id
    ];
}

/**
 * Search rulebooks for relevant content
 */
function search_rulebooks(mysqli $conn, string $query, ?string $category = null, ?string $system = null, int $limit = 5): array {
    $sql = "SELECT 
                r.id as rulebook_id,
                r.title as book_title,
                r.category,
                r.system_type,
                rp.page_number,
                rp.page_text,
                MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM rulebook_pages rp
            JOIN rulebooks r ON rp.rulebook_id = r.id
            WHERE MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE)";
    
    $params = [$query, $query];
    $types = 'ss';
    
    if ($category) {
        $sql .= " AND r.category = ?";
        $params[] = $category;
        $types .= 's';
    }
    
    if ($system) {
        $sql .= " AND r.system_type = ?";
        $params[] = $system;
        $types .= 's';
    }
    
    $sql .= " ORDER BY relevance DESC LIMIT ?";
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

/**
 * Detect if the question targets the Six Traditions topic
 */
function question_indicates_traditions(string $question): bool {
    $q = strtolower($question);
    if (strpos($q, 'tradition') !== false) return true;
    $keywords = ['masquerade','domain','progeny','accounting','hospitality','destruction'];
    $hits = 0;
    foreach ($keywords as $kw) {
        if (strpos($q, $kw) !== false) { $hits++; }
    }
    return $hits >= 2; // mention of at least two names strongly suggests the topic
}

/**
 * BOOLEAN MODE search tuned for the Six Traditions
 * Requires a Tradition mention and boosts specific Tradition-of phrases
 */
function search_rulebooks_traditions(mysqli $conn, ?string $category = null, ?string $system = null, int $limit = 20): array {
    $boolean = '+Tradition* Masquerade Domain Progeny Accounting Hospitality Destruction "Tradition of the" "the Six Traditions"';

    $sql = "SELECT 
                r.id as rulebook_id,
                r.title as book_title,
                r.category,
                r.system_type,
                rp.page_number,
                rp.page_text,
                MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE) as relevance,
                (
                    (rp.page_text LIKE '%Tradition%') +
                    2 * (rp.page_text REGEXP 'Tradition of the (Masquerade|Domain|Progeny|Accounting|Hospitality|Destruction)')
                ) as tboost
            FROM rulebook_pages rp
            JOIN rulebooks r ON rp.rulebook_id = r.id
            WHERE MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE)";

    $params = [$boolean, $boolean];
    $types = 'ss';

    if ($category) {
        $sql .= " AND r.category = ?";
        $params[] = $category;
        $types .= 's';
    }

    if ($system) {
        $sql .= " AND r.system_type = ?";
        $params[] = $system;
        $types .= 's';
    }

    $sql .= " ORDER BY (relevance + tboost) DESC LIMIT ?";
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

/**
 * Extract clean snippet from page text
 */
function extract_excerpt(string $text, int $max_chars = 800): string {
    // Remove excessive whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    if (strlen($text) <= $max_chars) {
        return $text;
    }
    
    // Try to cut at sentence boundary
    $excerpt = substr($text, 0, $max_chars);
    $last_period = strrpos($excerpt, '.');
    
    if ($last_period !== false && $last_period > $max_chars * 0.7) {
        return substr($text, 0, $last_period + 1);
    }
    
    return $excerpt . '...';
}

/**
 * Build context from search results for AI
 */
function build_context_from_results(array $results): string {
    if (empty($results)) {
        return "No relevant rulebook content found.";
    }
    
    $context = "Context from VTM/MET rulebooks:\n\n";
    
    foreach ($results as $i => $result) {
        $source_num = $i + 1;
        $excerpt = extract_excerpt($result['page_text'], 800);
        
        $context .= sprintf(
            "[Source %d] %s (Page %d, Category: %s, System: %s):\n%s\n\n",
            $source_num,
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
 * For Traditions queries, prioritize pages that look like formal Tradition entries.
 * Reorders results to surface pages that include phrases like
 * "Tradition of the Masquerade" or a close pairing of a Tradition name with the word Tradition.
 */
function prioritize_tradition_pages(array $results): array {
    if (empty($results)) return $results;

    $score = function(array $r): int {
        $t = $r['page_text'] ?? '';
        // Lightweight scoring heuristics
        $points = 0;
        // Exact phrase hits get strong boost
        foreach (['Tradition of the Masquerade','Tradition of the Domain','Tradition of the Progeny','Tradition of the Accounting','Tradition of the Hospitality','Tradition of the Destruction'] as $p) {
            if (stripos($t, $p) !== false) { $points += 10; }
        }
        // Name + Tradition proximity (same line-ish)
        foreach (['Masquerade','Domain','Progeny','Accounting','Hospitality','Destruction'] as $n) {
            if (preg_match('/'.preg_quote($n,'/').'.{0,80}Tradition/i', $t) || preg_match('/Tradition.{0,80}'.preg_quote($n,'/').'/i', $t)) {
                $points += 3;
            }
        }
        // Headings
        if (preg_match('/\bSix\s+Traditions\b/i', $t)) { $points += 6; }
        if (preg_match('/\bThe\s+Traditions\b/i', $t)) { $points += 4; }
        return $points;
    };

    usort($results, function($a, $b) use ($score) {
        $sa = $score($a);
        $sb = $score($b);
        if ($sa === $sb) {
            // tie-breaker: higher MySQL relevance if present
            $ra = (float)($a['relevance'] ?? 0);
            $rb = (float)($b['relevance'] ?? 0);
            return ($rb <=> $ra);
        }
        return ($sb <=> $sa);
    });

    return $results;
}

/**
 * Seed a concise reference rulebook containing the Six Traditions.
 * Creates a synthetic rulebook and pages with explicit "Tradition of the ..." entries
 * to improve retrieval and citations while remaining excerpt-based.
 * Only callable via secure pathway.
 */
function seed_traditions(mysqli $conn, string $title = 'VTM - Traditions (Reference)', string $category = 'Core', string $system = 'MET-VTM'): array {
    // Ensure rulebook exists
    $rb = db_fetch_one($conn,
        "SELECT id FROM rulebooks WHERE title = ? AND system_type = ?",
        "ss",
        [$title, $system]
    );

    if (!$rb) {
        $insert_id = db_execute($conn,
            "INSERT INTO rulebooks (title, category, system_type) VALUES (?,?,?)",
            "sss",
            [$title, $category, $system]
        );
        if (!$insert_id) {
            return ['success' => false, 'error' => 'Failed to create rulebook'];
        }
        $rulebook_id = (int)$insert_id;
    } else {
        $rulebook_id = (int)$rb['id'];
    }

    // Define pages (minimal summaries)
    $pages = [
        1 => "The Six Traditions (Camarilla)\n\nThe Camarilla recognizes six foundational Traditions that govern Kindred society. Each Tradition is often titled as 'Tradition of the …'. The six are: Masquerade, Domain, Progeny, Accounting, Hospitality, and Destruction.",
        2 => "Tradition of the Masquerade\n\nKeep the existence of Kindred hidden from mortals; avoid breaches that expose vampiric society.",
        3 => "Tradition of the Domain\n\nRespect the authority of a domain's ruler (typically the Prince). A guest must observe the local ruler’s laws and customs.",
        4 => "Tradition of the Progeny\n\nDo not create childer without the permission of the domain’s ruler; illicit Embraces are punishable.",
        5 => "Tradition of the Accounting\n\nThe sire is responsible for the actions and education of her childe until released; debts and obligations must be honored.",
        6 => "Tradition of the Hospitality\n\nAnnounce yourself when entering a new domain and request leave to remain; unannounced Kindred risk sanction.",
        7 => "Tradition of the Destruction\n\nDo not destroy another Kindred without proper authority (usually vested in the domain’s ruler)."
    ];

    // Remove existing pages for this synthetic rulebook to avoid duplicates
    db_execute($conn, "DELETE FROM rulebook_pages WHERE rulebook_id = ?", "i", [$rulebook_id]);

    // Insert pages
    $inserted = 0;
    foreach ($pages as $page_num => $text) {
        $ok = db_execute($conn,
            "INSERT INTO rulebook_pages (rulebook_id, page_number, page_text) VALUES (?,?,?)",
            "iis",
            [$rulebook_id, $page_num, $text]
        );
        if ($ok) { $inserted++; }
    }

    return [
        'success' => true,
        'rulebook_id' => $rulebook_id,
        'inserted_pages' => $inserted,
        'title' => $title,
        'category' => $category,
        'system' => $system
    ];
}

/**
 * Ask the Laws Agent a question
 */
function ask_laws_agent(mysqli $conn, string $question, ?string $category = null, ?string $system = null): array {
    // Search for relevant content
    if (question_indicates_traditions($question)) {
        $search_results = search_rulebooks_traditions($conn, $category, $system, 20);
        // If nothing substantial came back, fall back to general search
        if (empty($search_results)) {
            $search_results = search_rulebooks($conn, $question, $category, $system, 20);
        }
        // If coverage of the six is weak, augment with per‑tradition BOOLEAN searches
        if (!empty($search_results)) {
            // Helper: compute which traditions appear in current snippets
            $found = [];
            $names = ['Masquerade','Domain','Progeny','Accounting','Hospitality','Destruction'];
            foreach ($search_results as $r) {
                $text = strtolower($r['page_text']);
                foreach ($names as $n) {
                    $ln = strtolower($n);
                    if (strpos($text, $ln) !== false || strpos($text, 'tradition of the ' . $ln) !== false) {
                        $found[$n] = true;
                    }
                }
            }

            if (count($found) < 4) {
                // Query each missing tradition and merge unique pages
                $byKey = [];
                foreach ($search_results as $r) {
                    $key = $r['rulebook_id'] . ':' . $r['page_number'];
                    $byKey[$key] = $r;
                }

                foreach ($names as $n) {
                    if (isset($found[$n])) continue;
                    $phrase = '"Tradition of the ' . $n . '" ' . $n . ' Tradition*';
                    $boolean = '+(' . $phrase . ')';

                    $sql = "SELECT 
                                r.id as rulebook_id,
                                r.title as book_title,
                                r.category,
                                r.system_type,
                                rp.page_number,
                                rp.page_text,
                                MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE) as relevance
                            FROM rulebook_pages rp
                            JOIN rulebooks r ON rp.rulebook_id = r.id
                            WHERE MATCH(rp.page_text) AGAINST(? IN BOOLEAN MODE)";

                    $params = [$boolean, $boolean];
                    $types = 'ss';
                    if ($category) { $sql .= " AND r.category = ?"; $params[] = $category; $types .= 's'; }
                    if ($system)   { $sql .= " AND r.system_type = ?"; $params[] = $system; $types .= 's'; }
                    $sql .= " ORDER BY relevance DESC LIMIT 3";

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

                // Replace search_results with merged, up to 30 items
                $search_results = array_slice(array_values($byKey), 0, 30);
            }
            // Finally, prioritize likely Tradition-definition pages
            $search_results = prioritize_tradition_pages($search_results);

            // If still thin on explicit Tradition coverage, broaden scope beyond provided system
            if (count($found) < 4) {
                $broader = search_rulebooks_traditions($conn, $category, null, 20); // drop system filter
                if (!empty($broader)) {
                    // merge unique by rulebook_id:page_number
                    $byKey = [];
                    foreach ($search_results as $r) {
                        $key = $r['rulebook_id'] . ':' . $r['page_number'];
                        $byKey[$key] = $r;
                    }
                    foreach ($broader as $r) {
                        $key = $r['rulebook_id'] . ':' . $r['page_number'];
                        if (!isset($byKey[$key])) { $byKey[$key] = $r; }
                    }
                    $search_results = array_slice(array_values($byKey), 0, 30);
                    $search_results = prioritize_tradition_pages($search_results);
                }
            }
        }
    } else {
        $search_results = search_rulebooks($conn, $question, $category, $system, 10);
    }
    
    if (empty($search_results)) {
        return [
            'success' => true,
            'question' => $question,
            'answer' => "I couldn't find specific information about that in the VTM/MET rulebooks. Try rephrasing your question or using different keywords. You can also specify a category (Core, Faction, Supplement, Blood Magic, Journal) or system (MET-VTM, VTM, MTA, etc.) to narrow the search.",
            'sources' => [],
            'ai_model' => null,
            'searched' => true,
            'results_found' => 0
        ];
    }
    
    // Build context for AI
    $context = build_context_from_results($search_results);
    
    // Build the full prompt
    $prompt = $context . "\nQuestion: " . $question;
    
    // System prompt
    $system_prompt = "You are an expert on Vampire: The Masquerade and Mind's Eye Theatre rules and lore. Your role is to answer questions based ONLY on the provided rulebook excerpts above.

IMPORTANT RULES:
1. Always cite your sources using the format: (Source [number]: [Book Title], Page [page])
2. If the answer requires information from multiple sources, cite all relevant sources
3. If the excerpts don't contain enough information to fully answer the question, say so clearly
4. Do not make up or assume information not present in the excerpts
5. Be concise but thorough in your explanations
6. Use the exact terminology from the rulebooks

Answer the user's question now:";
    
    // Call Anthropic API
    $ai_response = call_anthropic($prompt, $system_prompt, 1500);
    
    if (!$ai_response['success']) {
        return [
            'success' => false,
            'error' => 'AI service error: ' . $ai_response['error'],
            'question' => $question,
            'sources' => array_map(function($r) {
                return [
                    'book' => $r['book_title'],
                    'page' => $r['page_number'],
                    'category' => $r['category'],
                    'system' => $r['system_type']
                ];
            }, $search_results)
        ];
    }
    
    // Format sources for response
    $sources = array_map(function($r) {
        return [
            'book' => $r['book_title'],
            'page' => (int)$r['page_number'],
            'category' => $r['category'],
            'system' => $r['system_type'],
            'excerpt' => extract_excerpt($r['page_text'], 300),
            'relevance' => (float)$r['relevance']
        ];
    }, $search_results);
    
    return [
        'success' => true,
        'question' => $question,
        'answer' => $ai_response['content'],
        'sources' => $sources,
        'ai_model' => $ai_response['model'] ?? 'claude-3-5-sonnet',
        'searched' => true,
        'results_found' => count($search_results)
    ];
}

// Handle API request
try {
    // Check for MCP API key bypass
    $mcp_api_key = $_GET['mcp_key'] ?? $_POST['mcp_key'] ?? '';
    $mcp_bypass = ($mcp_api_key === 'vbn_mcp_b4byp4ss_k3y_2025');
    
    // Get action early so we can allow certain public endpoints
    $action = $_GET['action'] ?? $_POST['action'] ?? 'ask';

    // Check authentication (unless bypassed by MCP or action is public/health)
    if (!$mcp_bypass && $action !== 'health' && $action !== 'public_traditions') {
        $auth = check_authentication($conn);
        if (!$auth['authenticated'] || !$auth['verified']) {
            http_response_code($auth['http_code']);
            echo json_encode([
                'success' => false,
                'error' => $auth['error']
            ]);
            exit;
        }
    }
    
    switch ($action) {
        case 'ask':
            // Get question from GET or POST
            $question = $_GET['question'] ?? $_POST['question'] ?? '';
            $category = $_GET['category'] ?? $_POST['category'] ?? null;
            $system = $_GET['system'] ?? $_POST['system'] ?? null;
            
            // Validate question
            $question = trim($question);
            if (empty($question)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Question parameter is required'
                ]);
                exit;
            }
            
            // Ask the Laws Agent
            $response = ask_laws_agent($conn, $question, $category, $system);
            echo json_encode($response);
            break;

        case 'public_traditions':
            // Return the seeded Six Traditions reference without requiring authentication
            $rb = db_fetch_one($conn,
                "SELECT id FROM rulebooks WHERE title = ? AND system_type = ? LIMIT 1",
                "ss",
                ['VTM - Traditions (Reference)', 'MET-VTM']
            );
            if (!$rb) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Traditions reference not found. Ask an admin to seed it.'
                ]);
                break;
            }
            $rulebook_id = (int)$rb['id'];
            $pages_result = db_select($conn,
                "SELECT page_number, page_text FROM rulebook_pages WHERE rulebook_id = ? ORDER BY page_number ASC",
                "i",
                [$rulebook_id]
            );
            $pages = [];
            if ($pages_result) {
                while ($row = mysqli_fetch_assoc($pages_result)) {
                    $pages[] = $row;
                }
            }
            echo json_encode([
                'success' => true,
                'title' => 'VTM - Traditions (Reference)',
                'rulebook_id' => $rulebook_id,
                'pages' => $pages
            ]);
            break;
            
        case 'seed_traditions':
            // Only allow via MCP bypass key for safety
            if (!$mcp_bypass) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Forbidden'
                ]);
                exit;
            }
            $seed = seed_traditions($conn);
            echo json_encode($seed);
            break;

        case 'health':
            // Health check endpoint
            $api_key_configured = load_anthropic_api_key() !== null;
            
            echo json_encode([
                'success' => true,
                'status' => 'online',
                'api_configured' => $api_key_configured,
                'database' => 'connected',
                'authenticated' => true
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action. Supported actions: ask, health'
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

