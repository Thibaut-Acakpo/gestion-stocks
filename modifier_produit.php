<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';
if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}
$stmt = mysqli_prepare($connexion, "SELECT * FROM produits WHERE produit_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$produit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$produit) {
    header('Location: index.php');
    exit;
}
$categories = mysqli_query($connexion, "SELECT categorie_id, nom FROM categories ORDER BY nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier le produit | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('products'); ?>
<main class="page-wrap">
    <a class="back-link" href="index.php"><?= app_icon('back') ?> Retour au catalogue</a>
    <div class="page-heading"><div><h1 class="page-title-icon"><?= app_icon('edit') ?> Modifier le produit</h1><p>Modifiez les informations de « <?= htmlspecialchars($produit['nom'], ENT_QUOTES, 'UTF-8') ?> ».</p></div></div>
    <section class="panel form-panel">
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php $err = (string)$_GET['error']; if ($err === '2') echo 'Veuillez remplir tous les champs obligatoires.'; elseif ($err === '3') echo 'Les prix doivent être positifs et la quantité ne peut pas être négative.'; else echo 'La modification a échoué. Vérifiez les informations et réessayez.'; ?></div><?php endif; ?>
        <?php if (mysqli_num_rows($categories) === 0): ?><div class="alert alert-warning">Aucune catégorie n'existe. Créez-en une avant de modifier ce produit.</div><?php endif; ?>
        <form action="tr_modifier_produit.php" method="post" data-confirm-title="Modifier ce produit ?" data-confirm-message="Les nouvelles informations remplaceront celles enregistrées actuellement." data-confirm-label="Modifier" data-confirm-type="primary">
            <?= app_csrf_field() ?><input type="hidden" name="produit_id" value="<?= (int)$produit['produit_id'] ?>">
            <div class="row g-3">
                <div class="col-12"><label class="form-label" for="nom">Nom du produit</label><input id="nom" type="text" class="form-control" name="nom" value="<?= htmlspecialchars($produit['nom'], ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required></div>
                <div class="col-md-6"><label class="form-label" for="prix_achat">Prix d'achat (FCFA)</label><input id="prix_achat" type="number" step="1" min="1" class="form-control" name="prix_achat" value="<?= htmlspecialchars($produit['prix_achat'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="prix_vente">Prix de vente (FCFA)</label><input id="prix_vente" type="number" step="1" min="1" class="form-control" name="prix_vente" value="<?= htmlspecialchars($produit['prix_vente'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="quantite">Quantité en stock</label><input id="quantite" type="number" min="0" step="1" class="form-control" name="quantite" value="<?= htmlspecialchars($produit['quantite'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="categorie_id">Catégorie</label><select id="categorie_id" name="categorie_id" class="form-select" required><option value="">Choisir une catégorie</option><?php while ($cat = mysqli_fetch_assoc($categories)): ?><option value="<?= (int)$cat['categorie_id'] ?>" <?= (int)$cat['categorie_id'] === (int)$produit['categorie_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endwhile; ?></select></div>
            </div>
            <div class="form-actions"><button type="submit" name="submit" class="btn btn-primary"><?= app_icon('check') ?> Enregistrer les modifications</button><a href="index.php" class="btn btn-outline-secondary"><?= app_icon('back') ?> Annuler</a></div>
        </form>
    </section>
</main>
<script src="js/app.js?v=20261118"></script>
</body>
</html>
