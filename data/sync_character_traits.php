<?php
/**
 * Character Traits Synchronization System
 * 
 * This script examines all characters in the database, matches them with JSON files,
 * and ensures they have traits either imported from JSON or generated based on character data.
 * 
 * Usage:
 *   Web: https://vbn.talkingheads.video/data/sync_character_traits.php?character_id=123
 *   Web (all): https://vbn.talkingheads.video/data/sync_character_traits.php?all=1
 *   CLI: php sync_character_traits.php [--character-id=123] [--all] [--dry-run]
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$is_cli = PHP_SAPI === 'cli';
$options = [];

if ($is_cli) {
    $options = getopt('', ['character-id:', 'all', 'dry-run', 'help']);

    if (isset($options['help'])) {
        echo "Character Traits Synchronization System\n\n";
        echo "Usage:\n";
        echo "  php sync_character_traits.php [options]\n\n";
        echo "Options:\n";
        echo "  --character-id=N    Process single character by ID\n";
        echo "  --all               Process all characters\n";
        echo "  --dry-run           Show what would be done without making changes\n";
        echo "  --help              Show this help message\n\n";
        exit(0);
    }
}

$dry_run = false;
$process_all = false;
$character_id = null;

if ($is_cli) {
    $dry_run = isset($options['dry-run']);
    $process_all = isset($options['all']);
    if (isset($options['character-id'])) {
        $character_id = (int)$options['character-id'];
    }
} else {
    $dry_run = isset($_GET['dry_run']) && $_GET['dry_run'] === '1';
    $process_all = isset($_GET['all']) && $_GET['all'] == '1';
    if (isset($_GET['character_id'])) {
        $character_id = (int)$_GET['character_id'];
    }
}

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    if (!$is_cli) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    die("❌ Database connection failed: " . mysqli_connect_error() . "\n");
}

if (!$is_cli) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Character Traits Synchronization System\n";
    echo str_repeat('=', 65) . "\n";
}

function terminate_script($is_cli, $code = 0) {
    if ($is_cli) {
        exit((int)$code);
    }
    if ((int)$code !== 0) {
        http_response_code(200);
    }
    exit;
}

register_shutdown_function(function () use ($is_cli) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!$is_cli) {
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo "\nFatal Error: " . $error['message'] . " in " . $error['file'] . ':' . $error['line'] . "\n";
    }
});

// Set time limit for large batches
set_time_limit(600); // 10 minutes

/**
 * Logger class for output and logging
 */
class Logger {
    /** @var array<int, string> */
    private $logs = [];
    /** @var bool */
    private $verbose = true;
    
    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message";
        $this->logs[] = $log_entry;
        
        if ($this->verbose) {
            switch ($level) {
                case 'ERROR':
                    $prefix = '❌';
                    break;
                case 'WARNING':
                    $prefix = '⚠️';
                    break;
                case 'SUCCESS':
                    $prefix = '✅';
                    break;
                case 'INFO':
                    $prefix = 'ℹ️';
                    break;
                default:
                    $prefix = '•';
            }
            echo "$prefix $message\n";
        }
    }
    
    public function getLogs() {
        return $this->logs;
    }
    
    public function setVerbose($verbose) {
        $this->verbose = (bool)$verbose;
    }
}

/**
 * Character Trait Syncer
 */
class CharacterTraitSyncer {
    private $conn;
    /** @var Logger */
    private $logger;
    /** @var bool */
    private $dry_run;
    /** @var array<string, int> */
    private $stats = [
        'processed' => 0,
        'imported_from_json' => 0,
        'generated' => 0,
        'errors' => 0,
        'no_json_file' => 0,
        'already_has_traits' => 0
    ];
    /** @var bool */
    private $hasXpCostColumn = false;
    
    public function __construct($conn, Logger $logger, $dry_run = false) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->dry_run = (bool)$dry_run;

