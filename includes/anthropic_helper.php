<?php
/**
 * Anthropic AI Helper Module
 * Reusable helper for making calls to Anthropic's Claude API
 */

/**
 * Load API key from config files
 */
function load_anthropic_api_key(): ?string {
    // Try environment variable first
    $api_key = getenv('ANTHROPIC_API_KEY');
    if ($api_key && $api_key !== 'your_anthropic_api_key_here') {
        return $api_key;
    }
    
    // Try config.env file
    $config_env = __DIR__ . '/../config.env';
    if (file_exists($config_env)) {
        $lines = file($config_env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, 'ANTHROPIC_API_KEY=') === 0) {
                $key = trim(substr($line, strlen('ANTHROPIC_API_KEY=')));
                if ($key && $key !== 'your_anthropic_api_key_here') {
                    return $key;
                }
            }
        }
    }
    
    // Try .taskmaster/config.json
    $taskmaster_config = __DIR__ . '/../.taskmaster/config.json';
    if (file_exists($taskmaster_config)) {
        $config = json_decode(file_get_contents($taskmaster_config), true);
        if (isset($config['models']['main']['provider']) && $config['models']['main']['provider'] === 'anthropic') {
            // Check environment for the key
            $env_key = getenv('ANTHROPIC_API_KEY');
            if ($env_key && $env_key !== 'your_anthropic_api_key_here') {
                return $env_key;
            }
        }
    }
    
    return null;
}

/**
 * Call Anthropic API with a prompt
 * 
 * @param string $prompt The user prompt/question
 * @param string $system_prompt The system instructions
 * @param int $max_tokens Maximum tokens to generate
 * @param string $model The Claude model to use
 * @return array Response with 'success', 'content', and 'error' keys
 */
function call_anthropic(
    string $prompt, 
    string $system_prompt = '',
    int $max_tokens = 2000,
    string $model = 'claude-3-5-sonnet-20241022'
): array {
    // Load API key
    $api_key = load_anthropic_api_key();
    
    if (!$api_key) {
        return [
            'success' => false,
            'error' => 'Anthropic API key not configured',
            'content' => null
        ];
    }
    
    // Build request payload
    $data = [
        'model' => $model,
        'max_tokens' => $max_tokens,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ]
    ];
    
    // Add system prompt if provided
    if (!empty($system_prompt)) {
        $data['system'] = $system_prompt;
    }
    
    // Make API call
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $api_key,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Handle curl errors
    if ($response === false) {
        return [
            'success' => false,
            'error' => 'API request failed: ' . $curl_error,
            'content' => null
        ];
    }
    
    // Parse response
    $result = json_decode($response, true);
    
    // Handle HTTP errors
    if ($http_code !== 200) {
        $error_msg = 'API error (HTTP ' . $http_code . ')';
        if (isset($result['error']['message'])) {
            $error_msg .= ': ' . $result['error']['message'];
        }
        
        return [
            'success' => false,
            'error' => $error_msg,
            'content' => null,
            'http_code' => $http_code
        ];
    }
    
    // Extract content from response
    if (isset($result['content'][0]['text'])) {
        return [
            'success' => true,
            'content' => $result['content'][0]['text'],
            'error' => null,
            'model' => $result['model'] ?? $model,
            'usage' => $result['usage'] ?? null
        ];
    }
    
    // Unexpected response format
    return [
        'success' => false,
        'error' => 'Unexpected API response format',
        'content' => null,
        'raw_response' => $result
    ];
}

/**
 * Format a conversation with multiple messages
 * Useful for multi-turn conversations
 * 
 * @param array $messages Array of ['role' => 'user'|'assistant', 'content' => '...']
 * @param string $system_prompt The system instructions
 * @param int $max_tokens Maximum tokens to generate
 * @param string $model The Claude model to use
 * @return array Response with 'success', 'content', and 'error' keys
 */
function call_anthropic_conversation(
    array $messages,
    string $system_prompt = '',
    int $max_tokens = 2000,
    string $model = 'claude-3-5-sonnet-20241022'
): array {
    $api_key = load_anthropic_api_key();
    
    if (!$api_key) {
        return [
            'success' => false,
            'error' => 'Anthropic API key not configured',
            'content' => null
        ];
    }
    
    $data = [
        'model' => $model,
        'max_tokens' => $max_tokens,
        'messages' => $messages
    ];
    
    if (!empty($system_prompt)) {
        $data['system'] = $system_prompt;
    }
    
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $api_key,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false || $http_code !== 200) {
        $result = json_decode($response, true);
        return [
            'success' => false,
            'error' => $result['error']['message'] ?? 'API request failed',
            'content' => null
        ];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['content'][0]['text'])) {
        return [
            'success' => true,
            'content' => $result['content'][0]['text'],
            'error' => null,
            'model' => $result['model'] ?? $model,
            'usage' => $result['usage'] ?? null
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Unexpected API response format',
        'content' => null
    ];
}

