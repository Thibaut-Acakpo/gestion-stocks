<?php
// Configuration commune : base de données, session, protection CSRF, contrôle des rôles.
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';            // WAMP : mot de passe root vide par défaut
const DB_NAME = 'gestion_users';

ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
if (session_status() === PHP_SESSION_NONE) session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Base de données inaccessible : vérifiez que WAMP est démarré et les valeurs de config.php.');
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_champ(): string { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function csrf_verifier(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Session expirée. Rechargez la page et recommencez.');
    }
}
function utilisateur(): ?array { return $_SESSION['user'] ?? null; }
function exiger_role(string ...$roles): void {
    $u = utilisateur();
    if (!$u) { header('Location: connecter.php'); exit; }
    if ($roles && !in_array($u['role'], $roles, true)) { http_response_code(403); exit('Accès refusé.'); }
}
