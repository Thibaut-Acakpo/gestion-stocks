<?php
session_start();
require __DIR__ . '/app_ui.php';
if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php?logout=1');
    exit;
}
app_verify_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: connecter.php');
exit;
