<?php
/**
 * Laws Agent API
 * AI-powered agent that answers VTM/MET rules questions
 */

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
 * Ask the Laws Agent a question
 */
function ask_laws_agent(mysqli $conn, string $question, ?string $category = null, ?string $system = null): array {
    // Search for relevant content
    $search_results = search_rulebooks($conn, $question, $category, $system, 5);
    
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
    // Check authentication
    $auth = check_authentication($conn);
    
    if (!$auth['authenticated'] || !$auth['verified']) {
        http_response_code($auth['http_code']);
        echo json_encode([
            'success' => false,
            'error' => $auth['error']
        ]);
        exit;
    }
    
    // Get parameters
    $action = $_GET['action'] ?? $_POST['action'] ?? 'ask';
    
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

