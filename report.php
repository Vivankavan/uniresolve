<?php require 'includes/bootstrap.php'; $title = 'Report an issue';
$u = require_role('student');
$cats = q('SELECT c.id,c.name,d.name dept FROM categories c JOIN departments d ON d.id=c.department_id ORDER BY c.name')->fetchAll();
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $t = trim($_POST['title'] ?? ''); $d = trim($_POST['description'] ?? ''); $loc = trim($_POST['location'] ?? '');
    $cat = (int)($_POST['category'] ?? 0); $urg = $_POST['urgency'] ?? '';
    $c = q('SELECT department_id FROM categories WHERE id=?', [$cat])->fetch();
    if (mb_strlen($t) < 5 || mb_strlen($t) > 120) $err['title'] = 'Title must be 5 to 120 characters.';
    if (mb_strlen($d) < 10 || mb_strlen($d) > 2000) $err['description'] = 'Describe the problem in 10 to 2000 characters.';
    if (mb_strlen($loc) > 120) $err['location'] = 'Location is too long (max 120).';
    if (!$c) $err['category'] = 'Choose a category.';
    if (!in_array($urg, URGENCIES, true)) $err['urgency'] = 'Choose an urgency.';
    if (!$err) {
        $pdo = db(); $pdo->beginTransaction();
        q('INSERT INTO issues (ref_no,user_id,title,description,location,category_id,urgency,department_id) VALUES (?,?,?,?,?,?,?,?)',
          ['TMP' . bin2hex(random_bytes(8)), $u['id'], $t, $d, $loc, $cat, $urg, $c['department_id']]);
        $id = (int)$pdo->lastInsertId();
        $ref = sprintf('UR-%s-%06d', date('Y'), $id);
        q('UPDATE issues SET ref_no=? WHERE id=?', [$ref, $id]);
        q('INSERT INTO status_history (issue_id,old_status,new_status,changed_by) VALUES (?,NULL,?,?)', [$id, 'Open', $u['id']]);
        $pdo->commit();
        flash("Issue submitted. Your reference number is $ref."); redirect('issue.php?id=' . $id);
    }
}
include 'includes/header.php'; ?>
<div class="card narrow"><h1>Report an issue</h1>
<form method="post" novalidate data-validate><?= csrf_field() ?>
  <label>Title<input name="title" required minlength="5" maxlength="120" value="<?= old('title') ?>" placeholder="e.g. Projector not working in Room 204"></label><?php if(isset($err['title'])): ?><p class="err"><?= e($err['title']) ?></p><?php endif; ?>
  <label>Category<select name="category" required><option value="">Choose...</option>
    <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= (int)($_POST['category'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?> (<?= e($c['dept']) ?>)</option><?php endforeach; ?></select></label><?php if(isset($err['category'])): ?><p class="err"><?= e($err['category']) ?></p><?php endif; ?>
  <label>Urgency<select name="urgency" required><?php foreach (URGENCIES as $x): ?><option <?= (($_POST['urgency'] ?? 'Medium')===$x)?'selected':'' ?>><?= $x ?></option><?php endforeach; ?></select></label>
  <label>Location<input name="location" maxlength="120" value="<?= old('location') ?>" placeholder="Building, floor, room"></label><?php if(isset($err['location'])): ?><p class="err"><?= e($err['location']) ?></p><?php endif; ?>
  <label>What is happening?<textarea name="description" rows="6" required minlength="10" maxlength="2000"><?= old('description') ?></textarea></label><?php if(isset($err['description'])): ?><p class="err"><?= e($err['description']) ?></p><?php endif; ?>
  <button class="btn">Submit issue</button>
</form></div>
<?php include 'includes/footer.php'; ?>
