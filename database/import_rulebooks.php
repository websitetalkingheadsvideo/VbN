<?php
/**
 * Import extracted rulebook data into the database
 */

require_once __DIR__ . '/../includes/connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Determine book category from filename
 */
function determine_category(string $filename): string {
    if (strpos($filename, 'Introductory') !== false || strpos($filename, 'Reference') !== false || 
        strpos($filename, 'Laws of the Night') !== false) {
        return 'Core';
    } elseif (strpos($filename, 'Camarilla') !== false || strpos($filename, 'Anarch') !== false || strpos($filename, 'Sabbat') !== false) {
        return 'Faction';
    } elseif (strpos($filename, 'Journal') !== false) {
        return 'Journal';
    } elseif (strpos($filename, 'Blood Magic') !== false || strpos($filename, 'Thaumaturgy') !== false) {
        return 'Blood Magic';
    } elseif (strpos($filename, 'Laws of') !== false || strpos($filename, 'Liber des') !== false || strpos($filename, 'Dark Epics') !== false) {
        return 'Supplement';
    }
    return 'Other';
}

/**
 * Extract system type from filename
 */
function determine_system(string $filename): string {
    if (strpos($filename, 'MET - VTM') !== false) {
        return 'MET-VTM';
    } elseif (strpos($filename, 'MET') !== false) {
        return 'MET';
    } elseif (strpos($filename, 'VTM') !== false) {
        return 'VTM';
    } elseif (strpos($filename, 'MTA') !== false) {
        return 'MTA';
    } elseif (strpos($filename, 'Wraith') !== false) {
        return 'Wraith';
    } elseif (strpos($filename, 'WOD') !== false) {
        return 'WOD';
    }
    return 'Other';
}

/**
 * Extract book code from filename (e.g., "5017", "5040")
 */
