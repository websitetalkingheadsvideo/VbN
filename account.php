<?php
/**
 * Account Settings — Valley by Night
 * Update email and change password (Bootstrap 5 + a11y)
 */
session_start();
require_once __DIR__ . '/includes/auth_bypass.php';

if (!isset($_SESSION['user_id']) && !isAuthBypassEnabled()) {
    header('Location: login.php');
    exit();
}
if (isAuthBypassEnabled() && !isset($_SESSION['user_id'])) {
    setupBypassSession();
}

require_once __DIR__ . '/includes/connect.php';

$user_id = $_SESSION['user_id'];
$user = db_fetch_one($conn, 'SELECT id, username, email FROM users WHERE id = ?', 'i', [$user_id]);
if (!$user) {
    $_SESSION['error'] = 'Unable to load account.';
    header('Location: index.php');
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$extra_css = [];
include __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
  <h1 class="section-heading">Account Settings</h1>

  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger" role="alert" aria-live="polite"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success" role="alert" aria-live="polite"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="gothic-panel">
        <h2 class="h5 text-light mb-3">Update Email</h2>
        <form action="update_account.php" method="POST" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <input type="hidden" name="action" value="email">

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled readonly>
          </div>

          <div class="mb-3">
            <label for="current_email" class="form-label">Current Email</label>
            <input type="email" id="current_email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled readonly>
          </div>

          <div class="mb-3">
            <label for="new_email" class="form-label">New Email</label>
            <input type="email" id="new_email" name="new_email" class="form-control" required autocomplete="email">
            <div class="invalid-feedback">Please enter a valid email address.</div>
          </div>

          <button type="submit" class="btn btn-primary">Update Email</button>
        </form>
      </div>
    </div>

    <div class="col-md-6">
      <div class="gothic-panel">
        <h2 class="h5 text-light mb-3">Change Password</h2>
        <form action="update_account.php" method="POST" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <input type="hidden" name="action" value="password">

          <div class="mb-3">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
            <div class="invalid-feedback">Current password is required.</div>
          </div>

          <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
            <div class="invalid-feedback">New password must be at least 8 characters.</div>
          </div>

          <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
            <div class="invalid-feedback">Please confirm your new password.</div>
          </div>

          <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

