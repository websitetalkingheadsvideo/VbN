<?php
declare(strict_types=1);

return [
    'chronicle' => 'Valley by Night',
    'batch' => [
        'size' => 25,
        'sleep_ms' => 50,
    ],
    'paths' => [
        'reports' => __DIR__ . '/../reports',
        'logs' => __DIR__ . '/../logs/agent_activity.log',
        'prompts' => __DIR__ . '/../prompts',
        'history' => __DIR__ . '/../reports/history',
    ],
    'features' => [
        'generate_briefs' => true,
        'missing_field_reports' => true,
        'continuity_checks' => true,
        'history_compilation' => true,
    ],
    'filters' => [
        'updated_since_minutes' => null,
        'limit_to_ids' => [],
    ],
];

