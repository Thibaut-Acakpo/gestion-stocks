<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';

if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}

$resultat = mysqli_query($connexion, "SELECT p.*, c.nom AS nom_categorie FROM produits p LEFT JOIN categories c ON p.categorie_id = c.categorie_id ORDER BY p.nom");
$res_categories = mysqli_query($connexion, "SELECT nom FROM categories ORDER BY nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produits | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('products'); ?>
<main class="page-wrap">
    <div class="page-heading">
        <div><h1 class="page-title-icon"><?= app_icon('products') ?> Catalogue des produits</h1><p>Consultez et gérez les articles de votre stock.</p></div>
        <div class="d-flex gap-2 flex-wrap"><button type="button" id="exportProducts" class="btn btn-outline-primary">Exporter CSV</button><a href="ajout_produit.php" class="btn btn-primary"><?= app_icon('plus') ?> Nouveau produit</a></div>
    </div>
    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Le produit a été supprimé.</div><?php endif; ?>
    <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= ($_GET['error'] ?? '') === 'sales' ? 'Ce produit ne peut pas être supprimé car il est lié à une ou plusieurs ventes.' : 'La suppression a échoué. Vérifiez que le produit existe.' ?></div><?php endif; ?>
    <div class="search-row">
        <div class="search-control"><?= app_icon('search') ?><input type="search" id="search" class="form-control" placeholder="Rechercher un produit..." aria-label="Rechercher un produit"></div>
        <select id="filtreCategorie" class="form-select" aria-label="Filtrer par catégorie">
            <option value="">Toutes les catégories</option>
            <?php while ($cat = mysqli_fetch_assoc($res_categories)): ?><option value="<?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endwhile; ?>
        </select>
        <select id="filtreStatut" class="form-select" aria-label="Filtrer par état">
            <option value="">Tous les états</option>
            <option value="en stock">En stock</option>
            <option value="faible">Stock faible</option>
            <option value="rupture">Rupture</option>
        </select>
    </div>
    <?php if ($resultat && mysqli_num_rows($resultat) > 0): ?>
        <div class="d-flex justify-content-between align-items-center mb-2"><span id="compteur" class="small text-secondary"></span><span id="noResult" class="small text-danger"></span></div>
        <div class="panel table-panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Produit</th><th>Achat</th><th>Vente</th><th>Quantité</th><th>État</th><th>Catégorie</th><th class="text-center">Actions</th></tr></thead>
                    <tbody id="tableBody">
                    <?php while ($row = mysqli_fetch_assoc($resultat)): ?>
                        <?php $badge = ['en stock' => 'success', 'faible' => 'warning', 'rupture' => 'danger'][$row['statut']] ?? 'secondary'; ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['prix_achat'], ENT_QUOTES, 'UTF-8') ?> FCFA</td>
                            <td><?= htmlspecialchars($row['prix_vente'], ENT_QUOTES, 'UTF-8') ?> FCFA</td>
                            <td><?= htmlspecialchars($row['quantite'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($row['statut'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($row['nom_categorie'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><div class="table-actions">
                                <a href="modifier_produit.php?id=<?= (int)$row['produit_id'] ?>" class="btn btn-sm btn-outline-primary btn-icon" aria-label="Modifier <?= htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8') ?>" title="Modifier"><?= app_icon('edit') ?></a>
                                <form method="post" action="supprimer_produit.php" data-confirm-title="Supprimer ce produit ?" data-confirm-message="Ce produit sera retiré définitivement du catalogue." data-confirm-label="Supprimer" data-confirm-type="danger">
                                    <?= app_csrf_field() ?><input type="hidden" name="id" value="<?= (int)$row['produit_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Supprimer <?= htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8') ?>" title="Supprimer"><?= app_icon('trash') ?></button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="panel empty-state"><?= app_icon('products') ?><p>Aucun produit dans le catalogue.</p><a href="ajout_produit.php" class="btn btn-primary"><?= app_icon('plus') ?> Ajouter le premier produit</a></div>
    <?php endif; ?>
</main>
<script src="js/app.js?v=20261118"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('search');
    const category = document.getElementById('filtreCategorie');
    const status = document.getElementById('filtreStatut');
    const rows = document.querySelectorAll('#tableBody tr');
    const counter = document.getElementById('compteur');
    const noResult = document.getElementById('noResult');
    function filterRows() {
        const query = search.value.toLocaleLowerCase();
        const selectedCategory = category.value.toLocaleLowerCase();
        const selectedStatus = status.value.toLocaleLowerCase();
        let visible = 0;
        rows.forEach(function (row) {
            const matches = row.children[0].textContent.toLocaleLowerCase().includes(query)
                && (!selectedCategory || row.children[5].textContent.toLocaleLowerCase().trim() === selectedCategory)
                && (!selectedStatus || row.children[4].textContent.toLocaleLowerCase().trim() === selectedStatus);
            row.hidden = !matches;
            if (matches) visible++;
        });
        counter.textContent = visible + ' produit' + (visible > 1 ? 's' : '') + ' affiché' + (visible > 1 ? 's' : '');
        noResult.textContent = visible === 0 ? 'Aucun résultat pour ces filtres.' : '';
    }
    search.addEventListener('input', filterRows);
    category.addEventListener('change', filterRows);
    status.addEventListener('change', filterRows);
    document.getElementById('exportProducts').addEventListener('click', function () {
        const visibleRows = Array.from(rows).filter(function (row) { return !row.hidden; });
        const lines = [['Produit','Prix achat','Prix vente','Quantite','Etat','Categorie']];
        visibleRows.forEach(function (row) {
            lines.push(Array.from(row.children).slice(0,6).map(function (cell) { return '"' + cell.textContent.trim().replace(/"/g,'""') + '"'; }));
        });
        const blob = new Blob(['\ufeff' + lines.map(function (line) { return line.join(','); }).join('\n')], {type:'text/csv;charset=utf-8'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'produits.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });
    filterRows();
});
</script>
</body>
</html>
