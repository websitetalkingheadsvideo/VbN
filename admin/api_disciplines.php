<?php
// API endpoint for fetching discipline data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? 'all';

try {
    switch ($action) {
        case 'all':
            // Get all discipline data
            $result = getAllDisciplineData($conn);
            break;
            
        case 'disciplines':
            // Get just the disciplines
            $result = getDisciplines($conn);
            break;
            
        case 'clans':
            // Get just the clans
            $result = getClans($conn);
            break;
            
        case 'clan-disciplines':
            // Get clan-discipline access mapping
            $result = getClanDisciplines($conn);
            break;
            
        default:
            $result = ['success' => false, 'error' => 'Invalid action parameter'];
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getAllDisciplineData($conn) {
    // Get all disciplines with their powers
    // This includes both base disciplines and Thaumaturgy paths (which may have parent_discipline)
    // Include ALL disciplines - don't filter by parent_discipline
    // Check if category column exists first
    $check_category = mysqli_query($conn, "SHOW COLUMNS FROM disciplines LIKE 'category'");
    $has_category = mysqli_num_rows($check_category) > 0;
    
    $category_field = $has_category ? 'd.category,' : '';
    $query = "SELECT d.id, d.name, {$category_field} d.description, d.parent_discipline,
                     dp.power_level, dp.power_name, dp.description as power_description
              FROM disciplines d
              LEFT JOIN discipline_powers dp ON d.id = dp.discipline_id
              ORDER BY d.name, dp.power_level";
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return [
            'success' => false,
            'error' => 'Failed to query disciplines: ' . mysqli_error($conn)
        ];
    }
    
    $disciplines = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $disciplineName = $row['name'];
        
        if (!isset($disciplines[$disciplineName])) {
            $disciplines[$disciplineName] = [];
        }
        
        if ($row['power_level']) {
            $disciplines[$disciplineName][] = [
                'level' => (int)$row['power_level'],
                'name' => $row['power_name'],
                'description' => $row['power_description']
            ];
        }
    }
    
    // Get clan-discipline access mapping (optional - table may not exist)
    $clanDisciplineAccess = [];
    
    // Check if table exists first, then query (handles PHP 8+ exception behavior)
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'clan_disciplines'");
    $table_exists = $table_check && mysqli_num_rows($table_check) > 0;
    
    if ($table_exists) {
        $clan_query = "SELECT c.name as clan_name, d.name as discipline_name
                       FROM clans c
                       JOIN clan_disciplines cd ON c.id = cd.clan_id
                       JOIN disciplines d ON cd.discipline_id = d.id
                       ORDER BY c.name, d.name";
        
        // Suppress errors and check result
        $clan_result = @mysqli_query($conn, $clan_query);
        if ($clan_result) {
            while ($row = mysqli_fetch_assoc($clan_result)) {
                $clanName = $row['clan_name'];
                $disciplineName = $row['discipline_name'];
                
                if (!isset($clanDisciplineAccess[$clanName])) {
                    $clanDisciplineAccess[$clanName] = [];
                }
                
                $clanDisciplineAccess[$clanName][] = $disciplineName;
            }
            mysqli_free_result($clan_result);
        } else {
            // Query failed but table exists - log and continue
            error_log("clan_disciplines query failed: " . mysqli_error($conn));
        }
    } else {
        error_log("clan_disciplines table does not exist, skipping clan-discipline mapping");
    }
    
    return [
        'success' => true,
        'data' => [
            'disciplinePowers' => $disciplines,
            'clanDisciplineAccess' => $clanDisciplineAccess
        ]
    ];
}

function getDisciplines($conn) {
    $query = "SELECT id, name, category, description FROM disciplines ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [
            'success' => false,
            'error' => 'Failed to query disciplines: ' . mysqli_error($conn)
        ];
    }
    
    $disciplines = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $disciplines[] = $row;
    }
    
    return [
        'success' => true,
        'data' => $disciplines
    ];
}

function getClans($conn) {
    $query = "SELECT id, name, nickname, description FROM clans ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [
            'success' => false,
            'error' => 'Failed to query clans: ' . mysqli_error($conn)
        ];
    }
    
    $clans = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $clans[] = $row;
    }
    
    return [
        'success' => true,
        'data' => $clans
    ];
}

function getClanDisciplines($conn) {
    $query = "SELECT c.name as clan_name, d.name as discipline_name
              FROM clans c
              JOIN clan_disciplines cd ON c.id = cd.clan_id
              JOIN disciplines d ON cd.discipline_id = d.id
              ORDER BY c.name, d.name";
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return [
            'success' => false,
            'error' => 'Failed to query clan disciplines: ' . mysqli_error($conn)
        ];
    }
    
    $clanDisciplineAccess = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $clanName = $row['clan_name'];
        $disciplineName = $row['discipline_name'];
        
        if (!isset($clanDisciplineAccess[$clanName])) {
            $clanDisciplineAccess[$clanName] = [];
        }
        
        $clanDisciplineAccess[$clanName][] = $disciplineName;
    }
    
    return [
        'success' => true,
        'data' => $clanDisciplineAccess
    ];
}
?>
