<?php
declare(strict_types=1);

use RuntimeException;

header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/../includes/connect.php';

    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new RuntimeException('Database connection unavailable from includes/connect.php.');
    }

    $characterName = 'Roxanne Murphy';
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

    $userId = 1; // admin owner
    $playerName = 'NPC';
    $nature = 'Visionary';
    $demeanor = 'Priestess';
    $concept = 'Setite temple-mistress running a nightclub front in Mesa';
    $clan = 'Followers of Set';
    $generation = 9;
    $sire = '';
    $pc = 0;
    $status = 'active';
    $camarillaStatus = 'Independent (Followers of Set)';
    $characterImage = 'Roxanne Murphy.png';
    $totalXp = 30;
    $spentXp = 0;

    $biography = <<<BIO
After years operating in smaller occult circles, Roxanne established herself in Mesa by building The Hole as both a profitable nightlife destination and a perfectly concealed Setite temple. She rules its levels like a descending mystery cult: public pleasure above, true corruption and worship below. She works closely with Marcus (operations) and Jennifer (security) and maintains the blasphemy-shrine where she performs Akhu rites. She is aware of Camarilla eyes on her domain but is confident in her layered defenses.
BIO;

    $appearance = <<<APPEARANCE
Impeccably composed Setite woman who favors Egyptian-inspired jewelry and rich fabrics. Often seen in the private levels of The Hole, lit by warm, low light that accents her ritual regalia.
APPEARANCE;

    $notes = <<<NOTES
High priestess of The Hole; maintains layered security and monthly Akhu rites in a hidden shrine dedicated to Set.
NOTES;

    $agentNotes = <<<AGENT
Architect and high priestess behind The Hole in Mesa. Oversees the tiered Setite corruption engine, balances hospitality with ruthless enforcement, and coordinates with Marcus and Jennifer to keep Setite operations hidden from Camarilla scrutiny.
AGENT;

    $actingNotes = <<<ACTING
Play her as an unshakeable occult matriarch: poised voice, measured cadence, and ritual precision. Hospitality is a calculated weapon; warmth masks threat until Set’s interests are challenged.
ACTING;

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
    echo 'Roxanne insert failed: ' . $error->getMessage() . PHP_EOL;
}


