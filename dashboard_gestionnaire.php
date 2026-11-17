<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';

if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'gestionnaire') {
    header('Location: connecter.php');
    exit;
}

$nb_produits = mysqli_fetch_row(mysqli_query($connexion, "SELECT COUNT(*) FROM produits"))[0];
$nb_categories = mysqli_fetch_row(mysqli_query($connexion, "SELECT COUNT(*) FROM categories"))[0];
$nb_ventes = mysqli_fetch_row(mysqli_query($connexion, "SELECT COUNT(*) FROM ventes"))[0];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('dashboard'); ?>
<main class="page-wrap">
    <div class="page-heading">
        <div>
            <h1>Bonjour, <?= htmlspecialchars($_SESSION['user_nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Consultez rapidement l'état de votre stock et de vos ventes.</p>
        </div>
        <a href="ajout_vente.php" class="btn btn-primary"><?= app_icon('plus') ?> Enregistrer une vente</a>
    </div>
    <section class="row g-3 mb-4" aria-label="Statistiques">
        <div class="col-md-4"><div class="stat-card"><div class="d-flex justify-content-between align-items-center"><span class="stat-label">Produits</span><span class="stat-icon"><?= app_icon('products') ?></span></div><p class="stat-value"><?= (int)$nb_produits ?></p><a class="small" href="index.php">Voir le catalogue</a></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="d-flex justify-content-between align-items-center"><span class="stat-label">Catégories</span><span class="stat-icon"><?= app_icon('categories') ?></span></div><p class="stat-value"><?= (int)$nb_categories ?></p><a class="small" href="liste_categories.php">Voir les catégories</a></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="d-flex justify-content-between align-items-center"><span class="stat-label">Ventes enregistrées</span><span class="stat-icon"><?= app_icon('sales') ?></span></div><p class="stat-value"><?= (int)$nb_ventes ?></p><a class="small" href="liste_ventes.php">Voir l'historique</a></div></div>
    </section>
    <section class="panel">
        <h2 class="h5 fw-bold mb-3">Accès rapides</h2>
        <div class="row g-3">
            <div class="col-md-6"><a href="ajout_produit.php" class="btn btn-outline-primary quick-link w-100"><?= app_icon('plus') ?> Ajouter un produit</a></div>
            <div class="col-md-6"><a href="ajout_vente.php" class="btn btn-primary quick-link w-100"><?= app_icon('sales') ?> Enregistrer une vente</a></div>
        </div>
    </section>
</main>
<script src="js/app.js?v=20261118"></script>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
