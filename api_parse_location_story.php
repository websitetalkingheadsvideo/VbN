<?php
/**
 * API - Parse Location Story
 * POST /api_parse_location_story.php
 * Uses AI to extract structured location data from narrative text
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['narrative'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing narrative text']);
    exit;
}

$narrative = $data['narrative'];

// Load environment variables from config.env
function loadEnvFile($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set as environment variable
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
    return true;
}

// Load config.env file
$env_loaded = loadEnvFile(__DIR__ . '/config.env');
if (!$env_loaded) {
    // Try .env as fallback
    $env_loaded = loadEnvFile(__DIR__ . '/.env');
}

// Load AI configuration
$config_path = __DIR__ . '/.taskmaster/config.json';
if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'AI configuration not found']);
    exit;
}

$config = json_decode(file_get_contents($config_path), true);
$model_config = $config['models']['main'];

// Get API key from environment
$api_key = null;
$api_endpoint = null;

switch ($model_config['provider']) {
    case 'anthropic':
        $api_key = getenv('ANTHROPIC_API_KEY');
        $api_endpoint = 'https://api.anthropic.com/v1/messages';
        break;
    case 'openai':
        $api_key = getenv('OPENAI_API_KEY');
        $api_endpoint = 'https://api.openai.com/v1/chat/completions';
        break;
    default:
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unsupported AI provider: ' . $model_config['provider']]);
        exit;
}

if (!$api_key || $api_key === 'your_anthropic_api_key_here' || $api_key === 'your_openai_api_key_here') {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'AI API key not configured. Please add your API key to config.env file.'
    ]);
    exit;
}

// Build the extraction prompt
$prompt = <<<PROMPT
You are a data extraction specialist for a Vampire: The Masquerade LARP game database. Your task is to parse a narrative description of a location and extract structured data that matches our database schema.

Extract as much information as possible from the narrative and format it as JSON. For each field, include:
1. The extracted value
2. A confidence score (0.0 to 1.0) indicating how certain you are about the extraction
3. A brief reason for the confidence level

**Location Narrative:**
{$narrative}

**Required JSON Structure:**
Return ONLY valid JSON (no markdown, no code blocks) with this exact structure:

{
  "name": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "type": {"value": "Haven|Elysium|Domain|Hunting Ground|Gathering Place|Business|Chantry|Temple|Wilderness|Other", "confidence": 0.0-1.0, "reason": "string"},
  "summary": {"value": "string (max 500 chars)", "confidence": 0.0-1.0, "reason": "string"},
  "description": {"value": "string (preserve narrative)", "confidence": 1.0, "reason": "direct text"},
  "notes": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "status": {"value": "Active|Abandoned|Destroyed|Under Construction|Contested|Hidden", "confidence": 0.0-1.0, "reason": "string"},
  "status_notes": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "district": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "address": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "latitude": {"value": 0.0, "confidence": 0.0-1.0, "reason": "string"},
  "longitude": {"value": 0.0, "confidence": 0.0-1.0, "reason": "string"},
  "owner_type": {"value": "Individual|Coterie|Clan|Faction|Contested|Public", "confidence": 0.0-1.0, "reason": "string"},
  "owner_notes": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "faction": {"value": "Camarilla|Anarch|Independent|Sabbat|Mortal|None", "confidence": 0.0-1.0, "reason": "string"},
  "access_control": {"value": "Public|Open|Restricted|Private|Threshold|Elysium", "confidence": 0.0-1.0, "reason": "string"},
  "access_notes": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "security_level": {"value": 1-5, "confidence": 0.0-1.0, "reason": "string"},
  "security_locks": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_alarms": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_guards": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_hidden_entrance": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_sunlight_protected": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_warding_rituals": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_cameras": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_reinforced": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "security_notes": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "utility_blood_storage": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_computers": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_library": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_medical": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_workshop": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_hidden_caches": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_armory": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_communications": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "utility_notes": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "social_features": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "capacity": {"value": 0, "confidence": 0.0-1.0, "reason": "string"},
  "prestige_level": {"value": 0-5, "confidence": 0.0-1.0, "reason": "string"},
  "has_supernatural": {"value": 0|1, "confidence": 0.0-1.0, "reason": "string"},
  "node_points": {"value": 0-10, "confidence": 0.0-1.0, "reason": "string"},
  "node_type": {"value": "None|Standard|Corrupted|Pure|Ancient", "confidence": 0.0-1.0, "reason": "string"},
  "ritual_space": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "magical_protection": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "cursed_blessed": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "parent_location_id": {"value": null, "confidence": 0.0, "reason": "requires database lookup"},
  "relationship_type": {"value": "", "confidence": 0.0-1.0, "reason": "string"},
  "relationship_notes": {"value": "string", "confidence": 0.0-1.0, "reason": "string"},
  "image": {"value": "", "confidence": 0.0, "reason": "not in narrative"}
}

**Extraction Guidelines:**
1. Use direct quotes when available (confidence: 0.9-1.0)
2. Use strong implications (confidence: 0.7-0.9)
3. Use reasonable inferences (confidence: 0.4-0.7)
4. Use educated guesses (confidence: 0.1-0.4)
5. If completely absent, use empty/null/0 (confidence: 0.0)

For boolean fields (security_*, utility_*), mark as 1 if mentioned or strongly implied, 0 otherwise.

For numeric fields:
- security_level: Infer from narrative (1=minimal, 5=maximum)
- capacity: Extract if mentioned, otherwise reasonable estimate based on type
- prestige_level: Infer social importance (0=none, 5=legendary)
- node_points: Only if supernatural power is mentioned (0-5=minor, 6-10=major)

Return ONLY the JSON object, no markdown formatting, no code blocks, no additional text.
PROMPT;

// Make API request based on provider
try {
    if ($model_config['provider'] === 'anthropic') {
        $request_body = [
            'model' => $model_config['modelId'],
            'max_tokens' => 4096,
            'temperature' => $model_config['temperature'],
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $ch = curl_init($api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $api_key,
            'anthropic-version: 2023-06-01'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception('AI API request failed: HTTP ' . $http_code);
        }

        $response_data = json_decode($response, true);
        if (!isset($response_data['content'][0]['text'])) {
            throw new Exception('Unexpected API response format');
        }

        $extracted_json = $response_data['content'][0]['text'];

    } else if ($model_config['provider'] === 'openai') {
        $request_body = [
            'model' => $model_config['modelId'],
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $model_config['temperature']
        ];

        $ch = curl_init($api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception('AI API request failed: HTTP ' . $http_code);
        }

        $response_data = json_decode($response, true);
        if (!isset($response_data['choices'][0]['message']['content'])) {
            throw new Exception('Unexpected API response format');
        }

        $extracted_json = $response_data['choices'][0]['message']['content'];
    }

    // Clean up the response (remove markdown code blocks if present)
    $extracted_json = preg_replace('/^```json\s*/m', '', $extracted_json);
    $extracted_json = preg_replace('/\s*```$/m', '', $extracted_json);
    $extracted_json = trim($extracted_json);

    // Parse the JSON
    $extracted_data = json_decode($extracted_json, true);
    
    if (!$extracted_data) {
        throw new Exception('Failed to parse AI response as JSON: ' . json_last_error_msg());
    }

    // Return success with extracted data
    echo json_encode([
        'success' => true,
        'data' => $extracted_data,
        'raw_response' => $extracted_json
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'AI parsing failed: ' . $e->getMessage()
    ]);
}
?>

