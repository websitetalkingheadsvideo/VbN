<?php
/**
 * Discipline Data Converter
 * 
 * Converts discipline data from JSON format to database-ready format
 * for character_disciplines table
 * 
 * JSON Format:
 * [
 *   {
 *     "name": "Auspex",
 *     "level": 3,
 *     "powers": [
 *       {"level": 1, "power": "Heightened Senses"},
 *       {"level": 2, "power": "The Spirit's Touch"},
 *       {"level": 3, "power": "Psychic Projection"}
 *     ]
 *   }
 * ]
 * 
 * Database Format:
 * [
 *   ["discipline_name" => "Auspex", "level" => 1, "xp_cost" => 0],
 *   ["discipline_name" => "Auspex", "level" => 2, "xp_cost" => 0],
 *   ["discipline_name" => "Auspex", "level" => 3, "xp_cost" => 0]
 * ]
 */

/**
 * Calculate XP cost for a discipline level
 * Rules:
 * - First 3 dots (levels 1-3) are FREE (0 XP)
 * - Dots 4-5 cost 3 XP each
 * 
 * @param int $level The discipline level (1-5)
 * @return int The XP cost for that level
 */
function calculateDisciplineXPCost(int $level): int
{
    if ($level >= 1 && $level <= 3) {
        return 0;
    } elseif ($level === 4 || $level === 5) {
        return 3;
    }
    return 0;
}

/**
 * Convert discipline data from JSON format to database format
 * 
 * @param array $disciplines_json Array of discipline objects from JSON
 * @return array Array of database-ready rows, each with discipline_name, level, and xp_cost
 */
function convertDisciplinesToDatabase(array $disciplines_json): array
{
    $result = [];
    
    foreach ($disciplines_json as $discipline) {
        $discipline_name = $discipline['name'] ?? null;
        
        if (!$discipline_name) {
            continue; // Skip invalid entries
        }
        
        // Check if this discipline has powers defined
        if (!empty($discipline['powers']) && is_array($discipline['powers'])) {
            // Use powers array to determine levels
            foreach ($discipline['powers'] as $power) {
                $level = $power['level'] ?? null;
                
                if ($level && is_numeric($level) && $level >= 1 && $level <= 5) {
                    $result[] = [
                        'discipline_name' => $discipline_name,
                        'level' => (int)$level,
                        'xp_cost' => calculateDisciplineXPCost((int)$level)
                    ];
                }
            }
        } elseif (isset($discipline['level']) && is_numeric($discipline['level'])) {
            // No powers defined, use the level field to create sequential levels
            $max_level = (int)$discipline['level'];
            
            for ($level = 1; $level <= $max_level && $level <= 5; $level++) {
                $result[] = [
                    'discipline_name' => $discipline_name,
                    'level' => $level,
                    'xp_cost' => calculateDisciplineXPCost($level)
                ];
            }
        }
    }
    
    return $result;
}

/**
 * Insert converted discipline data into database
 * 
 * @param mysqli $conn Database connection
 * @param int $character_id Character ID to associate disciplines with
 * @param array $disciplines_db Database-ready discipline array
 * @return array Result with 'success' boolean, 'count' int, and 'errors' array
 */
function insertDisciplinesIntoDatabase(mysqli $conn, int $character_id, array $disciplines_db): array
{
    $errors = [];
    $count = 0;
    
    $stmt = $conn->prepare("
        INSERT INTO character_disciplines (character_id, discipline_name, level, xp_cost)
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        return [
            'success' => false,
            'count' => 0,
            'errors' => ["Prepare failed: " . $conn->error]
        ];
    }
    
    foreach ($disciplines_db as $row) {
        $stmt->bind_param(
            "isii",
            $character_id,
            $row['discipline_name'],
            $row['level'],
            $row['xp_cost']
        );
        
        if (!$stmt->execute()) {
            $errors[] = "Failed to insert {$row['discipline_name']} level {$row['level']}: " . $stmt->error;
        } else {
            $count++;
        }
    }
    
    $stmt->close();
    
    return [
        'success' => count($errors) === 0,
        'count' => $count,
        'errors' => $errors
    ];
}

/**
 * Get summary statistics about converted disciplines
 * 
 * @param array $disciplines_db Database-ready discipline array
 * @return array Statistics including total_xp, total_levels, discipline_counts
 */
function getDisciplineStatistics(array $disciplines_db): array
{
    $stats = [
        'total_xp' => 0,
        'total_levels' => count($disciplines_db),
        'discipline_counts' => []
    ];
    
    foreach ($disciplines_db as $row) {
        $stats['total_xp'] += $row['xp_cost'];
        
        $name = $row['discipline_name'];
        if (!isset($stats['discipline_counts'][$name])) {
            $stats['discipline_counts'][$name] = 0;
        }
        $stats['discipline_counts'][$name]++;
    }
    
    return $stats;
}



