        $column_check = mysqli_query($this->conn, "SHOW COLUMNS FROM character_traits LIKE 'xp_cost'");
        if ($column_check) {
            $this->hasXpCostColumn = mysqli_num_rows($column_check) > 0;
            mysqli_free_result($column_check);
        }
    }
    
    /**
     * Get all characters from database
     */
    public function getAllCharacters() {
        $query = "SELECT id, character_name, clan, nature, demeanor, concept, biography 
                  FROM characters 
                  ORDER BY id";
        $result = mysqli_query($this->conn, $query);
        
        if (!$result) {
            $this->logger->log("Failed to query characters: " . mysqli_error($this->conn), 'ERROR');
            return [];
        }
        
        $characters = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $characters[] = $row;
        }
        
        return $characters;
    }
    
    /**
     * Get single character by ID
     */
    public function getCharacterById($character_id) {
        $query = "SELECT id, character_name, clan, nature, demeanor, concept, biography 
                  FROM characters 
                  WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $character_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $character = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $character ?: null;
    }
    
    /**
     * Find matching JSON file for character
     */
    public function findJsonFile($character_name) {
        // Directories to search
        $directories = [
            __DIR__ . '/../reference/Characters/Added to Database/',
            __DIR__ . '/../reference/Characters/'
        ];
        
        // Try exact match first
        foreach ($directories as $dir) {
            $exact_file = $dir . $character_name . '.json';
            if (file_exists($exact_file)) {
                return $exact_file;
            }
        }
        
        // Try with URL encoding
        foreach ($directories as $dir) {
            $encoded_file = $dir . urlencode($character_name) . '.json';
            if (file_exists($encoded_file)) {
                return $encoded_file;
            }
        }
        
        // Fuzzy match - scan all JSON files
        foreach ($directories as $dir) {
            if (!is_dir($dir)) continue;
            
            $files = glob($dir . '*.json');
            $best_match = null;
            $best_score = 0;
            
            foreach ($files as $file) {
                $filename = basename($file, '.json');
                similar_text(strtolower($character_name), strtolower($filename), $percent);
                
                if ($percent > $best_score && $percent > 80) {
                    $best_score = $percent;
                    $best_match = $file;
                }
            }
            
            if ($best_match) {
                return $best_match;
            }
        }
        
        return null;
    }
    
    /**
     * Load and parse JSON file
     */
    public function loadJsonFile($filepath) {
        if (!file_exists($filepath)) {
            return null;
        }
        
        $content = file_get_contents($filepath);
        if ($content === false) {
            $this->logger->log("Failed to read JSON file: $filepath", 'ERROR');
            return null;
        }
        
        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log("JSON decode error in $filepath: " . json_last_error_msg(), 'ERROR');
            return null;
        }
        
        return $data;
    }
    
    /**
     * Extract traits from JSON data
     */
    public function extractTraitsFromJson($json_data) {
        $traits = [
            'positive' => ['Physical' => [], 'Social' => [], 'Mental' => []],
            'negative' => ['Physical' => [], 'Social' => [], 'Mental' => []]
        ];
        
        if (!is_array($json_data)) {
            return $traits;
        }

        // Standard format: traits.Physical, traits.Social, traits.Mental
        if (isset($json_data['traits']) && is_array($json_data['traits'])) {
            foreach (['Physical', 'Social', 'Mental'] as $category) {
                if (isset($json_data['traits'][$category]) && is_array($json_data['traits'][$category])) {
                    foreach ($json_data['traits'][$category] as $trait) {
                        if (is_string($trait)) {
                            $traits['positive'][$category][] = $trait;
                        } elseif (is_array($trait) && !empty($trait)) {
                            // Handle format like [{"Strength": 2}]
                            $trait_name = array_key_first($trait);
                            if ($trait_name) {
                                $traits['positive'][$category][] = $trait_name;
                            }
                        }
                    }
                }
            }
        }
        
        // Negative traits
        if (isset($json_data['negativeTraits']) && is_array($json_data['negativeTraits'])) {
            foreach (['Physical', 'Social', 'Mental'] as $category) {
                if (isset($json_data['negativeTraits'][$category]) && is_array($json_data['negativeTraits'][$category])) {
                    foreach ($json_data['negativeTraits'][$category] as $trait) {
                        if (is_string($trait)) {
                            $traits['negative'][$category][] = $trait;
                        }
                    }
                }
            }
        }
        
        // Alternative format: attributes (like Layla al-Sahr.json)
        if (isset($json_data['attributes']) && is_array($json_data['attributes'])) {
            // Parse attributes like "Excellent (Dexterous, Graceful, Quick, Precise)"
            foreach (['physical', 'social', 'mental'] as $attr_key) {
                if (isset($json_data['attributes'][$attr_key]) && is_string($json_data['attributes'][$attr_key])) {
                    $attr_value = $json_data['attributes'][$attr_key];
                    // Extract traits from parentheses
                    if (preg_match('/\(([^)]+)\)/', $attr_value, $matches)) {
                        $trait_list = explode(',', $matches[1]);
                        $category = ucfirst($attr_key);
                        foreach ($trait_list as $trait) {
                            $trait = trim($trait);
                            if (!empty($trait)) {
                                $traits['positive'][$category][] = $trait;
                            }
                        }
                    }
                }
            }
        }
        
        return $traits;
    }
    
    /**
     * Check if character already has traits in database
     */
    public function characterHasTraits($character_id) {
        $query = "SELECT COUNT(*) as count FROM character_traits WHERE character_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $character_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Import traits from JSON to database
     */
    public function importTraits($character_id, $traits) {
        if ($this->dry_run) {
            $count = 0;
            foreach ($traits as $type => $categories) {
                foreach ($categories as $category => $trait_list) {
                    $count += count($trait_list);
                }
            }
            $this->logger->log("Would import $count traits for character ID $character_id", 'INFO');
            return $count;
        }
        
        $imported = 0;
        
        mysqli_begin_transaction($this->conn);
        
        try {
            // Get existing traits to avoid duplicates
            $existing_query = "SELECT trait_name, trait_category, trait_type 
                              FROM character_traits 
                              WHERE character_id = ?";
            $stmt = mysqli_prepare($this->conn, $existing_query);
            $existing = [];
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $character_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $key = strtolower($row['trait_name']) . '|' . $row['trait_category'] . '|' . $row['trait_type'];
                    $existing[$key] = true;
                }
                mysqli_stmt_close($stmt);
            } else {
                $this->logger->log("Failed to prepare existing traits query: " . mysqli_error($this->conn), 'ERROR');
            }
            
            if ($this->hasXpCostColumn) {
                $insert_stmt = mysqli_prepare($this->conn, "
                    INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type, xp_cost)
                    VALUES (?, ?, ?, ?, 0)
                ");
            } else {
                $insert_stmt = mysqli_prepare($this->conn, "
                    INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type)
                    VALUES (?, ?, ?, ?)
                ");
            }

            if (!$insert_stmt) {
                $this->logger->log("Failed to prepare trait insert: " . mysqli_error($this->conn), 'ERROR');
                mysqli_rollback($this->conn);
                return 0;
            }
            
            foreach ($traits as $type => $categories) {
                foreach ($categories as $category => $trait_list) {
                    foreach ($trait_list as $trait_name) {
                        $key = strtolower($trait_name) . '|' . $category . '|' . $type;
                        if (!isset($existing[$key])) {
                            mysqli_stmt_bind_param($insert_stmt, "isss", $character_id, $trait_name, $category, $type);
                            if (!mysqli_stmt_execute($insert_stmt)) {
                                $this->logger->log("Failed to insert trait '$trait_name' ($category/$type): " . mysqli_stmt_error($insert_stmt), 'ERROR');
                            } else {
                                $imported++;
                            }
                        }
                    }
                }
            }
            
            if ($insert_stmt) {
                mysqli_stmt_close($insert_stmt);
            }
            mysqli_commit($this->conn);
            
            $this->logger->log("Imported $imported traits for character ID $character_id", 'SUCCESS');
            return $imported;
            
        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            $this->logger->log("Failed to import traits: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Get character abilities from database
     */
    private function getCharacterAbilities($character_id) {
        $query = "SELECT ability_name, level FROM character_abilities WHERE character_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $character_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $abilities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $abilities[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $abilities;
    }
    
    /**
     * Get character disciplines from database
     */
    private function getCharacterDisciplines($character_id) {
        $query = "SELECT discipline_name, level FROM character_disciplines WHERE character_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $character_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $disciplines = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $disciplines[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $disciplines;
    }
    
    /**
     * Generate traits based on abilities
     */
    private function generateTraitsFromAbilities($abilities) {
        if (!is_array($abilities)) {
            return ['Physical' => [], 'Social' => [], 'Mental' => []];
        }
        $traits = ['Physical' => [], 'Social' => [], 'Mental' => []];
        
        // Ability to trait mappings
        $ability_traits = [
            'Physical' => [
                'Athletics' => ['Quick', 'Agile', 'Nimble', 'Coordinated'],
                'Brawl' => ['Strong', 'Tough', 'Aggressive', 'Resilient'],
                'Melee' => ['Strong', 'Coordinated', 'Precise', 'Quick'],
                'Stealth' => ['Subtle', 'Graceful', 'Quiet', 'Observant'],
                'Dodge' => ['Quick', 'Agile', 'Nimble', 'Alert'],
                'Firearms' => ['Precise', 'Steady', 'Alert', 'Coordinated'],
                'Alertness' => ['Alert', 'Observant', 'Perceptive']
            ],
            'Social' => [
                'Expression' => ['Eloquent', 'Charismatic', 'Persuasive', 'Articulate'],
                'Performance' => ['Charismatic', 'Elegant', 'Alluring', 'Expressive'],
                'Leadership' => ['Commanding', 'Inspiring', 'Confident', 'Charismatic'],
                'Subterfuge' => ['Cunning', 'Persuasive', 'Diplomatic', 'Charming'],
                'Intimidation' => ['Intimidating', 'Commanding', 'Imposing', 'Forceful'],
                'Etiquette' => ['Elegant', 'Poised', 'Diplomatic', 'Refined'],
                'Empathy' => ['Empathetic', 'Perceptive', 'Intuitive', 'Understanding']
            ],
            'Mental' => [
                'Academics' => ['Studious', 'Learned', 'Intelligent', 'Analytical'],
                'Investigation' => ['Observant', 'Analytical', 'Perceptive', 'Clever'],
                'Occult' => ['Studious', 'Perceptive', 'Intuitive', 'Mystical'],
                'Science' => ['Analytical', 'Intelligent', 'Methodical', 'Studious'],
                'Computer' => ['Analytical', 'Intelligent', 'Focused', 'Precise'],
                'Medicine' => ['Observant', 'Precise', 'Methodical', 'Caring'],
                'Finance' => ['Analytical', 'Clever', 'Methodical', 'Perceptive']
            ]
        ];
        
        foreach ($abilities as $ability) {
            $ability_name = $ability['ability_name'];
            $level = (int)$ability['level'];
            
            // Generate traits for high-level abilities (level 4+)
            if ($level >= 4) {
                foreach ($ability_traits as $category => $mappings) {
                    if (isset($mappings[$ability_name])) {
                        $available_traits = $mappings[$ability_name];
                        // Pick 1-2 traits based on level
                        $count = $level >= 5 ? 2 : 1;
                        $selected = array_slice($available_traits, 0, $count);
                        foreach ($selected as $trait) {
                            if (!in_array($trait, $traits[$category])) {
                                $traits[$category][] = $trait;
                            }
                        }
                    }
                }
            }
        }
        
        return $traits;
    }
    
    /**
     * Generate traits based on disciplines
     */
    private function generateTraitsFromDisciplines($disciplines) {
        if (!is_array($disciplines)) {
            return ['Physical' => [], 'Social' => [], 'Mental' => []];
        }
        $traits = ['Physical' => [], 'Social' => [], 'Mental' => []];
        
        $discipline_traits = [
            'Celerity' => ['Physical' => ['Quick', 'Nimble', 'Agile']],
            'Potence' => ['Physical' => ['Strong', 'Powerful', 'Tough']],
            'Fortitude' => ['Physical' => ['Tough', 'Resilient', 'Enduring']],
            'Presence' => ['Social' => ['Charismatic', 'Commanding', 'Alluring', 'Magnetic']],
            'Dominate' => ['Social' => ['Intimidating', 'Commanding', 'Forceful']],
            'Auspex' => ['Mental' => ['Perceptive', 'Intuitive', 'Observant', 'Insightful']],
            'Obfuscate' => ['Mental' => ['Subtle', 'Unassuming'], 'Social' => ['Cunning', 'Deceptive']],
            'Animalism' => ['Mental' => ['Instinctive', 'Perceptive'], 'Social' => ['Empathetic']],
            'Protean' => ['Physical' => ['Wild', 'Adaptable'], 'Mental' => ['Instinctive']],
            'Thaumaturgy' => ['Mental' => ['Studious', 'Analytical', 'Focused', 'Disciplined']],
            'Necromancy' => ['Mental' => ['Studious', 'Mystical', 'Disciplined']],
            'Quietus' => ['Physical' => ['Precise', 'Deadly'], 'Mental' => ['Focused', 'Disciplined']],
            'Serpentis' => ['Social' => ['Seductive', 'Alluring'], 'Physical' => ['Graceful']],
            'Vicissitude' => ['Mental' => ['Creative', 'Artistic'], 'Physical' => ['Adaptable']]
        ];
        
        foreach ($disciplines as $discipline) {
            $name = $discipline['discipline_name'];
            // Handle Thaumaturgy paths
            if (stripos($name, 'Thaumaturgy') !== false) {
                $name = 'Thaumaturgy';
            }
            if (stripos($name, 'Necromancy') !== false) {
                $name = 'Necromancy';
            }
            
            if (isset($discipline_traits[$name])) {
                foreach ($discipline_traits[$name] as $category => $trait_list) {
                    foreach ($trait_list as $trait) {
                        if (!in_array($trait, $traits[$category])) {
                            $traits[$category][] = $trait;
                        }
                    }
                }
            }
        }
        
        return $traits;
    }
    
    /**
     * Generate traits based on clan
     */
    private function generateTraitsFromClan($clan) {
        $traits = ['Physical' => [], 'Social' => [], 'Mental' => []];
        $negative = [];
        
        $clan_traits = [
            'Brujah' => ['Physical' => ['Quick', 'Aggressive'], 'Social' => ['Passionate', 'Intense']],
            'Toreador' => ['Social' => ['Charismatic', 'Elegant', 'Alluring', 'Artistic']],
            'Tremere' => ['Mental' => ['Analytical', 'Studious', 'Disciplined', 'Methodical']],
            'Ventrue' => ['Social' => ['Commanding', 'Poised', 'Diplomatic', 'Confident']],
            'Gangrel' => ['Physical' => ['Wild', 'Tough', 'Resilient'], 'Mental' => ['Instinctive']],
            'Malkavian' => ['Mental' => ['Perceptive', 'Intuitive', 'Unpredictable', 'Insightful']],
            'Nosferatu' => ['Mental' => ['Clever', 'Resourceful'], 'negative' => ['Social' => ['Hideous']]],
            'Assamite' => ['Physical' => ['Quick', 'Precise'], 'Mental' => ['Focused', 'Disciplined']],
            'Banu Haqim' => ['Physical' => ['Quick', 'Precise'], 'Mental' => ['Focused', 'Disciplined']],
            'Lasombra' => ['Social' => ['Intimidating', 'Commanding'], 'Mental' => ['Cunning']],
            'Tzimisce' => ['Mental' => ['Analytical', 'Artistic'], 'Physical' => ['Adaptable']],
            'Ravnos' => ['Social' => ['Cunning', 'Charming'], 'Mental' => ['Clever']],
            'Followers of Set' => ['Social' => ['Seductive', 'Persuasive', 'Alluring']],
            'Giovanni' => ['Mental' => ['Studious', 'Analytical'], 'Social' => ['Businesslike']],
            'Caitiff' => ['Physical' => ['Adaptable'], 'Social' => ['Versatile'], 'Mental' => ['Flexible']]
        ];
        
        // Check for clan name variations
        $clan_lower = strtolower($clan);
        foreach ($clan_traits as $clan_name => $clan_data) {
            if (stripos($clan_lower, strtolower($clan_name)) !== false) {
                foreach ($clan_data as $category => $trait_list) {
                    if ($category === 'negative') {
                        // Handle negative traits
                        foreach ($trait_list as $neg_category => $neg_trait_list) {
                            foreach ($neg_trait_list as $trait) {
                                $negative[$neg_category][] = $trait;
                            }
                        }
                    } elseif (in_array($category, ['Physical', 'Social', 'Mental'])) {
                        // Handle positive traits
                        foreach ($trait_list as $trait) {
                            if (!in_array($trait, $traits[$category])) {
                                $traits[$category][] = $trait;
                            }
                        }
                    }
                }
                break;
            }
        }
        
        return ['positive' => $traits, 'negative' => ['Physical' => [], 'Social' => $negative['Social'] ?? [], 'Mental' => []]];
    }
    
    /**
     * Generate traits based on nature/demeanor
     */
    private function generateTraitsFromNatureDemeanor($nature, $demeanor) {
        $traits = ['Physical' => [], 'Social' => [], 'Mental' => []];
        
        $archetype_traits = [
            'Architect' => ['Mental' => ['Analytical', 'Methodical', 'Intelligent']],
            'Bon Vivant' => ['Social' => ['Charismatic', 'Hedonistic', 'Elegant']],
            'Bravo' => ['Physical' => ['Aggressive', 'Tough'], 'Social' => ['Intimidating']],
            'Caregiver' => ['Social' => ['Empathetic', 'Nurturing', 'Caring']],
            'Child' => ['Social' => ['Innocent', 'Playful'], 'Mental' => ['Curious']],
            'Conformist' => ['Social' => ['Diplomatic', 'Adaptable'], 'Mental' => ['Methodical']],
            'Conniver' => ['Social' => ['Cunning', 'Persuasive'], 'Mental' => ['Clever']],
            'Curmudgeon' => ['Social' => ['Intimidating', 'Forceful'], 'Mental' => ['Analytical']],
            'Deviant' => ['Mental' => ['Unpredictable', 'Creative'], 'Social' => ['Unconventional']],
            'Director' => ['Social' => ['Commanding', 'Confident'], 'Mental' => ['Analytical']],
            'Fanatic' => ['Social' => ['Intense', 'Passionate'], 'Mental' => ['Focused']],
            'Gallant' => ['Social' => ['Charismatic', 'Elegant', 'Charming']],
            'Judge' => ['Mental' => ['Analytical', 'Observant'], 'Social' => ['Commanding']],
            'Loner' => ['Mental' => ['Independent', 'Observant'], 'Social' => ['Reserved']],
            'Martyr' => ['Social' => ['Selfless', 'Caring'], 'Mental' => ['Determined']],
            'Monster' => ['Physical' => ['Aggressive', 'Intimidating'], 'Social' => ['Callous']],
            'Pedagogue' => ['Mental' => ['Intelligent', 'Studious'], 'Social' => ['Persuasive']],
            'Penitent' => ['Mental' => ['Disciplined', 'Methodical'], 'Social' => ['Reserved']],
            'Perfectionist' => ['Mental' => ['Analytical', 'Methodical', 'Focused']],
            'Rebel' => ['Social' => ['Passionate', 'Intense'], 'Mental' => ['Independent']],
            'Rogue' => ['Social' => ['Cunning', 'Charming'], 'Mental' => ['Clever']],
            'Survivor' => ['Physical' => ['Tough', 'Resilient'], 'Mental' => ['Observant']],
            'Traditionalist' => ['Mental' => ['Methodical', 'Disciplined'], 'Social' => ['Reserved']],
            'Visionary' => ['Mental' => ['Creative', 'Intuitive'], 'Social' => ['Inspiring']]
        ];
        
        foreach ([$nature, $demeanor] as $archetype) {
            $archetype_lower = strtolower($archetype);
            foreach ($archetype_traits as $name => $trait_data) {
                if (stripos($archetype_lower, strtolower($name)) !== false) {
                    foreach ($trait_data as $category => $trait_list) {
                        foreach ($trait_list as $trait) {
                            if (!in_array($trait, $traits[$category])) {
                                $traits[$category][] = $trait;
                            }
                        }
                    }
                    break;
                }
            }
        }
        
        return $traits;
    }
    
    /**
     * Extract traits from biography text
     */
    private function generateTraitsFromBiography($biography) {
        $traits = ['Physical' => [], 'Social' => [], 'Mental' => []];
        
        if (empty($biography)) {
            return $traits;
        }
        
        $biography_lower = strtolower($biography);
        
        // Keyword to trait mappings
        $keyword_traits = [
            'Physical' => [
                'strong' => 'Strong',
                'powerful' => 'Strong',
                'quick' => 'Quick',
                'fast' => 'Quick',
                'agile' => 'Agile',
                'nimble' => 'Nimble',
                'graceful' => 'Graceful',
                'athletic' => 'Athletic',
                'tough' => 'Tough',
                'resilient' => 'Resilient',
                'precise' => 'Precise',
                'coordinated' => 'Coordinated'
            ],
            'Social' => [
                'charismatic' => 'Charismatic',
                'charming' => 'Charming',
                'elegant' => 'Elegant',
                'persuasive' => 'Persuasive',
                'diplomatic' => 'Diplomatic',
                'commanding' => 'Commanding',
                'intimidating' => 'Intimidating',
                'seductive' => 'Seductive',
                'alluring' => 'Alluring',
                'eloquent' => 'Eloquent'
            ],
            'Mental' => [
                'intelligent' => 'Intelligent',
                'smart' => 'Intelligent',
                'clever' => 'Clever',
                'analytical' => 'Analytical',
                'observant' => 'Observant',
                'perceptive' => 'Perceptive',
                'studious' => 'Studious',
                'learned' => 'Learned',
                'focused' => 'Focused',
                'disciplined' => 'Disciplined',
                'methodical' => 'Methodical'
            ]
        ];
        
        foreach ($keyword_traits as $category => $keywords) {
            foreach ($keywords as $keyword => $trait) {
                if (stripos($biography_lower, $keyword) !== false) {
                    if (!in_array($trait, $traits[$category])) {
                        $traits[$category][] = $trait;
                    }
                }
            }
        }
        
        // Limit biography-generated traits to avoid over-generation
        foreach ($traits as $category => &$trait_list) {
            $trait_list = array_slice($trait_list, 0, 2);
        }
        
        return $traits;
    }
    
    /**
     * Generate traits based on all character data
     */
    public function generateTraits($character_id, $character) {
        if (!is_array($character)) {
            $character = [];
        }
        $generated = [
            'positive' => ['Physical' => [], 'Social' => [], 'Mental' => []],
            'negative' => ['Physical' => [], 'Social' => [], 'Mental' => []]
        ];
        
        // Get additional character data
        $abilities = $this->getCharacterAbilities($character_id);
        $disciplines = $this->getCharacterDisciplines($character_id);
        
        // Generate from various sources
        $ability_traits = $this->generateTraitsFromAbilities($abilities);
        $discipline_traits = $this->generateTraitsFromDisciplines($disciplines);
        $clan_traits = $this->generateTraitsFromClan($character['clan'] ?? '');
        $nature_traits = $this->generateTraitsFromNatureDemeanor(
            $character['nature'] ?? '',
            $character['demeanor'] ?? ''
        );
        $biography_traits = $this->generateTraitsFromBiography($character['biography'] ?? '');
        
        // Combine all positive traits
        foreach (['Physical', 'Social', 'Mental'] as $category) {
            $combined = array_merge(
                $ability_traits[$category] ?? [],
                $discipline_traits[$category] ?? [],
                $clan_traits['positive'][$category] ?? [],
                $nature_traits[$category] ?? [],
                $biography_traits[$category] ?? []
            );
            
            // Remove duplicates and limit to 3-5 per category
            $combined = array_unique($combined);
            $generated['positive'][$category] = array_slice($combined, 0, 5);
        }
        
        // Add negative traits from clan
        if (!empty($clan_traits['negative'])) {
            foreach ($clan_traits['negative'] as $category => $negative_list) {
                $generated['negative'][$category] = array_slice($negative_list, 0, 2);
            }
        }
        
        return $generated;
    }
    
    /**
     * Process a single character
     */
    public function processCharacter($character) {
        if (!is_array($character)) {
            $this->logger->log("Invalid character data", 'ERROR');
            return;
        }
        $this->stats['processed']++;
        $character_id = (int)$character['id'];
        $character_name = $character['character_name'];
        
        $this->logger->log("Processing: $character_name (ID: $character_id)", 'INFO');
        
        // Check if already has traits
        if ($this->characterHasTraits($character_id)) {
            $this->stats['already_has_traits']++;
            $this->logger->log("Character already has traits, skipping", 'INFO');
            return;
        }
        
        // Find JSON file
        $json_file = $this->findJsonFile($character_name);
        $traits = ['positive' => ['Physical' => [], 'Social' => [], 'Mental' => []],
                   'negative' => ['Physical' => [], 'Social' => [], 'Mental' => []]];
        
        if ($json_file) {
            $this->logger->log("Found JSON file: " . basename($json_file), 'SUCCESS');
            
            // Load JSON
            $json_data = $this->loadJsonFile($json_file);
            if ($json_data) {
                // Extract traits
                $traits = $this->extractTraitsFromJson($json_data);
            }
        } else {
            $this->stats['no_json_file']++;
            $this->logger->log("No JSON file found for character", 'WARNING');
        }
        
        // Check if any traits found in JSON
        $has_traits = false;
        foreach ($traits as $type => $categories) {
            foreach ($categories as $category => $trait_list) {
                if (!empty($trait_list)) {
                    $has_traits = true;
                    break 2;
                }
            }
        }
        
        // If no traits in JSON, generate them
        if (!$has_traits) {
            $this->logger->log("No traits in JSON, generating based on character data", 'INFO');
            $traits = $this->generateTraits($character_id, $character);
            $this->stats['generated'] += array_sum(array_map('count', $traits['positive'])) + 
                                         array_sum(array_map('count', $traits['negative']));
        }
        
        // Import traits (either from JSON or generated)
        if (!empty($traits['positive']) || !empty($traits['negative'])) {
            $imported = $this->importTraits($character_id, $traits);
            if ($imported > 0 && $has_traits) {
                $this->stats['imported_from_json'] += $imported;
            }
        }
    }
    
    /**
     * Get statistics
     */
    public function getStats() {
        return $this->stats;
    }
}

// Main execution
$logger = new Logger();
$logger->log("Character Traits Synchronization System", 'INFO');
$logger->log("Dry run mode: " . ($dry_run ? 'YES' : 'NO'), 'INFO');
echo "\n";

$syncer = new CharacterTraitSyncer($conn, $logger, $dry_run);

try {
    if ($character_id) {
        // Process single character
        $character = $syncer->getCharacterById($character_id);
        if (!$character) {
            $logger->log("Character with ID $character_id not found", 'ERROR');
            terminate_script($is_cli, 1);
        }
        $syncer->processCharacter($character);
    } elseif ($process_all) {
        // Process all characters
        $logger->log("Processing all characters...", 'INFO');
        $characters = $syncer->getAllCharacters();
        $total = count($characters);
        $logger->log("Found $total characters to process", 'INFO');
        
        foreach ($characters as $index => $character) {
            $progress = $index + 1;
            $logger->log("Progress: $progress/$total", 'INFO');
            $syncer->processCharacter($character);
        }
    } else {
        $logger->log("No action specified. Use --all or --character-id=N", 'ERROR');
        terminate_script($is_cli, 1);
    }
    
    // Print statistics
    echo "\n";
    echo "=================================================================\n";
    echo "Statistics\n";
    echo "=================================================================\n";
    $stats = $syncer->getStats();
    foreach ($stats as $key => $value) {
        echo ucfirst(str_replace('_', ' ', $key)) . ": $value\n";
    }
    
} catch (Exception $e) {
    $logger->log("Fatal error: " . $e->getMessage(), 'ERROR');
    terminate_script($is_cli, 1);
}

mysqli_close($conn);

if (!$is_cli) {
    echo "\n" . str_repeat('=', 65) . "\n";
}
?>

