<?php require 'includes/bootstrap.php'; $title = 'Sign in';
if (current_user()) redirect('dashboard.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $lock = $_SESSION['lock'] ?? ['n' => 0, 'until' => 0];
    if ($lock['until'] > time()) { $err = 'Too many attempts. Wait a few minutes and try again.'; }
    else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $row = q('SELECT id,password_hash FROM users WHERE email=?', [$email])->fetch();
        if ($row && password_verify($_POST['password'] ?? '', $row['password_hash'])) {
            session_regenerate_id(true); $_SESSION['uid'] = (int)$row['id']; unset($_SESSION['lock']);
            if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT))
                q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($_POST['password'], PASSWORD_DEFAULT), $row['id']]);
            redirect('dashboard.php');
        }
        $lock['n']++; if ($lock['n'] >= 5) { $lock = ['n' => 0, 'until' => time() + 300]; }
        $_SESSION['lock'] = $lock; $err = 'Email or password is incorrect.';
    }
}
include 'includes/header.php'; ?>
<div class="card narrow"><h1>Sign in</h1>
<?php if ($err): ?><div class="alert a-err"><?= e($err) ?></div><?php endif; ?>
<form method="post" data-validate><?= csrf_field() ?>
  <label>Email<input type="email" name="email" required value="<?= old('email') ?>" autocomplete="username"></label>
  <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
  <button class="btn">Sign in</button>
</form><p class="muted">New here? <a href="register.php">Create an account</a></p></div>
<?php include 'includes/footer.php'; ?>
