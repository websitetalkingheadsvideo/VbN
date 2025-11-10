<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.5.0');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

include __DIR__ . '/../includes/header.php';
?>
    <div class="container py-4">
        <h1 class="mb-3">Admin Panel Works</h1>
        <p>Connection: <?php echo ($conn ? 'Connected' : 'Failed'); ?></p>
        <?php
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM characters");
        if ($result) {
            $count = mysqli_fetch_assoc($result);
            echo '<p>Characters in DB: ' . (int)$count['total'] . '</p>';
        } else {
            echo '<p class="text-danger">Query error: ' . htmlspecialchars(mysqli_error($conn)) . '</p>';
        }
        ?>
    </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

