<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';

if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'administrateur') {
    header('Location: connecter.php');
    exit;
}

$nb_produits = mysqli_fetch_row(mysqli_query($connexion, "SELECT COUNT(*) FROM produits"))[0];
$nb_categories = mysqli_fetch_row(mysqli_query($connexion, "SELECT COUNT(*) FROM categories"))[0];
$nb_ventes = mysqli_fetch_row(mysqli_query($connexion, "SELECT COUNT(*) FROM ventes"))[0];
$ca_total = mysqli_fetch_row(mysqli_query($connexion, "SELECT COALESCE(SUM(montant_total), 0) FROM ventes"))[0];
$alertes = mysqli_query($connexion, "SELECT nom, quantite, statut FROM produits WHERE statut IN ('faible', 'rupture') ORDER BY statut DESC");
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
            <p>Voici le résumé de votre activité aujourd'hui.</p>
        </div>
        <a href="ajout_vente.php" class="btn btn-primary"><?= app_icon('plus') ?> Enregistrer une vente</a>
    </div>

    <section class="row g-3 mb-4" aria-label="Statistiques">
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="d-flex justify-content-between align-items-center"><span class="stat-label">Produits</span><span class="stat-icon"><?= app_icon('products') ?></span></div><p class="stat-value"><?= (int)$nb_produits ?></p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="d-flex justify-content-between align-items-center"><span class="stat-label">Catégories</span><span class="stat-icon"><?= app_icon('categories') ?></span></div><p class="stat-value"><?= (int)$nb_categories ?></p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="d-flex justify-content-between align-items-center"><span class="stat-label">Ventes enregistrées</span><span class="stat-icon"><?= app_icon('sales') ?></span></div><p class="stat-value"><?= (int)$nb_ventes ?></p></div></div>
        <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="d-flex justify-content-between align-items-center"><span class="stat-label">Chiffre d'affaires</span><span class="stat-icon"><?= app_icon('sales') ?></span></div><p class="stat-value"><?= number_format((float)$ca_total, 0, ',', ' ') ?> <small>FCFA</small></p></div></div>
    </section>

    <section class="row g-4">
        <div class="col-lg-7">
            <div class="panel dashboard-alerts p-0">
                <div class="d-flex align-items-center gap-2 p-3 border-bottom">
                    <span class="text-warning"><?= app_icon('warning') ?></span>
                    <h2>À surveiller — stock bas ou épuisé</h2>
                </div>
                <?php if (mysqli_num_rows($alertes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Produit</th><th>Quantité</th><th>État</th></tr></thead>
                            <tbody>
                            <?php while ($a = mysqli_fetch_assoc($alertes)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($a['quantite'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge text-bg-<?= $a['statut'] === 'rupture' ? 'danger' : 'warning' ?>"><?= htmlspecialchars($a['statut'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state text-success">Aucun produit en stock faible ou épuisé.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel h-100">
                <h2 class="h5 fw-bold mb-3">Accès rapides</h2>
                <div class="d-grid gap-2">
                    <a href="ajout_produit.php" class="btn btn-outline-primary quick-link"><?= app_icon('plus') ?> Ajouter un produit</a>
                    <a href="ajout_categorie.php" class="btn btn-outline-primary quick-link"><?= app_icon('plus') ?> Créer une catégorie</a>
                    <a href="liste_ventes.php" class="btn btn-outline-primary quick-link"><?= app_icon('sales') ?> Consulter les ventes</a>
                </div>
            </div>
        </div>
    </section>
</main>
<script src="js/app.js?v=20261118"></script>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
