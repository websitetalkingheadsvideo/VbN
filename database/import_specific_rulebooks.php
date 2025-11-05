<?php
/**
 * Import specific rulebooks from extraction summary by filename
 * Usage: php database/import_specific_rulebooks.php Toreador.docx Lasombra.docx
 */

require_once __DIR__ . '/../includes/connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Copy necessary functions from import_rulebooks.php
function determine_category(string $filename): string {
    if (strpos($filename, 'Clanbook') !== false || strpos($filename, 'Clan Books') !== false || 
        preg_match('/\b(Assamite|Brujah|Followers of Set|Gangrel|Giovanni|Lasombra|Malkavian|Nosferatu|Ravnos|Toreador|Tremere|Tzimisce|Ventrue)\b/i', $filename)) {
        return 'Clan Books';
    } elseif (strpos($filename, 'Introductory') !== false || strpos($filename, 'Reference') !== false || 
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

function extract_book_code(string $filename): ?string {
    if (preg_match('/\((\d{4})\)/', $filename, $matches)) {
        return $matches[1];
    }
    return null;
}

function generate_title(string $filename): string {
    $title = pathinfo($filename, PATHINFO_FILENAME);
    $title = preg_replace('/\s*\(\d{4}\)\s*$/', '', $title);
    $title = preg_replace('/^(MET|VTM|MTA|WOD|Wraith)\s*-\s*/', '', $title);
    return trim($title);
}

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

function import_rulebook(mysqli $conn, array $book_data, string $json_path, ?string $source_path): ?int {
    $metadata = $book_data['metadata'];
    $filename = $metadata['filename'];
    $pdf_pages = $metadata['page_count'];
    $extracted_pages = !empty($book_data['pages']) ? count($book_data['pages']) : 0;
    
    echo "Importing: {$filename} (Extracted: {$extracted_pages} pages)\n";
    
    $title = generate_title($filename);
    $category = determine_category($filename);
    $system_type = determine_system($filename);
    $book_code = extract_book_code($filename);
    $page_count = $metadata['page_count'];
    $author = $metadata['author'] ?? null;
    $subject = $metadata['subject'] ?? null;
    
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
    
    // Ensure source_path is not null (database constraint)
    if ($source_path === null) {
        // Construct expected path based on filename
        $source_path = '/reference/Books/Clan Books/' . $filename;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssissss',
        $filename, $title, $book_code, $category, $system_type, 
        $page_count, $json_path, $source_path, $author, $subject
    );
    
    if (!$stmt->execute()) {
        echo "    [ERROR] Failed to insert rulebook: " . $stmt->error . "\n";
        return null;
    }
    
    $rulebook_id = $stmt->insert_id ?: $conn->query(
        "SELECT id FROM rulebooks WHERE filename = " . $conn->quote($filename)
    )->fetch_row()[0];
    
    $stmt->close();
    
    if (!empty($book_data['pages']) && count($book_data['pages']) > 0) {
        $imported_pages = import_pages($conn, $rulebook_id, $book_data['pages']);
        echo "    [OK] Imported {$imported_pages} pages\n";
    } else {
        echo "    [WARN] Book has {$metadata['page_count']} pages in metadata but no extractable text\n";
    }
    
    $conn->query("UPDATE rulebooks SET status = 'indexed' WHERE id = {$rulebook_id}");
    
    echo "    [OK] Complete!\n";
    return $rulebook_id;
}

/**
 * Import specific rulebooks by filename
 */
function import_specific_rulebooks(mysqli $conn, string $data_dir, array $filenames): void {
    $summary_file = $data_dir . '/_extraction_summary.json';
    
    if (!file_exists($summary_file)) {
        throw new Exception("Extraction summary not found: {$summary_file}");
    }
    
    $summary = json_decode(file_get_contents($summary_file), true);
    
    if (!$summary) {
        throw new Exception("Failed to parse extraction summary");
    }
    
    // Filter to only the requested files
    $requested_files = array_filter($summary['files'], function($file_info) use ($filenames) {
        return in_array($file_info['filename'], $filenames);
    });
    
    if (empty($requested_files)) {
        echo "No matching files found in summary for: " . implode(', ', $filenames) . "\n";
        return;
    }
    
    $total = count($requested_files);
    $imported = 0;
    $skipped = 0;
    
    echo "Importing {$total} specific rulebook(s)...\n";
    echo "=" . str_repeat("=", 59) . "\n";
    flush();
    
    $current = 0;
    foreach ($requested_files as $file_info) {
        $current++;
        echo "\n[{$current}/{$total}] ";
        flush();
        
        $json_path = str_replace('G:\\VbN\\data\\extracted_rulebooks\\', $data_dir . '/', $file_info['output_json']);
        $json_path = str_replace('\\', '/', $json_path);
        
        if ($file_info['page_count'] == 0) {
            echo "[SKIP] Skipping (no pages): {$file_info['filename']}\n";
            $skipped++;
            continue;
        }
        
        if (!file_exists($json_path)) {
            echo "[ERROR] JSON not found: {$json_path}\n";
            $skipped++;
            continue;
        }
        
        $book_data = json_decode(file_get_contents($json_path), true);
        
        if (!$book_data) {
            echo "[ERROR] Failed to parse JSON: {$json_path}\n";
            $skipped++;
            continue;
        }
        
        // Get source file path (DOCX or PDF)
        // Match the path structure used in import_rulebooks.php
        // Convert JSON path to source file path
        $source_path = str_replace('data/extracted_rulebooks/', 'reference/Books/', $json_path);
        $source_path = str_replace('.json', '', $source_path);
        
        // Get the filename without extension
        $filename_base = pathinfo($file_info['filename'], PATHINFO_FILENAME);
        
        // Try DOCX first (since we're importing DOCX files)
        $docx_path = $source_path . '.docx';
        $pdf_path = $source_path . '.pdf';
        
        // Check if DOCX exists in Clan Books folder (since that's where these files are)
        $clan_books_docx = str_replace('reference/Books/', 'reference/Books/Clan Books/', $docx_path);
        $clan_books_pdf = str_replace('reference/Books/', 'reference/Books/Clan Books/', $pdf_path);
        
        // Determine the actual source path
        if (file_exists($clan_books_docx)) {
            $source_path = $clan_books_docx;
        } elseif (file_exists($docx_path)) {
            $source_path = $docx_path;
        } elseif (file_exists($clan_books_pdf)) {
            $source_path = $clan_books_pdf;
        } elseif (file_exists($pdf_path)) {
            $source_path = $pdf_path;
        } else {
            // Fallback: use expected Clan Books path (can't be null per DB constraint)
            $source_path = 'reference/Books/Clan Books/' . $file_info['filename'];
            echo "    [WARN] Source file not found, using expected path: {$source_path}\n";
        }
        
        $rulebook_id = import_rulebook($conn, $book_data, $json_path, $source_path);
        
        if ($rulebook_id) {
            $imported++;
        } else {
            echo "    [ERROR] Failed to import\n";
            $skipped++;
        }
    }
    
    echo "\n" . str_repeat("=", 59) . "\n";
    echo "[SUCCESS] Import complete!\n";
    echo "  [OK] Successfully imported: {$imported}\n";
    echo "  [SKIP] Skipped: {$skipped}\n";
}

// Main execution
try {
    $is_web = php_sapi_name() !== 'cli';
    
    if ($is_web) {
        header('Content-Type: text/html; charset=utf-8');
        ob_implicit_flush(1);
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        echo "<!DOCTYPE html><html><head><title>Importing Specific Rulebooks</title><style>
            body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
            pre { white-space: pre-wrap; word-wrap: break-word; }
        </style></head><body>";
        echo "<h1>Importing Specific Rulebooks</h1><pre>";
        flush();
    }
    
    $data_dir = __DIR__ . '/../data/extracted_rulebooks';
    
    // Get filenames from command line or GET parameters
    if ($is_web) {
        $filenames = isset($_GET['files']) ? explode(',', $_GET['files']) : [];
        if (empty($filenames)) {
            die("Usage: ?files=Toreador.docx,Lasombra.docx\n");
        }
    } else {
        $filenames = array_slice($argv, 1);
        if (empty($filenames)) {
            die("Usage: php import_specific_rulebooks.php Toreador.docx Lasombra.docx\n");
        }
    }
    
    echo "Starting targeted import...\n";
    echo "Files to import: " . implode(', ', $filenames) . "\n";
    echo "Data directory: {$data_dir}\n\n";
    
    import_specific_rulebooks($conn, $data_dir, $filenames);
    
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

