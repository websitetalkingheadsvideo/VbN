<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/urls.php';

$result = mysqli_query($conn, "SELECT id, name, logo_filename FROM clans ORDER BY name ASC");
if ($result === false) {
    http_response_code(500);
    echo 'DB error: ' . htmlspecialchars(mysqli_error($conn));
    exit;
}

// Precompute rows
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
mysqli_free_result($result);
mysqli_close($conn);

function logo_url(string $filename): string {
    $base = rtrim(VBN_BASE_URL, '/') . '/images/Clan%20Logos/';
    return $base . rawurlencode($filename);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clans Table</title>
    <style>
        body { font-family: Arial, sans-serif; background:#111; color:#eee; padding:20px; }
        table { width:100%; border-collapse: collapse; background:#1b1b1b; }
        th, td { padding:10px 12px; border-bottom:1px solid #333; }
        th { text-align:left; background:#222; }
        /* High-contrast logo well (checkerboard) for transparent logos */
        .logo {
            height:48px; width:auto; border:none !important; outline: none; box-shadow: none; border-radius:0; padding:0;
            background:
                linear-gradient(45deg, #ddd 25%, transparent 25%) -8px 0/16px 16px,
                linear-gradient(-45deg, #ddd 25%, transparent 25%) -8px 0/16px 16px,
                linear-gradient(45deg, transparent 75%, #ddd 75%) -8px 0/16px 16px,
                linear-gradient(-45deg, transparent 75%, #ddd 75%) -8px 0/16px 16px,
                #fff;
        }
        .muted { color:#aaa; font-family: monospace; }
        .header { display:flex; justify-content: space-between; align-items:center; margin-bottom:16px; }
        a { color:#4aa3ff; text-decoration:none; }
        a:hover { text-decoration: underline; }
    </style>
    <link rel="icon" href="/favicon.ico">
</head>
<body>
    <div class="header">
        <h1>Clans Table</h1>
        <div>
            <a href="/database/create_clans_table.php">Re-seed</a>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Logo</th>
                <th>Filename</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): $src = logo_url($r['logo_filename']); ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['name']); ?></td>
                <td><img class="logo" src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($r['name']); ?> logo" onerror="this.replaceWith(document.createTextNode('not found'))"/></td>
                <td class="muted"><?php echo htmlspecialchars($r['logo_filename']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>


