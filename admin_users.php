<?php require 'includes/bootstrap.php'; $title = 'Users';
$u = require_role('admin'); $err = [];
$depts = q('SELECT id,name FROM departments ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'create') {
        $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? '')); $pw = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? ''; $dep = (int)($_POST['department'] ?? 0);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 80) $err[] = 'Enter a name (2 to 80 characters).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Enter a valid email.';
        if (strlen($pw) < 8 || !preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) $err[] = 'Password needs 8+ characters with a letter and a number.';
        if (!in_array($role, ['staff', 'admin'], true)) $err[] = 'Choose a role.';
        if (!$err && q('SELECT 1 FROM users WHERE email=?', [$email])->fetch()) $err[] = 'That email is already registered.';
        if (!$err) {
            q('INSERT INTO users (name,email,password_hash,role,department_id) VALUES (?,?,?,?,?)', [$name, $email, password_hash($pw, PASSWORD_DEFAULT), $role, $dep ?: null]);
            flash('Account created.'); redirect('admin_users.php');
        }
    } elseif (($_POST['action'] ?? '') === 'role') {
        $id = (int)($_POST['id'] ?? 0); $role = $_POST['role'] ?? '';
        if ($id === (int)$u['id']) flash('You cannot change your own role.', 'err');
        elseif (in_array($role, ['student', 'staff', 'admin'], true)) { q('UPDATE users SET role=? WHERE id=?', [$role, $id]); flash('Role updated.'); }
        redirect('admin_users.php');
    }
}
$users = q('SELECT u.*,d.name dept FROM users u LEFT JOIN departments d ON d.id=u.department_id ORDER BY u.role,u.name')->fetchAll();
include 'includes/header.php'; ?>
<h1>Users</h1>
<div class="grid2">
<section class="card"><h2>Add staff or admin</h2>
<?php foreach ($err as $m): ?><p class="err"><?= e($m) ?></p><?php endforeach; ?>
<form method="post" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="create">
  <label>Name<input name="name" required maxlength="80" value="<?= old('name') ?>"></label>
  <label>Email<input type="email" name="email" required value="<?= old('email') ?>"></label>
  <label>Temporary password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
  <label>Role<select name="role"><option>staff</option><option>admin</option></select></label>
  <label>Department<select name="department"><option value="0">None</option><?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select></label>
  <button class="btn">Create account</button></form></section>
<section class="card"><h2>All users</h2>
<?php foreach ($users as $r): ?>
  <form method="post" class="urow"><?= csrf_field() ?><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?= $r['id'] ?>">
    <span><strong><?= e($r['name']) ?></strong><small><?= e($r['email']) ?></small></span>
    <select name="role"><?php foreach (['student','staff','admin'] as $x): ?><option <?= $r['role']===$x?'selected':'' ?>><?= $x ?></option><?php endforeach; ?></select>
    <button class="btn small">Save</button></form>
<?php endforeach; ?></section></div>
<?php include 'includes/footer.php'; ?>
