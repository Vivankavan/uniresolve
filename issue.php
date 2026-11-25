<?php require 'includes/bootstrap.php'; $title = 'Issue';
$u = require_login(); $id = (int)($_GET['id'] ?? 0);
$fetch = fn() => q('SELECT i.*,c.name cat,d.name dept,s.name student,a.name assignee FROM issues i
  JOIN categories c ON c.id=i.category_id JOIN departments d ON d.id=i.department_id JOIN users s ON s.id=i.user_id
  LEFT JOIN users a ON a.id=i.assigned_to WHERE i.id=?', [$id])->fetch();
$i = $fetch();
if (!$i || (!is_staff($u) && (int)$i['user_id'] !== (int)$u['id'])) { http_response_code(404); exit('Issue not found.'); }
$staff = is_staff($u);

function set_status(array $i, string $new, int $by): void {
    if ($new === $i['status']) return;
    q('UPDATE issues SET status=?, resolved_at=? WHERE id=?', [$new, in_array($new, ['Resolved','Closed'], true) ? date('Y-m-d H:i:s') : null, $i['id']]);
    q('INSERT INTO status_history (issue_id,old_status,new_status,changed_by) VALUES (?,?,?,?)', [$i['id'], $i['status'], $new, $by]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $act = $_POST['action'] ?? ''; $body = trim($_POST['body'] ?? '');
    if (mb_strlen($body) > 2000) { flash('Message is too long (max 2000).', 'err'); redirect("issue.php?id=$id"); }
    if ($act === 'comment') {
        if (mb_strlen($body) < 2) flash('Write at least 2 characters.', 'err');
        else { q('INSERT INTO issue_updates (issue_id,user_id,body) VALUES (?,?,?)', [$id, $u['id'], $body]); flash('Comment added.'); }
    } elseif ($act === 'update' && $staff) {
        $new = $_POST['status'] ?? $i['status'];
        if (!in_array($new, STATUSES, true)) { flash('Invalid status.', 'err'); redirect("issue.php?id=$id"); }
        if ($body === '' && $new === $i['status']) { flash('Add a message or change the status.', 'err'); redirect("issue.php?id=$id"); }
        if ($body !== '') q('INSERT INTO issue_updates (issue_id,user_id,body,is_internal) VALUES (?,?,?,?)', [$id, $u['id'], $body, empty($_POST['internal']) ? 0 : 1]);
        set_status($i, $new, (int)$u['id']); flash('Update saved.');
    } elseif ($act === 'assign' && $u['role'] === 'admin') {
        $dept = (int)($_POST['department'] ?? 0); $to = (int)($_POST['assignee'] ?? 0);
        if (!q('SELECT 1 FROM departments WHERE id=?', [$dept])->fetch()) { flash('Choose a department.', 'err'); redirect("issue.php?id=$id"); }
        if ($to && !q("SELECT 1 FROM users WHERE id=? AND role IN ('staff','admin')", [$to])->fetch()) { flash('Invalid assignee.', 'err'); redirect("issue.php?id=$id"); }
        q('UPDATE issues SET department_id=?, assigned_to=? WHERE id=?', [$dept, $to ?: null, $id]);
        if ($i['status'] === 'Open') set_status($i, 'Assigned', (int)$u['id']);
        q('INSERT INTO issue_updates (issue_id,user_id,body,is_internal) VALUES (?,?,?,0)', [$id, $u['id'], 'Issue assigned to a department/staff member.']);
        flash('Assignment saved.');
    }
    redirect("issue.php?id=$id");
}

$ups = q('SELECT x.*,u.name,u.role FROM issue_updates x JOIN users u ON u.id=x.user_id WHERE issue_id=?' . ($staff ? '' : ' AND is_internal=0'), [$id])->fetchAll();
$his = q('SELECT h.*,u.name FROM status_history h JOIN users u ON u.id=h.changed_by WHERE issue_id=?', [$id])->fetchAll();
$tl = [];
foreach ($ups as $x) $tl[] = ['t' => $x['created_at'], 'k' => 'msg', 'x' => $x];
foreach ($his as $x) $tl[] = ['t' => $x['created_at'], 'k' => 'st', 'x' => $x];
usort($tl, fn($a, $b) => strcmp($a['t'], $b['t']));
$depts = q('SELECT id,name FROM departments ORDER BY name')->fetchAll();
$team = q("SELECT id,name,role FROM users WHERE role IN ('staff','admin') ORDER BY name")->fetchAll();
include 'includes/header.php'; ?>
<p><a href="issues.php">&larr; Back to issues</a></p>
<div class="detail">
 <section class="card">
  <div class="ref"><?= e($i['ref_no']) ?></div>
  <h1><?= e($i['title']) ?></h1>
  <p><?= badge($i['urgency']) ?> <?= badge($i['status']) ?></p>
  <p class="pre"><?= e($i['description']) ?></p>
  <dl class="facts">
    <dt>Category</dt><dd><?= e($i['cat']) ?></dd><dt>Department</dt><dd><?= e($i['dept']) ?></dd>
    <dt>Assigned to</dt><dd><?= e($i['assignee'] ?? 'Not assigned yet') ?></dd><dt>Location</dt><dd><?= e($i['location'] ?: '-') ?></dd>
    <dt>Reported by</dt><dd><?= e($i['student']) ?></dd><dt>Submitted</dt><dd><?= fmt($i['created_at']) ?></dd>
    <?php if ($i['resolved_at']): ?><dt>Resolved</dt><dd><?= fmt($i['resolved_at']) ?></dd><?php endif; ?>
  </dl>
 </section>
 <section class="card">
  <h2>Updates and history</h2>
  <ol class="timeline">
  <?php foreach ($tl as $e): $x = $e['x']; ?>
    <?php if ($e['k'] === 'st'): ?>
      <li class="st"><strong><?= $x['old_status'] ? 'Status changed: ' . e($x['old_status']) . ' to ' . e($x['new_status']) : 'Submitted as ' . e($x['new_status']) ?></strong><small><?= e($x['name']) ?> &middot; <?= fmt($x['created_at']) ?></small></li>
    <?php else: ?>
      <li class="msg <?= $x['is_internal'] ? 'internal' : '' ?>"><p class="pre"><?= e($x['body']) ?></p><small><?= e($x['name']) ?> (<?= e($x['role']) ?>)<?= $x['is_internal'] ? ' &middot; internal note' : '' ?> &middot; <?= fmt($x['created_at']) ?></small></li>
    <?php endif; ?>
  <?php endforeach; ?></ol>
  <?php if ($staff): ?>
  <form method="post" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="update">
    <h3>Post an update</h3>
    <label>Message<textarea name="body" rows="3" maxlength="2000"></textarea></label>
    <label>Status<select name="status"><?php foreach (STATUSES as $x): ?><option <?= $i['status']===$x?'selected':'' ?>><?= $x ?></option><?php endforeach; ?></select></label>
    <label class="chk"><input type="checkbox" name="internal" value="1"> Internal note (hidden from the student)</label>
    <button class="btn">Save update</button>
  </form>
  <?php else: ?>
  <form method="post" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="comment">
    <label>Add a comment<textarea name="body" rows="3" required minlength="2" maxlength="2000"></textarea></label>
    <button class="btn">Post comment</button>
  </form>
  <?php endif; ?>
  <?php if ($u['role'] === 'admin'): ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="assign">
    <h3>Assign (admin)</h3>
    <label>Department<select name="department"><?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>" <?= (int)$i['department_id']===(int)$d['id']?'selected':'' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></label>
    <label>Staff member<select name="assignee"><option value="0">Unassigned</option><?php foreach ($team as $t): ?><option value="<?= $t['id'] ?>" <?= (int)$i['assigned_to']===(int)$t['id']?'selected':'' ?>><?= e($t['name']) ?> (<?= e($t['role']) ?>)</option><?php endforeach; ?></select></label>
    <button class="btn">Save assignment</button>
  </form>
  <?php endif; ?>
 </section>
</div>
<?php include 'includes/footer.php'; ?>
