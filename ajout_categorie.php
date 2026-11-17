<?php
session_start();
require __DIR__ . '/app_ui.php';
if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouvelle catégorie | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('categories'); ?>
<main class="page-wrap">
    <a class="back-link" href="liste_categories.php"><?= app_icon('back') ?> Retour aux catégories</a>
    <div class="page-heading"><div><h1 class="page-title-icon"><?= app_icon('categories') ?> Créer une catégorie</h1><p>Ajoutez une catégorie pour organiser votre catalogue.</p></div></div>
    <section class="panel form-panel">
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Catégorie créée avec succès.</div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= ($_GET['error'] ?? '') === '2' ? 'Le nom de la catégorie est obligatoire.' : 'Une erreur est survenue. Vérifiez les informations et réessayez.' ?></div><?php endif; ?>
        <form action="tr_ajout_categorie.php" method="post" data-confirm-title="Créer cette catégorie ?" data-confirm-message="Cette catégorie sera ajoutée et pourra recevoir de nouveaux produits." data-confirm-label="Créer" data-confirm-type="primary">
            <?= app_csrf_field() ?>
            <div class="mb-3"><label class="form-label" for="nom">Nom de la catégorie</label><input id="nom" type="text" class="form-control" name="nom" placeholder="Ex. Électroménager" maxlength="255" required></div>
            <div class="mb-3"><label class="form-label" for="description">Description</label><textarea id="description" class="form-control" name="description" placeholder="Décrivez brièvement cette catégorie" maxlength="255" required></textarea></div>
            <div class="form-actions"><button type="submit" class="btn btn-primary" name="submit"><?= app_icon('check') ?> Enregistrer</button><a href="liste_categories.php" class="btn btn-outline-secondary"><?= app_icon('back') ?> Annuler</a></div>
        </form>
    </section>
</main>
<script src="js/app.js?v=20261118"></script>
</body>
</html>