function extract_book_code(string $filename): ?string {
    if (preg_match('/\((\d{4})\)/', $filename, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Generate clean title from filename
 */
function generate_title(string $filename): string {
    // Remove extension
    $title = pathinfo($filename, PATHINFO_FILENAME);
    
    // Remove book code if present
    $title = preg_replace('/\s*\(\d{4}\)\s*$/', '', $title);
    
    // Clean up common prefixes
    $title = preg_replace('/^(MET|VTM|MTA|WOD|Wraith)\s*-\s*/', '', $title);
    
    return trim($title);
}

/**
 * Import a single rulebook
 */
function import_rulebook(mysqli $conn, array $book_data, string $json_path, string $pdf_path): ?int {
    $metadata = $book_data['metadata'];
    $filename = $metadata['filename'];
    $pdf_pages = $metadata['page_count'];
    $extracted_pages = !empty($book_data['pages']) ? count($book_data['pages']) : 0;
    
    echo "Importing: {$filename} (PDF: {$pdf_pages} pages, Extracted: {$extracted_pages} pages)\n";
    
    // Prepare book metadata
    $title = generate_title($filename);
    $category = determine_category($filename);
    $system_type = determine_system($filename);
    $book_code = extract_book_code($filename);
    $page_count = $metadata['page_count'];
    $author = $metadata['author'] ?? null;
    $subject = $metadata['subject'] ?? null;
    
    // Insert rulebook
    $sql = "INSERT INTO rulebooks 
            (filename, title, book_code, category, system_type, page_count, 
             file_path, pdf_path, author, subject, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'extracted')
            ON DUPLICATE KEY UPDATE 
            title = VALUES(title),
            book_code = VALUES(book_code),
            category = VALUES(category),
            system_type = VALUES(system_type),
            page_count = VALUES(page_count),
            file_path = VALUES(file_path),
            pdf_path = VALUES(pdf_path),
            updated_at = CURRENT_TIMESTAMP";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssissss',
        $filename, $title, $book_code, $category, $system_type, 
        $page_count, $json_path, $pdf_path, $author, $subject
    );
    
    if (!$stmt->execute()) {
        echo "    [ERROR] Failed to insert rulebook: " . $stmt->error . "\n";
        return null;
    }
    
    $rulebook_id = $stmt->insert_id ?: $conn->query(
        "SELECT id FROM rulebooks WHERE filename = " . $conn->quote($filename)
    )->fetch_row()[0];
    
    $stmt->close();
    
    // Import pages
    if (!empty($book_data['pages']) && count($book_data['pages']) > 0) {
        $imported_pages = import_pages($conn, $rulebook_id, $book_data['pages']);
        echo "    ✅ Imported {$imported_pages} pages\n";
    } else {
        echo "    ⚠️  WARN: Book has {$metadata['page_count']} pages in metadata but no extractable text (likely image-based PDF)\n";
    }
    
    // Update status
    $conn->query("UPDATE rulebooks SET status = 'indexed' WHERE id = {$rulebook_id}");
    
    echo "    ✨ Complete!\n";
    return $rulebook_id;
}

/**
 * Import pages for a rulebook
 */
function import_pages(mysqli $conn, int $rulebook_id, array $pages): int {
    $imported = 0;
    
    $sql = "INSERT INTO rulebook_pages 
            (rulebook_id, page_number, page_text, word_count) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            page_text = VALUES(page_text),
            word_count = VALUES(word_count),
            updated_at = CURRENT_TIMESTAMP";
    
    $stmt = $conn->prepare($sql);
    
    $total_pages = count($pages);
    $processed_pages = 0;
    echo "  Progress: Importing {$total_pages} pages...\n";
    
    foreach ($pages as $page) {
        $page_num = $page['page_number'];
        $text = $page['text'];
        $word_count = str_word_count($text);
        
        $stmt->bind_param('iisi', $rulebook_id, $page_num, $text, $word_count);
        
        if ($stmt->execute()) {
            $imported++;
        }
        
        $processed_pages++;
        if ($processed_pages % 50 == 0 || $processed_pages == $total_pages) {
            echo "      Progress: {$processed_pages}/{$total_pages} pages...\n";
        }
    }
    
    $stmt->close();
    return $imported;
}

/**
 * Main import function
 */
function import_all_rulebooks(mysqli $conn, string $data_dir): void {
    // Read extraction summary
    $summary_file = $data_dir . '/_extraction_summary.json';
    
    if (!file_exists($summary_file)) {
        throw new Exception("Extraction summary not found: {$summary_file}");
    }
    
    $summary = json_decode(file_get_contents($summary_file), true);
    
    if (!$summary) {
        throw new Exception("Failed to parse extraction summary");
    }
    
    $total = count($summary['files']);
    $imported = 0;
    $skipped = 0;
    
    echo "Importing {$total} rulebooks...\n";
    echo "=" . str_repeat("=", 59) . "\n";
    flush();
    
    $current = 0;
    foreach ($summary['files'] as $file_info) {
        $current++;
        echo "\n[{$current}/{$total}] ";
        flush();
        // Convert Windows paths to server paths
        $json_path = str_replace('G:\\VbN\\data\\extracted_rulebooks\\', $data_dir . '/', $file_info['output_json']);
        $json_path = str_replace('\\', '/', $json_path);
        
        // Skip books with no pages
        if ($file_info['page_count'] == 0) {
            echo "⏭️  Skipping (no pages in PDF): {$file_info['filename']}\n";
            $skipped++;
            continue;
        }
        
        // Load JSON data
        if (!file_exists($json_path)) {
            echo "❌ ERROR: JSON not found: {$json_path}\n";
            $skipped++;
            continue;
        }
        
        $book_data = json_decode(file_get_contents($json_path), true);
        
        if (!$book_data) {
            echo "❌ ERROR: Failed to parse JSON: {$json_path}\n";
            $skipped++;
            continue;
        }
        
        // Get PDF path
        $pdf_path = str_replace('data/extracted_rulebooks/', 'reference/Books/', $json_path);
        $pdf_path = str_replace('.json', '.pdf', $pdf_path);
        
        // Import the book
        $rulebook_id = import_rulebook($conn, $book_data, $json_path, $pdf_path);
        
        if ($rulebook_id) {
            $imported++;
        } else {
            echo "    ❌ Failed to import\n";
            $skipped++;
        }
    }
    
    echo "\n" . str_repeat("=", 59) . "\n";
    echo "🎉 IMPORT COMPLETE!\n";
    echo "  📚 Total books: {$total}\n";
    echo "  ✅ Successfully imported: {$imported}\n";
    echo "  ⏭️  Skipped: {$skipped}\n";
}

// Main execution
try {
    // Check if running via web browser or CLI
    $is_web = php_sapi_name() !== 'cli';
    
    if ($is_web) {
        header('Content-Type: text/html; charset=utf-8');
        // Disable output buffering for real-time progress
        ob_implicit_flush(1);
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        echo "<!DOCTYPE html><html><head><title>Importing Rulebooks</title><style>
            body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
            pre { white-space: pre-wrap; word-wrap: break-word; }
            .error { color: #ff6b6b; }
            .success { color: #51cf66; }
            .warning { color: #ffd43b; }
        </style></head><body>";
        echo "<h1>🦇 Importing Rulebooks to Database</h1><pre>";
        flush();
    }
    
    $data_dir = __DIR__ . '/../data/extracted_rulebooks';
    
    echo "Starting rulebook import...\n";
    echo "Data directory: {$data_dir}\n\n";
    
    import_all_rulebooks($conn, $data_dir);
    
    if ($is_web) {
        echo "</pre>";
        echo "<p style='margin-top: 20px;'><strong>Import complete! <a href='../admin/rulebooks_search.php'>View Rulebooks Search</a></strong></p>";
        echo "</body></html>";
    }
    
} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

