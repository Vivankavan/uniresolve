<?php require 'includes/bootstrap.php'; $title = 'Dashboard';
$u = require_login(); $own = !is_staff($u);
$W = $own ? 'WHERE i.user_id=' . (int)$u['id'] : '';
$AND = $own ? ' AND i.user_id=' . (int)$u['id'] : '';
$count = fn($c) => (int)q("SELECT COUNT(*) FROM issues i WHERE $c$AND")->fetchColumn();
$cards = ['Total' => $count('1=1'), 'Open' => $count("i.status IN ('Open','Assigned')"), 'In progress' => $count("i.status='In Progress'"),
          'Resolved' => $count("i.status IN ('Resolved','Closed')"), 'Critical, unresolved' => $count("i.urgency='Critical' AND i.status NOT IN ('Resolved','Closed')")];
$group = fn($sql) => q($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
$byStatus = $group("SELECT i.status,COUNT(*) FROM issues i $W GROUP BY i.status");
$byUrg = $group("SELECT i.urgency,COUNT(*) FROM issues i $W GROUP BY i.urgency");
$byCat = $group("SELECT c.name,COUNT(*) FROM issues i JOIN categories c ON c.id=i.category_id $W GROUP BY c.name ORDER BY 2 DESC");
$byDept = $group("SELECT d.name,COUNT(*) FROM issues i JOIN departments d ON d.id=i.department_id $W GROUP BY d.name ORDER BY 2 DESC");
$days = $group("SELECT DATE(i.created_at),COUNT(*) FROM issues i WHERE i.created_at >= (CURDATE() - INTERVAL 13 DAY)$AND GROUP BY DATE(i.created_at)");
$avg = q("SELECT AVG(TIMESTAMPDIFF(HOUR,i.created_at,i.resolved_at)) FROM issues i WHERE i.resolved_at IS NOT NULL$AND")->fetchColumn();
$recent = q("SELECT i.id,i.ref_no,i.title,i.status FROM issues i $W ORDER BY i.created_at DESC LIMIT 5")->fetchAll();
function bars(array $data, array $order = []): void {
    if (!$data) { echo '<p class="muted">No data yet.</p>'; return; }
    if ($order) { $d = []; foreach ($order as $k) $d[$k] = $data[$k] ?? 0; $data = $d; }
    $max = max($data) ?: 1;
    foreach ($data as $k => $v) echo '<div class="bar"><span>' . e($k) . '</span><i style="width:' . round($v / $max * 100) . '%"></i><b>' . (int)$v . '</b></div>';
}
$series = []; for ($n = 13; $n >= 0; $n--) { $d = date('Y-m-d', strtotime("-$n day")); $series[date('d M', strtotime($d))] = $days[$d] ?? 0; }
include 'includes/header.php'; ?>
<h1>Hello, <?= e(explode(' ', $u['name'])[0]) ?></h1>
<div class="stats"><?php foreach ($cards as $k => $v): ?><div class="stat"><b><?= $v ?></b><span><?= e($k) ?></span></div><?php endforeach; ?></div>
<p class="muted"><?= $avg !== null && $avg !== false ? 'Average time to resolve: ' . round((float)$avg, 1) . ' hours.' : 'No resolved issues yet.' ?></p>
<div class="grid2">
  <section class="card"><h2>By status</h2><?php bars($byStatus, STATUSES); ?></section>
  <section class="card"><h2>By urgency</h2><?php bars($byUrg, URGENCIES); ?></section>
  <section class="card"><h2>By category</h2><?php bars($byCat); ?></section>
  <section class="card"><h2>By department</h2><?php bars($byDept); ?></section>
  <section class="card"><h2>Reports in the last 14 days</h2><?php bars($series); ?></section>
  <section class="card"><h2>Recent issues</h2>
    <?php foreach ($recent as $r): ?><p><a href="issue.php?id=<?= $r['id'] ?>"><?= e($r['ref_no']) ?></a> <?= e($r['title']) ?> <?= badge($r['status']) ?></p><?php endforeach; ?>
    <?php if (!$recent): ?><p class="muted">Nothing reported yet.</p><?php endif; ?></section>
</div>
<?php include 'includes/footer.php'; ?>
