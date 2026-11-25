<?php require 'includes/bootstrap.php'; $title = 'Home'; $u = current_user();
$n = (int)q("SELECT COUNT(*) FROM issues WHERE status IN ('Resolved','Closed')")->fetchColumn();
include 'includes/header.php'; ?>
<section class="hero">
  <div>
    <h1>Broken light in Block C? Wi-Fi down in the lab? Tell us once, then watch it get fixed.</h1>
    <p>UniResolve sends your report to the right campus department, gives it a reference number, and shows every update until it is resolved.</p>
    <p>
      <?php if ($u): ?><a class="btn" href="<?= $u['role']==='student' ? 'report.php' : 'issues.php' ?>"><?= $u['role']==='student' ? 'Report an issue' : 'Open the issue queue' ?></a>
      <?php else: ?><a class="btn" href="register.php">Create a student account</a> <a class="btn ghost" href="login.php">Sign in</a><?php endif; ?>
    </p>
  </div>
  <aside class="stub">
    <div class="ref">UR-<?= date('Y') ?>-000123</div>
    <dl><dt>Category</dt><dd>Electrical / Lighting</dd><dt>Department</dt><dd>Facilities &amp; Maintenance</dd><dt>Status</dt><dd><?= badge('In Progress') ?></dd></dl>
    <p class="muted"><?= $n ?> issue<?= $n==1?'':'s' ?> resolved so far.</p>
  </aside>
</section>
<?php include 'includes/footer.php'; ?>
