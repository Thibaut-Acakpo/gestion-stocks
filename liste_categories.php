<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';

if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}
$categories = mysqli_query($connexion,
    "SELECT c.*, COUNT(p.produit_id) AS nombre_produits
     FROM categories c LEFT JOIN produits p ON p.categorie_id = c.categorie_id
     GROUP BY c.categorie_id, c.nom, c.description ORDER BY c.nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catégories | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('categories'); ?>
<main class="page-wrap">
    <div class="page-heading">
        <div><h1 class="page-title-icon"><?= app_icon('categories') ?> Catégories</h1><p>Organisez les produits de votre catalogue.</p></div>
        <div class="d-flex gap-2 flex-wrap"><button type="button" id="exportCategories" class="btn btn-outline-primary">Exporter CSV</button><a href="ajout_categorie.php" class="btn btn-primary"><?= app_icon('plus') ?> Nouvelle catégorie</a></div>
    </div>
    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">La catégorie a été supprimée.</div><?php endif; ?>
    <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= ($_GET['error'] ?? '') === 'fk' ? 'Cette catégorie ne peut pas être supprimée car elle contient des produits.' : 'La suppression a échoué. Vérifiez que cette catégorie n’est pas utilisée.' ?></div><?php endif; ?>
    <?php if ($categories && mysqli_num_rows($categories) > 0): ?>
        <div class="panel table-panel"><div class="table-responsive">
            <div class="search-control p-3 pb-0"><?= app_icon('search') ?><input type="search" id="searchCategory" class="form-control" placeholder="Rechercher une catégorie..." aria-label="Rechercher une catégorie"></div>
            <table class="table table-hover align-middle">
                <thead><tr><th>#</th><th>Nom</th><th>Description</th><th>Produits</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td class="text-secondary"><?= (int)$cat['categorie_id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($cat['description'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge text-bg-primary"><?= (int)$cat['nombre_produits'] ?></span></td>
                        <td><div class="table-actions">
                            <a href="modifier_categorie.php?id=<?= (int)$cat['categorie_id'] ?>" class="btn btn-sm btn-outline-primary"><?= app_icon('edit') ?> Modifier</a>
                            <form method="post" action="supprimer_categorie.php" data-confirm-title="Supprimer cette catégorie ?" data-confirm-message="Cette catégorie sera retirée définitivement si aucun produit ne lui est lié." data-confirm-label="Supprimer" data-confirm-type="danger">
                                <?= app_csrf_field() ?><input type="hidden" name="id" value="<?= (int)$cat['categorie_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><?= app_icon('trash') ?> Supprimer</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div></div>
    <?php else: ?>
        <div class="panel empty-state"><?= app_icon('categories') ?><p>Aucune catégorie n'a encore été créée.</p><a href="ajout_categorie.php" class="btn btn-primary"><?= app_icon('plus') ?> Créer une catégorie</a></div>
    <?php endif; ?>
</main>
<script src="js/app.js?v=20261118"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('searchCategory');
    const rows = Array.from(document.querySelectorAll('.table tbody tr'));
    search.addEventListener('input', function () {
        const query = search.value.toLocaleLowerCase();
        rows.forEach(function (row) { row.hidden = !row.textContent.toLocaleLowerCase().includes(query); });
    });
    document.getElementById('exportCategories').addEventListener('click', function () {
        const lines = [['Nom','Description','Nombre de produits']];
        rows.filter(function (row) { return !row.hidden; }).forEach(function (row) {
            lines.push([row.children[1].textContent,row.children[2].textContent,row.children[3].textContent].map(function(v){return '"' + v.trim().replace(/"/g,'""') + '"';}));
        });
        const blob = new Blob(['\ufeff'+lines.map(function(l){return l.join(',');}).join('\n')],{type:'text/csv;charset=utf-8'});
        const link=document.createElement('a'); link.href=URL.createObjectURL(blob); link.download='categories.csv'; link.click(); URL.revokeObjectURL(link.href);
    });
});
</script>
</body>
</html>
