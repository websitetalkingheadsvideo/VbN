<?php
// API endpoint for fetching discipline data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'includes/connect.php';

try {
    $pdo = new PDO("mysql:host=vdb5.pit.pair.com;dbname=working_vbn", "working_64", "pcf577#1");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
}

$action = $_GET['action'] ?? 'all';

try {
    switch ($action) {
        case 'all':
            // Get all discipline data
            $result = getAllDisciplineData($pdo);
            break;
            
        case 'disciplines':
            // Get just the disciplines
            $result = getDisciplines($pdo);
            break;
            
        case 'clans':
            // Get just the clans
            $result = getClans($pdo);
            break;
            
        case 'clan-disciplines':
            // Get clan-discipline access mapping
            $result = getClanDisciplines($pdo);
            break;
            
        default:
            $result = ['success' => false, 'error' => 'Invalid action parameter'];
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getAllDisciplineData($pdo) {
    // Get all disciplines with their powers
    $stmt = $pdo->query("
        SELECT d.id, d.name, d.category, d.description,
               dp.level, dp.name as power_name, dp.description as power_description
        FROM disciplines d
        LEFT JOIN discipline_powers dp ON d.id = dp.discipline_id
        ORDER BY d.name, dp.level
    ");
    
    $disciplines = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $disciplineName = $row['name'];
        
        if (!isset($disciplines[$disciplineName])) {
            $disciplines[$disciplineName] = [];
        }
        
        if ($row['level']) {
            $disciplines[$disciplineName][] = [
                'level' => (int)$row['level'],
                'name' => $row['power_name'],
                'description' => $row['power_description']
            ];
        }
    }
    
    // Get clan-discipline access mapping
    $stmt = $pdo->query("
        SELECT c.name as clan_name, d.name as discipline_name
        FROM clans c
        JOIN clan_disciplines cd ON c.id = cd.clan_id
        JOIN disciplines d ON cd.discipline_id = d.id
        ORDER BY c.name, d.name
    ");
    
    $clanDisciplineAccess = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clanName = $row['clan_name'];
        $disciplineName = $row['discipline_name'];
        
        if (!isset($clanDisciplineAccess[$clanName])) {
            $clanDisciplineAccess[$clanName] = [];
        }
        
        $clanDisciplineAccess[$clanName][] = $disciplineName;
    }
    
    return [
        'success' => true,
        'data' => [
            'disciplinePowers' => $disciplines,
            'clanDisciplineAccess' => $clanDisciplineAccess
        ]
    ];
}

function getDisciplines($pdo) {
    $stmt = $pdo->query("SELECT * FROM disciplines ORDER BY name");
    $disciplines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'success' => true,
        'data' => $disciplines
    ];
}

function getClans($pdo) {
    $stmt = $pdo->query("SELECT * FROM clans ORDER BY name");
    $clans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'success' => true,
        'data' => $clans
    ];
}

function getClanDisciplines($pdo) {
    $stmt = $pdo->query("
        SELECT c.name as clan_name, d.name as discipline_name
        FROM clans c
        JOIN clan_disciplines cd ON c.id = cd.clan_id
        JOIN disciplines d ON cd.discipline_id = d.id
        ORDER BY c.name, d.name
    ");
    
    $clanDisciplineAccess = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
