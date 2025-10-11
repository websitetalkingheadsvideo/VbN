<?php
/**
 * Character Import Database Migration Runner
 * Executes character_import_migration.sql with error handling
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=================================================================\n";
echo "Character Import Database Migration Runner\n";
echo "=================================================================\n\n";

// Include database connection
require_once __DIR__ . '/../includes/connect.php';

// Check if connection exists
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error() . "\n");
}

echo "✅ Database connection established\n";
echo "   Server: $servername\n";
echo "   Database: $dbname\n\n";

// Path to migration SQL file
$sql_file = __DIR__ . '/character_import_migration.sql';

// Check if SQL file exists
if (!file_exists($sql_file)) {
    die("❌ Migration file not found: $sql_file\n");
}

echo "📄 Reading migration file: character_import_migration.sql\n\n";

// Read SQL file
$sql_content = file_get_contents($sql_file);

if ($sql_content === false) {
    die("❌ Failed to read migration file\n");
}

// Remove all comment lines first
$sql_content = preg_replace('/^--.*$/m', '', $sql_content);

// Split SQL into individual statements by semicolon
$statements = explode(';', $sql_content);

$success_count = 0;
$error_count = 0;
$skipped_count = 0;

echo "🚀 Executing migration...\n";
echo "   Found " . count($statements) . " potential statements\n\n";

foreach ($statements as $index => $statement) {
    // Trim whitespace
    $statement = trim($statement);
    
    // Skip empty statements
    if (empty($statement)) {
        continue;
    }
    
    // Execute statement
    $result = mysqli_query($conn, $statement);
    
    if ($result === false) {
        $error = mysqli_error($conn);
        $error_code = mysqli_errno($conn);
        
        // Check if error is due to column/index already existing (safe to ignore)
        // MySQL error codes: 1060 = Duplicate column, 1061 = Duplicate key
        if ($error_code === 1060 || 
            $error_code === 1061 ||
            stripos($error, "Duplicate column name") !== false || 
            stripos($error, "Duplicate key name") !== false ||
            stripos($error, "already exists") !== false) {
            $skipped_count++;
            
            // Show which table/column was skipped
            if (preg_match('/ALTER TABLE\s+(\w+)/', $statement, $matches)) {
                echo "⏭️  Skipped (already exists): Table {$matches[1]}\n";
            } else {
                echo "⏭️  Skipped (already exists): " . substr($statement, 0, 60) . "...\n";
            }
        } else {
            $error_count++;
            echo "❌ Error executing statement:\n";
            echo "   Statement: " . substr($statement, 0, 100) . "...\n";
            echo "   Error: $error\n\n";
        }
    } else {
        $success_count++;
        
        // Show meaningful output for certain statements
        if (stripos($statement, 'CREATE TABLE') !== false) {
            preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
            $table_name = $matches[1] ?? 'unknown';
            echo "✅ Created table: $table_name\n";
        } elseif (stripos($statement, 'ALTER TABLE') !== false) {
            preg_match('/ALTER TABLE\s+`?(\w+)`?/i', $statement, $matches);
            $table_name = $matches[1] ?? 'unknown';
            echo "✅ Modified table: $table_name\n";
        } elseif (stripos($statement, 'INSERT INTO') !== false) {
            preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches);
            $table_name = $matches[1] ?? 'unknown';
            $affected = mysqli_affected_rows($conn);
            echo "✅ Inserted data into: $table_name ($affected rows)\n";
        } elseif (stripos($statement, 'SELECT') !== false) {
            // Display SELECT results (for status messages)
            if ($result instanceof mysqli_result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    foreach ($row as $value) {
                        echo "   📋 $value\n";
                    }
                }
                mysqli_free_result($result);
            }
        }
    }
}

echo "\n";
echo "=================================================================\n";
echo "Migration Complete!\n";
echo "=================================================================\n";
echo "✅ Successful: $success_count statements\n";
if ($skipped_count > 0) {
    echo "⏭️  Skipped: $skipped_count statements (already exist)\n";
}
if ($error_count > 0) {
    echo "❌ Errors: $error_count statements\n";
} else {
    echo "❌ Errors: 0\n";
}
echo "=================================================================\n\n";

if ($error_count > 0) {
    echo "⚠️  Migration completed with errors. Please review the errors above.\n";
    exit(1);
} else {
    echo "🎉 Migration completed successfully!\n";
    echo "   Database is ready for character imports.\n\n";
    exit(0);
}

// Close connection
mysqli_close($conn);
?>

