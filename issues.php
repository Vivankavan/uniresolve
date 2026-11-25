<?php require 'includes/bootstrap.php'; $title = 'Issues';
$u = require_login(); $w = []; $a = [];
if (!is_staff($u)) { $w[] = 'i.user_id=?'; $a[] = $u['id']; }
$s = $_GET['status'] ?? ''; if (in_array($s, STATUSES, true)) { $w[] = 'i.status=?'; $a[] = $s; }
$g = $_GET['urgency'] ?? ''; if (in_array($g, URGENCIES, true)) { $w[] = 'i.urgency=?'; $a[] = $g; }
$cat = (int)($_GET['category'] ?? 0); if ($cat) { $w[] = 'i.category_id=?'; $a[] = $cat; }
$term = trim($_GET['q'] ?? ''); if ($term !== '') { $w[] = '(i.title LIKE ? OR i.ref_no LIKE ?)'; $like = '%' . addcslashes($term, '%_\\') . '%'; array_push($a, $like, $like); }
if (is_staff($u) && !empty($_GET['mine'])) { $w[] = 'i.assigned_to=?'; $a[] = $u['id']; }
$rows = q('SELECT i.*,c.name cat,d.name dept FROM issues i JOIN categories c ON c.id=i.category_id JOIN departments d ON d.id=i.department_id'
  . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY i.created_at DESC LIMIT 100', $a)->fetchAll();
$cats = q('SELECT id,name FROM categories ORDER BY name')->fetchAll();
include 'includes/header.php'; ?>
<h1><?= is_staff($u) ? 'All issues' : 'My issues' ?></h1>
<form class="filters" method="get">
  <input name="q" placeholder="Search title or reference" value="<?= e($term) ?>">
  <select name="status"><option value="">Any status</option><?php foreach (STATUSES as $x): ?><option <?= $s===$x?'selected':'' ?>><?= $x ?></option><?php endforeach; ?></select>
  <select name="urgency"><option value="">Any urgency</option><?php foreach (URGENCIES as $x): ?><option <?= $g===$x?'selected':'' ?>><?= $x ?></option><?php endforeach; ?></select>
  <select name="category"><option value="">Any category</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <?php if (is_staff($u)): ?><label class="chk"><input type="checkbox" name="mine" value="1" <?= !empty($_GET['mine'])?'checked':'' ?>> Assigned to me</label><?php endif; ?>
  <button class="btn small">Filter</button>
</form>
<?php if (!$rows): ?><div class="card empty"><p>No issues match.</p><?php if ($u['role']==='student'): ?><a class="btn" href="report.php">Report an issue</a><?php endif; ?></div><?php endif; ?>
<div class="tickets">
<?php foreach ($rows as $r): ?>
  <a class="ticket u-<?= strtolower($r['urgency']) ?>" href="issue.php?id=<?= $r['id'] ?>">
    <span class="tref"><?= e($r['ref_no']) ?></span>
    <span class="ttitle"><?= e($r['title']) ?></span>
    <span class="tmeta"><?= e($r['cat']) ?> &middot; <?= e($r['dept']) ?> &middot; <?= fmt($r['created_at']) ?></span>
    <span class="tbadges"><?= badge($r['urgency']) ?> <?= badge($r['status']) ?></span>
  </a>
<?php endforeach; ?></div>
<?php include 'includes/footer.php'; ?>
