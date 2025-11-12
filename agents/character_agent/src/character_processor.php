<?php
declare(strict_types=1);

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

const CHARACTER_AGENT_REQUIRED_TEXT_FIELDS = [
    'biography' => 'Add 2-3 paragraph chronicle summary.',
    'appearance' => 'Describe look, wardrobe, and notable tells.',
    'notes' => 'Outline current plots, debts, or secrets.',
];

const CHARACTER_AGENT_PLACEHOLDER_VALUES = [
    'tbd',
    'todo',
    'n/a',
    'na',
    'none',
    'unknown',
    'placeholder',
];

/**
 * @param array<string, mixed> $character
 * @return bool
 */
function character_agent_should_render_brief(array $character): bool
{
    if (character_agent_is_placeholder($character['player'] ?? null)) {
        return false;
    }

    foreach (array_keys(CHARACTER_AGENT_REQUIRED_TEXT_FIELDS) as $field) {
        $value = $character[$field] ?? null;

        if (character_agent_is_missing_text($value)) {
            return false;
        }
    }

    if (character_agent_build_trait_points($character) === []) {
        return false;
    }

    return true;
}

/**
 * @param array<string, mixed> $character
 * @return array<int, array<string, mixed>>
 */
function character_agent_collect_missing_fields(array $character): array
{
    $entries = [];

    foreach (CHARACTER_AGENT_REQUIRED_TEXT_FIELDS as $field => $recommendation) {
        $value = $character[$field] ?? null;

        if (character_agent_is_missing_text($value)) {
            $entries[] = [
                'field' => $field,
                'status' => 'missing',
                'recommendation' => $recommendation,
                'current_value' => (string) ($value ?? ''),
            ];
            continue;
        }

        if (character_agent_is_placeholder($value)) {
            $entries[] = [
                'field' => $field,
                'status' => 'placeholder',
                'recommendation' => $recommendation,
                'current_value' => (string) $value,
            ];
        }
    }

    return $entries;
}

/**
 * @param array<string, mixed> $character
 * @return array<string, mixed>
 */
function character_agent_build_missing_field_payload(array $character): array
{
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ATOM);

    return [
        'character_id' => $character['id'],
        'character_name' => $character['name'],
        'detected_at' => $timestamp,
        'fields' => character_agent_collect_missing_fields($character),
    ];
}

/**
 * @param array<string, mixed> $character
 * @return array<string, mixed>
 */
function character_agent_build_brief_context(array $character): array
{
    if (!character_agent_should_render_brief($character)) {
        throw new RuntimeException('Character does not meet requirements for brief generation.');
    }

    $summary = character_agent_extract_summary($character);

    return [
        'name' => $character['name'],
        'clan' => $character['clan'] ?? '',
        'faction' => $character['status'] ?? '',
        'location' => $character['coterie'] ?? '',
        'player' => $character['player'] ?? '',
        'summary' => $summary,
        'traits' => character_agent_build_trait_points($character),
        'hooks' => character_agent_build_plot_hooks($character),
    ];
}

/**
 * @param array<string, mixed> $character
 * @return array<string, mixed>
 */
function character_agent_build_history_segment(array $character): array
{
    return [
        'name' => $character['name'],
        'coterie' => $character['coterie'] ?? '',
        'biography' => $character['biography'] ?? '',
        'notes' => $character['notes'] ?? '',
        'agent_notes' => $character['agent_notes'] ?? '',
    ];
}

/**
 * @param array<string, mixed> $character
 * @return string
 */
function character_agent_slug_for_character(array $character): string
{
    $name = $character['name'] ?? '';

    if ($name === '') {
        return 'character-' . $character['id'];
    }

    $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? '');
    $trimmed = trim($normalized, '-');

    if ($trimmed === '') {
        return 'character-' . $character['id'];
    }

    return $trimmed;
}

/**
 * @param mixed $value
 * @return bool
 */
function character_agent_is_missing_text($value): bool
{
    if ($value === null) {
        return true;
    }

    if (!is_string($value)) {
        return true;
    }

    return trim($value) === '';
}

/**
 * @param mixed $value
 * @return bool
 */
function character_agent_is_placeholder($value): bool
{
    if (!is_string($value)) {
        return false;
    }

    $normalized = strtolower(trim($value));

    if ($normalized === 'npc') {
        return true;
    }

    return in_array($normalized, CHARACTER_AGENT_PLACEHOLDER_VALUES, true);
}

/**
 * @param array<string, mixed> $character
 * @return string
 */
function character_agent_extract_summary(array $character): string
{
    $biography = $character['biography'] ?? null;

    if ($biography === null || trim($biography) === '') {
        throw new RuntimeException('Summary requires filled biography.');
    }

    $paragraphs = preg_split('/\R{2,}/', trim($biography));

    if ($paragraphs === false || $paragraphs === []) {
        return trim($biography);
    }

    $selected = array_slice($paragraphs, 0, 2);

    return implode(PHP_EOL . PHP_EOL, array_map('trim', $selected));
}

/**
 * @param array<string, mixed> $character
 * @return array<int, string>
 */
function character_agent_build_trait_points(array $character): array
{
    $points = [];

    if (isset($character['concept']) && $character['concept'] !== null && $character['concept'] !== '') {
        $points[] = sprintf('Concept: %s', $character['concept']);
    }

    if (isset($character['generation']) && $character['generation'] !== null) {
        $points[] = sprintf('Generation: %s', $character['generation']);
    }

    if (isset($character['coterie']) && $character['coterie'] !== null && $character['coterie'] !== '') {
        $points[] = sprintf('Coterie: %s', $character['coterie']);
    }

    if (isset($character['status']) && $character['status'] !== null && $character['status'] !== '') {
        $points[] = sprintf('Status: %s', $character['status']);
    }

    return array_slice($points, 0, 3);
}

/**
 * @param array<string, mixed> $character
 * @return array<int, string>
 */
function character_agent_build_plot_hooks(array $character): array
{
    $hooks = [];
    $name = $character['name'] ?? 'This character';

    if (isset($character['coterie']) && $character['coterie'] !== null && $character['coterie'] !== '') {
        $hooks[] = sprintf('%s faces pressure to keep %s aligned with Camarilla expectations.', $name, $character['coterie']);
    }

    if (isset($character['sire']) && $character['sire'] !== null && $character['sire'] !== '') {
        $hooks[] = sprintf('Investigate how %s\'s bond with sire %s could destabilize local alliances.', $name, $character['sire']);
    }

    if (isset($character['notes']) && $character['notes'] !== null && $character['notes'] !== '') {
        $hooks[] = sprintf('Escalate the ongoing thread noted in chronicles: %s', mb_substr(trim($character['notes']), 0, 140));
    }

    return array_slice($hooks, 0, 3);
}

/**
 * @param array<string, mixed> $character
 * @param bool $briefGenerated
 * @param array<int, array<string, mixed>> $missingFields
 * @return array<string, mixed>
 */
function character_agent_build_log_payload(array $character, bool $briefGenerated, array $missingFields): array
{
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ATOM);
    $events = [];

    if ($briefGenerated) {
        $events[] = 'brief_written';
    }

    foreach ($missingFields as $entry) {
        $events[] = 'missing_field:' . $entry['field'] . ':' . $entry['status'];
    }

    return [
        'timestamp' => $timestamp,
        'character_id' => $character['id'],
        'events' => $events,
    ];
}

