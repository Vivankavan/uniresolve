<?php
// Creates demo accounts + sample issues. Run ONCE, then DELETE this file.
//   Local:   php seed.php
//   Hosting: open seed.php?token=YOUR_SEED_TOKEN in the browser (token is in your .env)
require 'includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
if (PHP_SAPI !== 'cli' && (empty($_ENV['SEED_TOKEN']) || !hash_equals($_ENV['SEED_TOKEN'], (string)($_GET['token'] ?? '')))) { http_response_code(403); exit("Forbidden. Add ?token=YOUR_SEED_TOKEN\n"); }
if ((int)q('SELECT COUNT(*) FROM users')->fetchColumn() > 0) exit("Users already exist. Nothing changed.\n");
$mk = function (string $n, string $e, string $r, int $dep = 0): array {
    $pw = substr(bin2hex(random_bytes(6)), 0, 10) . 'a1';
    q('INSERT INTO users (name,email,password_hash,role,department_id) VALUES (?,?,?,?,?)', [$n, $e, password_hash($pw, PASSWORD_DEFAULT), $r, $dep ?: null]);
    return [(int)db()->lastInsertId(), $e, $pw];
};
$admin = $mk('Demo Admin', 'admin@uniresolve.test', 'admin');
$staff = $mk('Demo Staff', 'staff@uniresolve.test', 'staff', 1);
$stud  = $mk('Demo Student', 'student@uniresolve.test', 'student');
$samples = [['Corridor light flickering in Block C', 1, 'Medium', 'Block C, 2nd floor'], ['Wi-Fi drops in Computer Lab 3', 4, 'High', 'Lab 3'],
            ['Water leak near hostel bathroom', 2, 'Critical', 'Hostel A, ground floor'], ['Library AC too cold', 8, 'Low', 'Reading hall']];
foreach ($samples as $k => [$t, $c, $urg, $loc]) {
    $dep = (int)q('SELECT department_id FROM categories WHERE id=?', [$c])->fetchColumn();
    q('INSERT INTO issues (ref_no,user_id,title,description,location,category_id,urgency,department_id,created_at) VALUES (?,?,?,?,?,?,?,?,NOW() - INTERVAL ? DAY)',
      ['TMP' . $k, $stud[0], $t, 'Sample issue created by seed.php: ' . $t . '.', $loc, $c, $urg, $dep, $k * 2]);
    $id = (int)db()->lastInsertId();
    q('UPDATE issues SET ref_no=? WHERE id=?', [sprintf('UR-%s-%06d', date('Y'), $id), $id]);
    q('INSERT INTO status_history (issue_id,old_status,new_status,changed_by) VALUES (?,NULL,?,?)', [$id, 'Open', $stud[0]]);
}
echo "Seed complete. SAVE THESE LOGINS NOW (shown only once):\n";
foreach ([$admin, $staff, $stud] as [, $e, $p]) echo "  $e  /  $p\n";
echo "\nNow DELETE seed.php from your site.\n";
