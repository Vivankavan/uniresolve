<?php require 'includes/bootstrap.php'; $title = 'Create account';
if (current_user()) redirect('dashboard.php');
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? ''));
    $pw = $_POST['password'] ?? ''; $pw2 = $_POST['password2'] ?? '';
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) $err['name'] = 'Enter your full name (2 to 80 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160) $err['email'] = 'Enter a valid email address.';
    if (strlen($pw) < 8 || !preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) $err['password'] = 'Use at least 8 characters with a letter and a number.';
    if ($pw !== $pw2) $err['password2'] = 'Passwords do not match.';
    if (!$err && q('SELECT 1 FROM users WHERE email=?', [$email])->fetch()) $err['email'] = 'An account with this email already exists.';
    if (!$err) {
        q('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)', [$name, $email, password_hash($pw, PASSWORD_DEFAULT), 'student']);
        flash('Account created. You can sign in now.'); redirect('login.php');
    }
}
include 'includes/header.php'; ?>
<div class="card narrow"><h1>Create a student account</h1>
<form method="post" novalidate data-validate><?= csrf_field() ?>
  <label>Full name<input name="name" required minlength="2" maxlength="80" value="<?= old('name') ?>"></label><?php if(isset($err['name'])): ?><p class="err"><?= e($err['name']) ?></p><?php endif; ?>
  <label>Email<input type="email" name="email" required maxlength="160" value="<?= old('email') ?>"></label><?php if(isset($err['email'])): ?><p class="err"><?= e($err['email']) ?></p><?php endif; ?>
  <label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label><?php if(isset($err['password'])): ?><p class="err"><?= e($err['password']) ?></p><?php endif; ?>
  <label>Confirm password<input type="password" name="password2" required minlength="8" autocomplete="new-password"></label><?php if(isset($err['password2'])): ?><p class="err"><?= e($err['password2']) ?></p><?php endif; ?>
  <button class="btn">Create account</button>
</form><p class="muted">Already registered? <a href="login.php">Sign in</a></p></div>
<?php include 'includes/footer.php'; ?>
