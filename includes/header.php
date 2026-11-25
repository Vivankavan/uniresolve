<?php $u = current_user(); $page = basename($_SERVER['PHP_SELF']); ?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(($title ?? 'Home') . ' | UniResolve') ?></title>
<link rel="stylesheet" href="assets/style.css">
</head><body>
<header class="top">
  <a class="brand" href="index.php"><span class="mark"></span>UniResolve</a>
  <button class="burger" id="burger" aria-expanded="false" aria-controls="nav">Menu</button>
  <nav id="nav">
  <?php if ($u): ?>
    <a href="dashboard.php" <?= $page==='dashboard.php'?'class="on"':'' ?>>Dashboard</a>
    <a href="issues.php" <?= $page==='issues.php'?'class="on"':'' ?>><?= is_staff($u) ? 'All issues' : 'My issues' ?></a>
    <?php if ($u['role']==='student'): ?><a href="report.php" <?= $page==='report.php'?'class="on"':'' ?>>Report an issue</a><?php endif; ?>
    <?php if ($u['role']==='admin'): ?><a href="admin_users.php" <?= $page==='admin_users.php'?'class="on"':'' ?>>Users</a><?php endif; ?>
    <form method="post" action="logout.php"><?= csrf_field() ?><button class="linkbtn">Sign out (<?= e(explode(' ', $u['name'])[0]) ?>)</button></form>
  <?php else: ?>
    <a href="login.php">Sign in</a><a class="cta" href="register.php">Create account</a>
  <?php endif; ?>
  </nav>
</header>
<main class="wrap">
<?php foreach (flash() as [$t, $m]): ?><div class="alert a-<?= e($t) ?>" role="alert"><?= e($m) ?></div><?php endforeach; ?>
