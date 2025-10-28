<?php
/**
 * Create database tables for rulebook content storage and search
 */

require_once __DIR__ . '/../includes/connect.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Create all rulebook-related tables
 */
function create_rulebooks_tables(mysqli $conn): void {
    
    // Table 1: Rulebooks - Main metadata table
    $sql_rulebooks = "
    CREATE TABLE IF NOT EXISTS rulebooks (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        book_code VARCHAR(50) DEFAULT NULL COMMENT 'e.g., 5017, 5040',
        category VARCHAR(100) NOT NULL COMMENT 'Core, Faction, Supplement, Blood Magic, Journal, Other',
        system_type VARCHAR(50) NOT NULL COMMENT 'VTM, MET, MTA, WOD, Wraith, etc',
        page_count INT UNSIGNED NOT NULL DEFAULT 0,
        file_path VARCHAR(500) NOT NULL,
        pdf_path VARCHAR(500) NOT NULL,
        author VARCHAR(255) DEFAULT NULL,
        subject VARCHAR(500) DEFAULT NULL,
        extraction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_indexed TIMESTAMP NULL DEFAULT NULL,
        status ENUM('extracted', 'indexed', 'parsed', 'complete') DEFAULT 'extracted',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_system (system_type),
        INDEX idx_status (status),
        FULLTEXT INDEX ft_title (title)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Main rulebook metadata table';
    ";
    
    // Table 2: Rulebook Pages - Individual page content
    $sql_pages = "
    CREATE TABLE IF NOT EXISTS rulebook_pages (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        rulebook_id INT UNSIGNED NOT NULL,
        page_number INT UNSIGNED NOT NULL,
        page_text LONGTEXT NOT NULL,
        word_count INT UNSIGNED DEFAULT 0,
        has_tables BOOLEAN DEFAULT FALSE,
        has_images BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (rulebook_id) REFERENCES rulebooks(id) ON DELETE CASCADE,
        UNIQUE KEY unique_page (rulebook_id, page_number),
        INDEX idx_page_num (page_number),
        FULLTEXT INDEX ft_content (page_text)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Individual pages from rulebooks';
    ";
    
    // Table 3: Rulebook Sections - Chapters/sections (manually curated or AI-parsed)
    $sql_sections = "
    CREATE TABLE IF NOT EXISTS rulebook_sections (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        rulebook_id INT UNSIGNED NOT NULL,
        section_title VARCHAR(255) NOT NULL,
        section_type VARCHAR(50) DEFAULT 'chapter' COMMENT 'chapter, appendix, index, etc',
        start_page INT UNSIGNED DEFAULT NULL,
        end_page INT UNSIGNED DEFAULT NULL,
        parent_section_id INT UNSIGNED DEFAULT NULL COMMENT 'For nested sections',
        section_order INT UNSIGNED DEFAULT 0,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (rulebook_id) REFERENCES rulebooks(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_section_id) REFERENCES rulebook_sections(id) ON DELETE SET NULL,
        INDEX idx_rulebook (rulebook_id),
        INDEX idx_parent (parent_section_id),
        FULLTEXT INDEX ft_title (section_title)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Sections and chapters within rulebooks';
    ";
    
    // Table 4: Rulebook Rules - Specific game rules and mechanics
    $sql_rules = "
    CREATE TABLE IF NOT EXISTS rulebook_rules (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        rulebook_id INT UNSIGNED NOT NULL,
        rule_name VARCHAR(255) NOT NULL,
        rule_category VARCHAR(100) DEFAULT NULL COMMENT 'Combat, Social, Disciplines, Merits, etc',
        rule_text LONGTEXT NOT NULL,
        page_reference VARCHAR(50) DEFAULT NULL COMMENT 'e.g., p.142, pp.50-52',
        related_terms JSON DEFAULT NULL COMMENT 'Related keywords and terms',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (rulebook_id) REFERENCES rulebooks(id) ON DELETE CASCADE,
        INDEX idx_rulebook (rulebook_id),
        INDEX idx_category (rule_category),
        FULLTEXT INDEX ft_rule_name (rule_name),
        FULLTEXT INDEX ft_rule_text (rule_text)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Specific game rules extracted from rulebooks';
    ";
    
    // Table 5: Rulebook Search Terms - For quick lookups and search
    $sql_search = "
    CREATE TABLE IF NOT EXISTS rulebook_search_terms (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        term VARCHAR(255) NOT NULL,
        term_type VARCHAR(50) DEFAULT NULL COMMENT 'discipline, clan, merit, flaw, rule, etc',
        rulebook_id INT UNSIGNED NOT NULL,
        page_number INT UNSIGNED DEFAULT NULL,
        context_snippet TEXT DEFAULT NULL COMMENT 'Brief context around the term',
        importance ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rulebook_id) REFERENCES rulebooks(id) ON DELETE CASCADE,
        INDEX idx_term (term),
        INDEX idx_type (term_type),
        INDEX idx_rulebook (rulebook_id),
        INDEX idx_importance (importance),
        FULLTEXT INDEX ft_term (term),
        FULLTEXT INDEX ft_context (context_snippet)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Search terms and keywords from rulebooks';
    ";
    
    // Table 6: Rulebook Cross References - Links between books
    $sql_xrefs = "
    CREATE TABLE IF NOT EXISTS rulebook_cross_references (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        source_rulebook_id INT UNSIGNED NOT NULL,
        source_page INT UNSIGNED DEFAULT NULL,
        target_rulebook_id INT UNSIGNED NOT NULL,
        target_page INT UNSIGNED DEFAULT NULL,
        reference_type VARCHAR(50) DEFAULT 'general' COMMENT 'see also, requires, expands, contradicts',
        reference_text TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (source_rulebook_id) REFERENCES rulebooks(id) ON DELETE CASCADE,
        FOREIGN KEY (target_rulebook_id) REFERENCES rulebooks(id) ON DELETE CASCADE,
        INDEX idx_source (source_rulebook_id),
        INDEX idx_target (target_rulebook_id),
        INDEX idx_type (reference_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Cross-references between rulebooks';
    ";
    
    // Execute all CREATE statements
    $tables = [
        'rulebooks' => $sql_rulebooks,
        'rulebook_pages' => $sql_pages,
        'rulebook_sections' => $sql_sections,
        'rulebook_rules' => $sql_rules,
        'rulebook_search_terms' => $sql_search,
        'rulebook_cross_references' => $sql_xrefs
    ];
    
    foreach ($tables as $table_name => $sql) {
        try {
            $conn->query($sql);
            echo "[SUCCESS] Created table: {$table_name}\n";
        } catch (mysqli_sql_exception $e) {
            echo "[ERROR] Failed to create {$table_name}: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// Main execution
try {
    echo "Creating rulebook database tables...\n";
    echo "=" . str_repeat("=", 59) . "\n";
    
    create_rulebooks_tables($conn);
    
    echo "=" . str_repeat("=", 59) . "\n";
    echo "[SUCCESS] All rulebook tables created successfully!\n";
    
} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

