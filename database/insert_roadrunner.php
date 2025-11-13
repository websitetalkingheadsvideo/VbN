<?php
declare(strict_types=1);

use RuntimeException;

header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/../includes/connect.php';

    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new RuntimeException('Database connection unavailable from includes/connect.php.');
    }

    $characterName = 'Marisol "Roadrunner" Vega';
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

    $userId = 1; // Admin user; adjust if a different owner should be assigned
    $playerName = 'NPC';
    $nature = 'Survivor';
    $demeanor = 'Loner';
    $concept = 'Desert tracker and coterie scout';
    $clan = 'Gangrel';
    $generation = 11;
    $sire = 'Unknown independent Gangrel';
    $pc = 0;
    $status = 'active';
    $camarillaStatus = 'Camarilla';
    $characterImage = null;
    $totalXp = 0;
    $spentXp = 0;

    $biography = <<<BIO
Former long-haul driver who knows every back road between Las Vegas and Phoenix. Embraced during a midnight stop by an independent Gangrel, Marisol “Roadrunner” Vega now guides Kindred through the Valley’s harsh outskirts, mapping supernatural safe trails and keeping her coterie a step ahead of threats.
BIO;

    $appearance = <<<APPEARANCE
Lean, sunburnt Latina in a faded trucker jacket, dust-caked boots, and hawk-bright eyes. Her presence is quiet and coiled, the stillness of a hunter who can explode into motion without warning.
APPEARANCE;

    $notes = <<<NOTES
Feeds on truckers and night-shift workers she deems respectful of the road. Maintains caches of gear along desert routes and leaves sigils to warn coterie mates about Sabbat patrols.
NOTES;

    $agentNotes = <<<AGENT
Core scout for the Valley Watch coterie; partners with Brujah ally Rafael “Switchback” Ortiz. Keeps tabs on Sabbat raiders near Guadalupe and trades information with ADOT night crews and park rangers.
AGENT;

    $actingNotes = <<<ACTING
Play with quiet confidence, dry humor, and unflinching pragmatism. Frequently tastes soil or clicks her tongue softly while assessing terrain. Treat vehicles, maps, and sign as lived-in extensions of her awareness.
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
    echo 'Roadrunner insert failed: ' . $error->getMessage() . PHP_EOL;
}


