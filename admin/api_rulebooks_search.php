<?php
/**
 * Rulebooks Search API
 * 
 * Provides search functionality across the rulebook database
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/connect.php';

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log errors

/**
 * Search across all rulebook content
 */
function search_all(mysqli $conn, string $query, ?string $category = null, ?string $system = null, int $limit = 50): array {
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
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = [];
    while ($row = $result->fetch_assoc()) {
        // Extract snippet around search term
        $snippet = extract_snippet($row['page_text'], $query);
        
        $results[] = [
            'rulebook_id' => (int)$row['rulebook_id'],
            'book_title' => $row['book_title'],
            'category' => $row['category'],
            'system_type' => $row['system_type'],
            'page_number' => (int)$row['page_number'],
            'snippet' => $snippet,
            'relevance' => (float)$row['relevance']
        ];
    }
    
    return $results;
}

/**
 * Search for specific rules
 */
function search_rules(mysqli $conn, string $query, ?string $rule_category = null, int $limit = 50): array {
    $sql = "SELECT 
                rr.id,
                rr.rule_name,
                rr.rule_category,
                rr.rule_text,
                rr.page_reference,
                r.title as book_title,
                r.system_type,
                MATCH(rr.rule_name, rr.rule_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM rulebook_rules rr
            JOIN rulebooks r ON rr.rulebook_id = r.id
            WHERE MATCH(rr.rule_name, rr.rule_text) AGAINST(? IN NATURAL LANGUAGE MODE)";
    
    $params = [$query, $query];
    $types = 'ss';
    
    if ($rule_category) {
        $sql .= " AND rr.rule_category = ?";
        $params[] = $rule_category;
        $types .= 's';
    }
    
    $sql .= " ORDER BY relevance DESC LIMIT ?";
    $params[] = $limit;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Search by term/keyword
 */
function search_terms(mysqli $conn, string $term, ?string $term_type = null, int $limit = 50): array {
    $sql = "SELECT 
                rst.term,
                rst.term_type,
                rst.page_number,
                rst.context_snippet,
                rst.importance,
                r.title as book_title,
                r.system_type
            FROM rulebook_search_terms rst
            JOIN rulebooks r ON rst.rulebook_id = r.id
            WHERE rst.term LIKE ?";
    
    $params = ['%' . $term . '%'];
    $types = 's';
    
    if ($term_type) {
        $sql .= " AND rst.term_type = ?";
        $params[] = $term_type;
        $types .= 's';
    }
    
    $sql .= " ORDER BY rst.importance DESC, r.category, rst.term LIMIT ?";
    $params[] = $limit;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all books with optional filtering
 */
function get_books(mysqli $conn, ?string $category = null, ?string $system = null): array {
    $sql = "SELECT 
                id,
                filename,
                title,
                book_code,
                category,
                system_type,
                page_count,
                status
            FROM rulebooks
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= 's';
    }
    
    if ($system) {
        $sql .= " AND system_type = ?";
        $params[] = $system;
        $types .= 's';
    }
    
    $sql .= " ORDER BY category, title";
    
    if (empty($params)) {
        $result = $conn->query($sql);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get page content
 */
function get_page(mysqli $conn, int $rulebook_id, int $page_number): ?array {
    $sql = "SELECT 
                rp.page_number,
                rp.page_text,
                rp.word_count,
                r.title as book_title,
                r.filename
            FROM rulebook_pages rp
            JOIN rulebooks r ON rp.rulebook_id = r.id
            WHERE rp.rulebook_id = ? AND rp.page_number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $rulebook_id, $page_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Extract snippet of text around search term
 */
function extract_snippet(string $text, string $search_term, int $context_chars = 200): string {
    $pos = stripos($text, $search_term);
    
    if ($pos === false) {
        // Term not found, return beginning
        return substr($text, 0, $context_chars * 2) . '...';
    }
    
    $start = max(0, $pos - $context_chars);
    $length = strlen($search_term) + ($context_chars * 2);
    $snippet = substr($text, $start, $length);
    
    // Add ellipsis if not at boundaries
    if ($start > 0) {
        $snippet = '...' . $snippet;
    }
    if ($start + $length < strlen($text)) {
        $snippet .= '...';
    }
    
    // Highlight search term
    $snippet = str_ireplace($search_term, "<mark>{$search_term}</mark>", $snippet);
    
    return $snippet;
}

/**
 * Get available categories and systems
 */
function get_filters(mysqli $conn): array {
    $categories = $conn->query("SELECT DISTINCT category FROM rulebooks ORDER BY category")->fetch_all(MYSQLI_ASSOC);
    $systems = $conn->query("SELECT DISTINCT system_type FROM rulebooks ORDER BY system_type")->fetch_all(MYSQLI_ASSOC);
    
    return [
        'categories' => array_column($categories, 'category'),
        'systems' => array_column($systems, 'system_type')
    ];
}

// Handle API requests
try {
    $action = $_GET['action'] ?? 'search';
    
    switch ($action) {
        case 'search':
            $query = $_GET['q'] ?? '';
            $category = $_GET['category'] ?? null;
            $system = $_GET['system'] ?? null;
            $limit = (int)($_GET['limit'] ?? 50);
            
            if (empty($query)) {
                throw new Exception('Search query is required');
            }
            
            $results = search_all($conn, $query, $category, $system, $limit);
            echo json_encode(['success' => true, 'results' => $results, 'count' => count($results)]);
            break;
            
        case 'search_rules':
            $query = $_GET['q'] ?? '';
            $rule_category = $_GET['rule_category'] ?? null;
            $limit = (int)($_GET['limit'] ?? 50);
            
            if (empty($query)) {
                throw new Exception('Search query is required');
            }
            
            $results = search_rules($conn, $query, $rule_category, $limit);
            echo json_encode(['success' => true, 'results' => $results, 'count' => count($results)]);
            break;
            
        case 'search_terms':
            $term = $_GET['term'] ?? '';
            $term_type = $_GET['type'] ?? null;
            $limit = (int)($_GET['limit'] ?? 50);
            
            if (empty($term)) {
                throw new Exception('Search term is required');
            }
            
            $results = search_terms($conn, $term, $term_type, $limit);
            echo json_encode(['success' => true, 'results' => $results, 'count' => count($results)]);
            break;
            
        case 'books':
            $category = $_GET['category'] ?? null;
            $system = $_GET['system'] ?? null;
            
            $books = get_books($conn, $category, $system);
            echo json_encode(['success' => true, 'books' => $books, 'count' => count($books)]);
            break;
            
        case 'page':
            $rulebook_id = (int)($_GET['rulebook_id'] ?? 0);
            $page_number = (int)($_GET['page'] ?? 0);
            
            if (!$rulebook_id || !$page_number) {
                throw new Exception('Rulebook ID and page number are required');
            }
            
            $page = get_page($conn, $rulebook_id, $page_number);
            
            if (!$page) {
                throw new Exception('Page not found');
            }
            
            echo json_encode(['success' => true, 'page' => $page]);
            break;
            
        case 'filters':
            $filters = get_filters($conn);
            echo json_encode(['success' => true, 'filters' => $filters]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

