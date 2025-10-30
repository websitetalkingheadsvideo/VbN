<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/connect.php';

mysqli_begin_transaction($conn);

try {
    // Ensure table exists
    $create = "
    CREATE TABLE IF NOT EXISTS clans (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        logo_filename VARCHAR(255) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_clan_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    if (!mysqli_query($conn, $create)) {
        throw new Exception('Create error: ' . mysqli_error($conn));
    }

    // Clear and re-seed
    if (!mysqli_query($conn, 'DELETE FROM clans')) {
        throw new Exception('Delete error: ' . mysqli_error($conn));
    }

    $seed = [
        ['Assamite', 'LogoClanAssamite.webp'],
        ['Brujah', 'LogoClanBrujah.webp'],
        ['Followers of Set', 'LogoClanFollowersofSet.webp'],
        ['Gangrel', 'LogoClanGangrel.webp'],
        ['Giovanni', 'LogoClanGiovanni.webp'],
        ['Lasombra', 'LogoClanLasombra.webp'],
        ['Malkavian', 'LogoClanMalkavian.webp'],
        ['Nosferatu', 'LogoClanNosferatu.webp'],
        ['Ravnos', 'LogoClanRavnos.webp'],
        ['Toreador', 'LogoClanToreador.webp'],
        ['Tremere', 'LogoClanTremere.webp'],
        ['Tzimisce', 'LogoClanTzimisce.webp'],
        ['Ventrue', 'LogoClanVentrue.webp'],
        ['Caitiff', 'LogoBloodlineCaitiff.webp'],
    ];

    $stmt = mysqli_prepare($conn, 'INSERT INTO clans (name, logo_filename) VALUES (?, ?)');
    if (!$stmt) {
        throw new Exception('Prepare error: ' . mysqli_error($conn));
    }

    $count = 0;
    foreach ($seed as [$name, $file]) {
        mysqli_stmt_bind_param($stmt, 'ss', $name, $file);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Insert error for ' . $name . ': ' . mysqli_stmt_error($stmt));
        }
        $count++;
    }
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);
    echo 'Reseed complete. Inserted rows: ' . $count;
} catch (Throwable $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo 'Reseed failed: ' . htmlspecialchars($e->getMessage());
}

mysqli_close($conn);


