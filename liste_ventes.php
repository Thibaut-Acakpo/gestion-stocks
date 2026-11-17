<?php
session_start();
require __DIR__ . '/app_ui.php';
require __DIR__ . '/connexion.php';

if (!isset($_SESSION['user_role'])) {
    header('Location: connecter.php');
    exit;
}
$ventes = mysqli_query($connexion,
    "SELECT v.vente_id, p.nom AS nom_produit, v.date_vente, v.quantite, v.montant_total
     FROM ventes v JOIN produits p ON v.produit_id = p.produit_id ORDER BY v.vente_id DESC");
$ca_total = mysqli_fetch_row(mysqli_query($connexion, "SELECT COALESCE(SUM(montant_total), 0) FROM ventes"))[0];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ventes | GestionStock</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css?v=20261118">
</head>
<body>
<?php app_navigation('sales'); ?>
<main class="page-wrap">
    <div class="page-heading">
        <div><h1 class="page-title-icon"><?= app_icon('sales') ?> Historique des ventes</h1><p>Retrouvez les transactions enregistrées.</p></div>
        <div class="d-flex gap-2 flex-wrap"><button type="button" id="exportSales" class="btn btn-outline-primary">Exporter CSV</button><a href="ajout_vente.php" class="btn btn-primary"><?= app_icon('plus') ?> Nouvelle vente</a></div>
    </div>
    <div class="panel mb-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div><span class="stat-label">Chiffre d'affaires total</span><div class="stat-value"><?= number_format((float)$ca_total, 0, ',', ' ') ?> <small>FCFA</small></div></div>
        <span class="stat-icon"><?= app_icon('sales') ?></span>
    </div>
    <?php if ($ventes && mysqli_num_rows($ventes) > 0): ?>
        <div class="search-row">
            <div class="search-control"><?= app_icon('search') ?><input type="search" id="searchSale" class="form-control" placeholder="Rechercher une vente..." aria-label="Rechercher une vente"></div>
            <input type="date" id="dateSale" class="form-control" aria-label="Filtrer par date">
        </div>
        <div class="panel table-panel"><div class="table-responsive">
            <table class="table table-hover align-middle" id="salesTable">
                <thead><tr><th>N°</th><th>Produit</th><th>Date</th><th>Quantité</th><th>Montant total</th></tr></thead>
                <tbody>
                <?php while ($v = mysqli_fetch_assoc($ventes)): ?>
                    <tr><td class="text-secondary">#<?= (int)$v['vente_id'] ?></td><td class="fw-semibold"><?= htmlspecialchars($v['nom_produit'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($v['date_vente'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($v['quantite'], ENT_QUOTES, 'UTF-8') ?></td><td class="fw-semibold"><?= number_format((float)$v['montant_total'], 0, ',', ' ') ?> FCFA</td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div></div>
    <?php else: ?>
        <div class="panel empty-state"><?= app_icon('sales') ?><p>Aucune vente n'a encore été enregistrée.</p><a href="ajout_vente.php" class="btn btn-primary"><?= app_icon('plus') ?> Enregistrer une vente</a></div>
    <?php endif; ?>
</main>
<script src="js/app.js?v=20261118"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('searchSale');
    const dateFilter = document.getElementById('dateSale');
    const rows = Array.from(document.querySelectorAll('#salesTable tbody tr'));
    function filterSales() {
        const query = search.value.toLocaleLowerCase();
        const date = dateFilter.value;
        rows.forEach(function (row) {
            const matchesText = row.textContent.toLocaleLowerCase().includes(query);
            const matchesDate = !date || row.children[2].textContent.trim() === date;
            row.hidden = !(matchesText && matchesDate);
        });
    }
    search.addEventListener('input', filterSales);
    dateFilter.addEventListener('change', filterSales);
    document.getElementById('exportSales').addEventListener('click', function () {
        const lines = [['Numero','Produit','Date','Quantite','Montant']];
        rows.filter(function (row) { return !row.hidden; }).forEach(function (row) {
            lines.push(Array.from(row.children).map(function (cell) {
                return '"' + cell.textContent.trim().replace(/"/g,'""') + '"';
            }));
        });
        const blob = new Blob(['\ufeff'+lines.map(function(l){return l.join(',');}).join('\n')],{type:'text/csv;charset=utf-8'});
        const link=document.createElement('a'); link.href=URL.createObjectURL(blob); link.download='ventes.csv'; link.click(); URL.revokeObjectURL(link.href);
    });
});
</script>
</body>
</html>
