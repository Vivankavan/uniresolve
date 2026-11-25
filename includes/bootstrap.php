<?php
declare(strict_types=1);
const STATUSES = ['Open','Assigned','In Progress','Resolved','Closed'];
const URGENCIES = ['Low','Medium','High','Critical'];

(function () {
    $f = __DIR__ . '/../.env';
    if (!is_file($f)) return;
    foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        $l = trim($l);
        if ($l === '' || $l[0] === '#' || !str_contains($l, '=')) continue;
        [$k, $v] = explode('=', $l, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
})();

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') . ';port=' . ($_ENV['DB_PORT'] ?? '3306')
                 . ';dbname=' . ($_ENV['DB_NAME'] ?? '') . ';charset=utf8mb4';
            $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? '', $_ENV['DB_PASS'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed. Check your .env file.');
        }
    }
    return $pdo;
}
/** Run a prepared (injection-safe) query. */
function q(string $sql, array $args = []): PDOStatement { $s = db()->prepare($sql); $s->execute($args); return $s; }
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $to): never { header('Location: ' . $to); exit; }
function flash(?string $msg = null, string $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'][] = [$type, $msg]; return; }
    $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f;
}
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('Your session expired. Go back, refresh the page and try again.');
    }
}
function current_user(): ?array {
    static $u = false;
    if ($u === false) {
        $u = isset($_SESSION['uid']) ? (q('SELECT id,name,email,role,department_id FROM users WHERE id=?', [$_SESSION['uid']])->fetch() ?: null) : null;
    }
    return $u;
}
function require_login(): array { $u = current_user(); if (!$u) { flash('Please sign in first.', 'err'); redirect('login.php'); } return $u; }
function require_role(string ...$roles): array {
    $u = require_login();
    if (!in_array($u['role'], $roles, true)) { http_response_code(403); exit('You do not have access to this page.'); }
    return $u;
}
function is_staff(array $u): bool { return in_array($u['role'], ['staff', 'admin'], true); }
function badge(string $v): string { return '<span class="badge b-' . strtolower(str_replace(' ', '-', $v)) . '">' . e($v) . '</span>'; }
function fmt(?string $d): string { return $d ? date('d M Y, H:i', strtotime($d)) : '-'; }
function old(string $k): string { return e($_POST[$k] ?? ''); }
