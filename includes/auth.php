<?php
date_default_timezone_set('UTC');
header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite'=>'Lax', 'path'=>'/']);
    session_start();
}
if (isset($_SESSION['user_id'])) {
    if (time() - ($_SESSION['login_time'] ?? 0) > 7200) {
        $_SESSION = []; session_regenerate_id(true);
    } else {
        require_once __DIR__.'/db.php';
        $s=$conn->prepare('SELECT name,email,role,email_verified,is_active FROM users WHERE id=?');
        $s->bind_param('i',$_SESSION['user_id']); $s->execute(); $u=$s->get_result()->fetch_assoc();
        if (!$u || !$u['email_verified'] || !$u['is_active']) { $_SESSION=[]; session_regenerate_id(true); }
        else { $_SESSION['user_role']=$u['role']; $_SESSION['user_name']=$u['name']; $_SESSION['user_email']=$u['email']; }
    }
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function csrfField() { return '<input type="hidden" name="csrf" value="'.htmlspecialchars($_SESSION['csrf'],ENT_QUOTES).'">'; }
function checkCsrf() {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) { http_response_code(403); exit('Your form expired. Reload the page and try again.'); }
}
function requireLogin() { if (!isset($_SESSION['user_id'])) { if (basename($_SERVER['SCRIPT_NAME'] ?? '')==='checkout.php') $_SESSION['after_login']='checkout.php'; header('Location: /pages/login.php'); exit; } }
function requireAdmin() { requireLogin(); if ($_SESSION['user_role'] !== 'admin') { http_response_code(403); exit('Administrator access required. <a href="/pages/account.php">My account</a>'); } }
function requireCustomer() { requireLogin(); if ($_SESSION['user_role'] !== 'customer') { header('Location: /admin/index.php'); exit; } }

function productImageUrl($name) {
 $name=basename((string)$name);
 return $name && is_file(__DIR__.'/../assets/images/products/'.$name) ? '/assets/images/products/'.rawurlencode($name) : '/assets/images/logo/logo.png';
}
