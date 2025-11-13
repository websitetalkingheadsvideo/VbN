<?php
declare(strict_types=1);

use RuntimeException;

header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/../includes/connect.php';

    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new RuntimeException('Database connection unavailable from includes/connect.php.');
    }

    $characterName = 'Warner Jefferson';
    $chronicle = 'Valley by Night';

    $lookup = mysqli_prepare(
        $conn,
        'SELECT id FROM characters WHERE character_name = ? AND chronicle = ? LIMIT 1'
    );

    if ($lookup === false) {
        throw new RuntimeException('Failed to prepare lookup statement: ' . mysqli_error($conn));
    }

    if (!mysqli_stmt_bind_param($lookup, 'ss', $characterName, $chronicle)) {
        $error = mysqli_stmt_error($lookup);
        mysqli_stmt_close($lookup);
        throw new RuntimeException('Failed to bind lookup parameters: ' . $error);
    }

    if (!mysqli_stmt_execute($lookup)) {
        $error = mysqli_stmt_error($lookup);
        mysqli_stmt_close($lookup);
        throw new RuntimeException('Failed to execute lookup statement: ' . $error);
    }

    mysqli_stmt_store_result($lookup);

    if (mysqli_stmt_num_rows($lookup) > 0) {
        mysqli_stmt_close($lookup);
        echo 'Character already exists: ' . $characterName . PHP_EOL;
        mysqli_close($conn);
        return;
    }

    mysqli_stmt_close($lookup);

    $biography = <<<BIO
South Side Chicago prodigy turned Ivy League financier whose mastery of distressed assets drew the attention of Victoria Ashford-Sterling. Embraced in 1988 during the leveraged buyout boom, Warner now applies vulture finance tactics to Kindred politics, mapping Tremere and Giovanni holdings across Phoenix while quietly assembling his own power base.
BIO;

    $agentNotes = <<<NOTES
Assigned by the Ventrue Primogen to catalogue Tremere and Giovanni business fronts. Operates through shell companies managed by his ghoul Markus and reports progress to Victoria Ashford-Sterling. Maintains immaculate Camarilla etiquette while positioning for larger acquisitions.
NOTES;

    $actingNotes = <<<ACTING
Commanding financial executive; polished, patient, and transactional in every conversation. Voice is confident and measured with an Ivy League cadence—treat every interaction like closing a deal.
ACTING;

    $appearance = <<<APPEARANCE
Impeccably dressed African American man in his early thirties with bespoke suits, understated luxury accessories, and a calculating gaze that sizes up every room. Moves with deliberate authority and never appears anything less than perfectly composed.
APPEARANCE;

    $notes = <<<NOTESFIELD
Feeds exclusively from mortals who hold business degrees, maintaining a curated Herd of MBA graduates through an exclusive executive club.
NOTESFIELD;

    $insert = mysqli_prepare(
        $conn,
    'INSERT INTO characters (
        user_id,
        character_name,
        player_name,
        chronicle,
        nature,
        demeanor,
        concept,
        clan,
        generation,
        sire,
        pc,
        status,
        camarilla_status,
        biography,
        agentNotes,
        actingNotes,
        appearance,
        notes,
        character_image,
        total_xp,
        spent_xp,
        created_at,
        updated_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
    )'
    );

    if ($insert === false) {
        throw new RuntimeException('Failed to prepare insert statement: ' . mysqli_error($conn));
    }

    $userId = 1; // Admin account (adjust if a different owner is required)
    $playerName = 'NPC';
    $nature = 'Capitalist';
    $demeanor = 'Director';
    $concept = 'Vulture finance specialist';
    $clan = 'Ventrue';
    $generation = 11;
    $sire = 'Victoria Ashford-Sterling';
    $pc = 0;
    $status = 'active';
    $camarillaStatus = 'Camarilla';
    $characterImage = 'Warner Jefferson.webp';
    $totalXp = 0;
    $spentXp = 0;

    $bindParams = [
        $insert,
        'isssssssisissssssssii',
        &$userId,
        &$characterName,
        &$playerName,
        &$chronicle,
        &$nature,
        &$demeanor,
        &$concept,
        &$clan,
        &$generation,
        &$sire,
        &$pc,
        &$status,
        &$camarillaStatus,
        &$biography,
        &$agentNotes,
        &$actingNotes,
        &$appearance,
        &$notes,
        &$characterImage,
        &$totalXp,
        &$spentXp
    ];

    if (!call_user_func_array('mysqli_stmt_bind_param', $bindParams)) {
        $error = mysqli_stmt_error($insert);
        mysqli_stmt_close($insert);
        throw new RuntimeException('Failed to bind insert parameters: ' . $error);
    }

    if (!mysqli_stmt_execute($insert)) {
        $error = mysqli_stmt_error($insert);
        mysqli_stmt_close($insert);
        throw new RuntimeException('Failed to execute insert statement: ' . $error);
    }

    mysqli_stmt_close($insert);
    mysqli_close($conn);

    echo 'Character inserted: ' . $characterName . PHP_EOL;
} catch (Throwable $error) {
    http_response_code(500);
    echo 'Warner insert failed: ' . $error->getMessage() . PHP_EOL;
}


