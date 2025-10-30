<?php
declare(strict_types=1);

// Creates `clans` table and seeds LOTN Revised clan names with logo filenames

require_once __DIR__ . '/../includes/connect.php';

// Create table if not exists
$sql = "
CREATE TABLE IF NOT EXISTS clans (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    logo_filename VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_clan_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if (!mysqli_query($conn, $sql)) {
    http_response_code(500);
    echo 'Error creating clans table: ' . mysqli_error($conn);
    exit;
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

$stmt = mysqli_prepare($conn, 'INSERT INTO clans (name, logo_filename) VALUES (?, ?) ON DUPLICATE KEY UPDATE logo_filename = VALUES(logo_filename)');
if (!$stmt) {
    http_response_code(500);
    echo 'Error preparing seed statement: ' . mysqli_error($conn);
    exit;
}

foreach ($seed as [$name, $file]) {
    mysqli_stmt_bind_param($stmt, 'ss', $name, $file);
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo 'Error seeding clan ' . htmlspecialchars($name) . ': ' . mysqli_stmt_error($stmt);
        exit;
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo 'Clans table ready and seeded.';
