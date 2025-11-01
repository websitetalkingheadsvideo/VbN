<?php
/**
 * Discipline Helper Functions
 * 
 * Functions for looking up discipline powers and managing discipline data
 * 
 * Note: This file does NOT include connect.php. It relies on the calling file
 * to include connect.php so that $conn is available via 'global $conn'
 */

/**
 * Get a specific power for a discipline at a given level
 * 
 * @param string $discipline_name The name of the discipline
 * @param int $level The power level (1-5)
 * @return array|null Array with 'power_name' and 'description', or null if not found
 */
function getDisciplinePower($discipline_name, $level) {
    global $conn;
    
    if ($level < 1 || $level > 5) {
        return null;
    }
    
    $discipline_name = mysqli_real_escape_string($conn, $discipline_name);
    $level = (int)$level;
    
    $sql = "SELECT dp.power_name, dp.description, dp.prerequisites, d.name as discipline_name
            FROM discipline_powers dp
            JOIN disciplines d ON dp.discipline_id = d.id
            WHERE d.name = '$discipline_name' AND dp.power_level = $level";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return null;
    }
    
    if ($row = mysqli_fetch_assoc($result)) {
        return [
            'power_name' => $row['power_name'],
            'description' => $row['description'],
            'prerequisites' => $row['prerequisites'] ? json_decode($row['prerequisites'], true) : null,
            'discipline_name' => $row['discipline_name'],
            'level' => $level
        ];
    }
    
    return null;
}

/**
 * Get all powers for a character's discipline up to their level
 * 
 * @param int $character_id The character ID
 * @param string $discipline_name The discipline name
 * @return array Array of powers (levels 1 through character's level)
 */
function getCharacterDisciplinePowers($character_id, $discipline_name) {
    global $conn;
    
    if (!$conn) {
        error_log("getCharacterDisciplinePowers: Database connection not available");
        return [];
    }
    
    // Use regular queries to avoid mysqlnd dependency
    $character_id = (int)$character_id;
    $discipline_name = mysqli_real_escape_string($conn, $discipline_name);
    
    // First get the character's level in this discipline
    $char_level_sql = "SELECT level FROM character_disciplines 
                       WHERE character_id = $character_id AND discipline_name = '$discipline_name'";
    $char_result = mysqli_query($conn, $char_level_sql);
    
    if (!$char_result || !$char_row = mysqli_fetch_assoc($char_result)) {
        return [];
    }
    
    $character_level = (int)$char_row['level'];
    
    if ($character_level < 1 || $character_level > 5) {
        return [];
    }
    
    // Get all powers up to that level
    $sql = "SELECT dp.power_level, dp.power_name, dp.description, dp.prerequisites
            FROM discipline_powers dp
            JOIN disciplines d ON dp.discipline_id = d.id
            WHERE d.name = '$discipline_name' AND dp.power_level <= $character_level
            ORDER BY dp.power_level ASC";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Failed to query discipline powers: " . mysqli_error($conn));
        return [];
    }
    
    $powers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $powers[] = [
            'level' => (int)$row['power_level'],
            'power_name' => $row['power_name'],
            'description' => $row['description'],
            'prerequisites' => $row['prerequisites'] ? json_decode($row['prerequisites'], true) : null
        ];
    }
    
    return $powers;
}

/**
 * Get all 5 powers for a discipline
 * 
 * @param string $discipline_name The discipline name
 * @return array Array of all 5 powers
 */
function getAllDisciplinePowers($discipline_name) {
    global $conn;
    
    $discipline_name = mysqli_real_escape_string($conn, $discipline_name);
    
    $sql = "SELECT dp.power_level, dp.power_name, dp.description, dp.prerequisites
            FROM discipline_powers dp
            JOIN disciplines d ON dp.discipline_id = d.id
            WHERE d.name = '$discipline_name'
            ORDER BY dp.power_level ASC";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return [];
    }
    
    $powers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $powers[] = [
            'level' => (int)$row['power_level'],
            'power_name' => $row['power_name'],
            'description' => $row['description'],
            'prerequisites' => $row['prerequisites'] ? json_decode($row['prerequisites'], true) : null
        ];
    }
    
    return $powers;
}

/**
 * Validate that a discipline level is valid (1-5)
 * 
 * @param string $discipline_name The discipline name
 * @param int $level The level to validate
 * @return bool True if valid, false otherwise
 */
function validateDisciplineLevel($discipline_name, $level) {
    if (!is_numeric($level) || $level < 1 || $level > 5) {
        return false;
    }
    
    // Check that discipline exists
    global $conn;
    $discipline_name = mysqli_real_escape_string($conn, $discipline_name);
    $sql = "SELECT id FROM disciplines WHERE name = '$discipline_name'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return false;
    }
    
    $exists = mysqli_num_rows($result) > 0;
    return $exists;
}

/**
 * Get all disciplines for a character with their powers
 * 
 * @param int $character_id The character ID
 * @return array Associative array: discipline_name => ['level' => int, 'powers' => array]
 */
function getCharacterAllDisciplines($character_id) {
    global $conn;
    
    if (!$conn) {
        error_log("getCharacterAllDisciplines: Database connection not available");
        return [];
    }
    
    // Use regular query instead of prepared statement to avoid mysqlnd dependency
    $character_id = (int)$character_id;
    $sql = "SELECT discipline_name, level 
            FROM character_disciplines 
            WHERE character_id = $character_id
            ORDER BY discipline_name ASC";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Failed to query character disciplines: " . mysqli_error($conn));
        return [];
    }
    
    $disciplines = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $discipline_name = $row['discipline_name'];
        $level = (int)$row['level'];
        
        // Try to get powers from the master table
        $powers = getCharacterDisciplinePowers($character_id, $discipline_name);
        
        // If no powers found, it might be a custom path/name
        // Still include the discipline with its level
        $disciplines[$discipline_name] = [
            'level' => $level,
            'powers' => $powers,
            'is_custom' => empty($powers) // Mark as custom if no standard powers found
        ];
    }
    
    return $disciplines;
}

/**
 * Format discipline display string
 * Example: "Celerity 3: Quickness, Sprint, Enhanced Reflexes"
 * 
 * @param string $discipline_name The discipline name
 * @param int $level The character's level
 * @return string Formatted display string
 */
function formatDisciplineDisplay($discipline_name, $level) {
    $powers = getAllDisciplinePowers($discipline_name);
    
    if (empty($powers)) {
        return "{$discipline_name} {$level}";
    }
    
    $power_names = [];
    foreach ($powers as $power) {
        if ($power['level'] <= $level) {
            $power_names[] = $power['power_name'];
        }
    }
    
    if (empty($power_names)) {
        return "{$discipline_name} {$level}";
    }
    
    return "{$discipline_name} {$level}: " . implode(', ', $power_names);
}
?>

