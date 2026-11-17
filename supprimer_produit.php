<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';

if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}
app_verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php?error=1');
    exit;
}

$check = mysqli_prepare($connexion, "SELECT COUNT(*) FROM ventes WHERE produit_id = ?");
mysqli_stmt_bind_param($check, 'i', $id);
mysqli_stmt_execute($check);
if ((int)mysqli_fetch_row(mysqli_stmt_get_result($check))[0] > 0) {
    header('Location: index.php?error=sales');
    exit;
}

$stmt = mysqli_prepare($connexion, "DELETE FROM produits WHERE produit_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {
    header('Location: index.php?deleted=1');
} else {
    header('Location: index.php?error=1');
}
exit;
