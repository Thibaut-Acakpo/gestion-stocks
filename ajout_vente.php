<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';
if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}
$produits = mysqli_query($connexion, "SELECT produit_id, nom, prix_vente, quantite FROM produits WHERE quantite > 0 ORDER BY nom");
$hasProducts = mysqli_num_rows($produits) > 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouvelle vente | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('sales'); ?>
<main class="page-wrap">
    <a class="back-link" href="liste_ventes.php"><?= app_icon('back') ?> Retour aux ventes</a>
    <div class="page-heading"><div><h1 class="page-title-icon"><?= app_icon('sales') ?> Enregistrer une vente</h1><p>Le stock sera mis à jour après validation de la vente.</p></div></div>
    <section class="panel form-panel">
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Vente enregistrée et stock mis à jour.</div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php $err = (string)$_GET['error']; if ($err === 'stock') echo 'Stock insuffisant pour cette quantité.'; elseif ($err === '2') echo 'Veuillez sélectionner un produit et indiquer une quantité.'; elseif ($err === '3') echo 'La quantité doit être un nombre entier supérieur à zéro.'; else echo 'Une erreur est survenue. Vérifiez le stock et réessayez.'; ?></div><?php endif; ?>
        <?php if (!$hasProducts): ?><div class="alert alert-warning">Aucun produit disponible en stock. Réapprovisionnez votre catalogue avant d'enregistrer une vente.</div><?php endif; ?>
        <form action="tr_ajout_vente.php" method="post" data-confirm-title="Enregistrer cette vente ?" data-confirm-message="La vente sera enregistrée et la quantité vendue sera retirée du stock." data-confirm-label="Enregistrer" data-confirm-type="primary">
            <?= app_csrf_field() ?>
            <div class="mb-3"><label class="form-label" for="produit_id">Produit</label><select name="produit_id" id="produit_id" class="form-select" required><option value="">Sélectionner un produit</option><?php while ($p = mysqli_fetch_assoc($produits)): ?><option value="<?= (int)$p['produit_id'] ?>" data-prix="<?= htmlspecialchars($p['prix_vente'], ENT_QUOTES, 'UTF-8') ?>" data-stock="<?= htmlspecialchars($p['quantite'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?> — stock <?= htmlspecialchars($p['quantite'], ENT_QUOTES, 'UTF-8') ?> — <?= number_format((float)$p['prix_vente'], 0, ',', ' ') ?> FCFA/unité</option><?php endwhile; ?></select></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="quantite">Quantité vendue</label><input type="number" min="1" step="1" class="form-control" name="quantite" id="quantite" placeholder="Ex. 5" required></div>
                <div class="col-md-6"><label class="form-label" for="montant_affiche">Montant estimé</label><input type="text" class="form-control" id="montant_affiche" readonly placeholder="Calculé automatiquement"></div>
            </div>
            <div class="form-actions"><button type="submit" name="submit" class="btn btn-primary" <?= !$hasProducts ? 'disabled' : '' ?>><?= app_icon('check') ?> Confirmer la vente</button><a href="liste_ventes.php" class="btn btn-outline-secondary"><?= app_icon('back') ?> Annuler</a></div>
        </form>
    </section>
</main>
<script src="js/app.js?v=20261118"></script>
<script>
const productSelect = document.getElementById('produit_id');
const quantityInput = document.getElementById('quantite');
const amountDisplay = document.getElementById('montant_affiche');
function updateAmount() {
    const option = productSelect.options[productSelect.selectedIndex];
    const price = Number(option.dataset.prix || 0);
    const stock = Number(option.dataset.stock || 0);
    const quantity = Number(quantityInput.value || 0);
    amountDisplay.value = quantity > stock && stock > 0
        ? 'Stock disponible : ' + stock
        : (price * quantity).toLocaleString('fr-FR') + ' FCFA';
}
productSelect.addEventListener('change', updateAmount);
quantityInput.addEventListener('input', updateAmount);
</script>
</body>
</html>
