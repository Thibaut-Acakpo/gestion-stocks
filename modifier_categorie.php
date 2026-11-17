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
    header('Location: liste_categories.php');
    exit;
}
$stmt = mysqli_prepare($connexion, "SELECT categorie_id, nom, description FROM categories WHERE categorie_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$cat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$cat) {
    header('Location: liste_categories.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier la catégorie | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('categories'); ?>
<main class="page-wrap">
    <a class="back-link" href="liste_categories.php"><?= app_icon('back') ?> Retour aux catégories</a>
    <div class="page-heading"><div><h1 class="page-title-icon"><?= app_icon('edit') ?> Modifier la catégorie</h1><p>Mettez à jour le nom ou la description.</p></div></div>
    <section class="panel form-panel">
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger">La modification a échoué. Vérifiez les informations et réessayez.</div><?php endif; ?>
        <form action="tr_modifier_categorie.php" method="post" data-confirm-title="Modifier cette catégorie ?" data-confirm-message="Les nouvelles informations remplaceront celles enregistrées actuellement." data-confirm-label="Modifier" data-confirm-type="primary">
            <?= app_csrf_field() ?><input type="hidden" name="categorie_id" value="<?= (int)$cat['categorie_id'] ?>">
            <div class="mb-3"><label class="form-label" for="nom">Nom de la catégorie</label><input id="nom" type="text" class="form-control" name="nom" value="<?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required></div>
            <div class="mb-3"><label class="form-label" for="description">Description</label><textarea id="description" class="form-control" name="description" maxlength="255"><?= htmlspecialchars($cat['description'], ENT_QUOTES, 'UTF-8') ?></textarea></div>
            <div class="form-actions"><button type="submit" name="submit" class="btn btn-primary"><?= app_icon('check') ?> Enregistrer les modifications</button><a href="liste_categories.php" class="btn btn-outline-secondary"><?= app_icon('back') ?> Annuler</a></div>
        </form>
    </section>
</main>
<script src="js/app.js?v=20261118"></script>
</body>
</html>
