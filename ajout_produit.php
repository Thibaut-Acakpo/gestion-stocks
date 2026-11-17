<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';
if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}
$categories = mysqli_query($connexion, "SELECT categorie_id, nom FROM categories ORDER BY nom");
$hasCategories = mysqli_num_rows($categories) > 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau produit | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('products'); ?>
<main class="page-wrap">
    <a class="back-link" href="index.php"><?= app_icon('back') ?> Retour au catalogue</a>
    <div class="page-heading"><div><h1 class="page-title-icon"><?= app_icon('products') ?> Ajouter un produit</h1><p>Renseignez les informations de l'article à stocker.</p></div></div>
    <section class="panel form-panel">
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Produit ajouté avec succès.</div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php $err = (string)$_GET['error']; if ($err === '2') echo 'Veuillez remplir tous les champs obligatoires.'; elseif ($err === '3') echo 'Les prix doivent être positifs et la quantité ne peut pas être négative.'; else echo 'Une erreur est survenue. Vérifiez les informations et réessayez.'; ?></div><?php endif; ?>
        <?php if (!$hasCategories): ?><div class="alert alert-warning">Créez d'abord une catégorie avant d'ajouter un produit. <a href="ajout_categorie.php" class="alert-link">Créer une catégorie</a>.</div><?php endif; ?>
        <form action="tr_ajout_produit.php" method="post" data-confirm-title="Ajouter ce produit ?" data-confirm-message="Ce produit sera ajouté au catalogue avec les informations saisies." data-confirm-label="Ajouter" data-confirm-type="primary">
            <?= app_csrf_field() ?>
            <div class="row g-3">
                <div class="col-12"><label class="form-label" for="nom">Nom du produit</label><input id="nom" type="text" class="form-control" name="nom" placeholder="Ex. Riz local" maxlength="255" required></div>
                <div class="col-md-6"><label class="form-label" for="prix_achat">Prix d'achat (FCFA)</label><input id="prix_achat" type="number" step="1" min="1" class="form-control" name="prix_achat" placeholder="Ex. 500" required></div>
                <div class="col-md-6"><label class="form-label" for="prix_vente">Prix de vente (FCFA)</label><input id="prix_vente" type="number" step="1" min="1" class="form-control" name="prix_vente" placeholder="Ex. 700" required></div>
                <div class="col-md-6"><label class="form-label" for="quantite">Quantité en stock</label><input id="quantite" type="number" step="1" min="0" class="form-control" name="quantite" placeholder="Ex. 50" required></div>
                <div class="col-md-6"><label class="form-label" for="categorie_id">Catégorie</label><select id="categorie_id" name="categorie_id" class="form-select" required><option value="">Choisir une catégorie</option><?php while ($cat = mysqli_fetch_assoc($categories)): ?><option value="<?= (int)$cat['categorie_id'] ?>"><?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endwhile; ?></select></div>
            </div>
            <div class="form-actions"><button type="submit" name="submit" class="btn btn-primary" <?= !$hasCategories ? 'disabled' : '' ?>><?= app_icon('check') ?> Enregistrer le produit</button><a href="index.php" class="btn btn-outline-secondary"><?= app_icon('back') ?> Annuler</a></div>
        </form>
    </section>
</main>
<script src="js/app.js?v=20261118"></script>
</body>
</html>
