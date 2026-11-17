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
    header('Location: liste_categories.php?error=1');
    exit;
}

$check = mysqli_prepare($connexion, "SELECT COUNT(*) FROM produits WHERE categorie_id = ?");
mysqli_stmt_bind_param($check, 'i', $id);
mysqli_stmt_execute($check);
$count = (int)mysqli_fetch_row(mysqli_stmt_get_result($check))[0];
if ($count > 0) {
    header('Location: liste_categories.php?error=fk');
    exit;
}

$stmt = mysqli_prepare($connexion, "DELETE FROM categories WHERE categorie_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {
    header('Location: liste_categories.php?deleted=1');
} else {
    header('Location: liste_categories.php?error=1');
}
exit;
