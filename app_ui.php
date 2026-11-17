<?php
function app_icon(string $name): string {
    $icons = [
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-6h-4v6H4a1 1 0 0 1-1-1z"/>',
        'products' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5M12 8v5"/>',
        'categories' => '<rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/>',
        'sales' => '<path d="M3 3v18h18"/><path d="m7 14 4-4 4 3 6-7"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="m16 4 4 4L9 19l-5 1 1-5L16 4Z"/><path d="m14 6 4 4"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/>',
        'back' => '<path d="m15 18-6-6 6-6"/><path d="M9 12h12"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'warning' => '<path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 4h.01"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
    ];
    if (!isset($icons[$name])) {
        throw new InvalidArgumentException('Icône inconnue.');
    }
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
}

function app_csrf_token(): string {
    if (empty($_SESSION['app_csrf'])) {
        $_SESSION['app_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['app_csrf'];
}

function app_csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function app_verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !hash_equals($_SESSION['app_csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Session expirée ou requête invalide. Rechargez la page et recommencez.');
    }
}

function app_navigation(string $active = ''): void {
    $role = strtolower($_SESSION['user_role'] ?? '');
    $dashboard = $role === 'administrateur' ? 'dashboard_administrateur.php' : 'dashboard_gestionnaire.php';
    $links = [
        'dashboard' => ['Accueil', $dashboard, 'home'],
        'products' => ['Produits', 'index.php', 'products'],
        'categories' => ['Catégories', 'liste_categories.php', 'categories'],
        'sales' => ['Ventes', 'liste_ventes.php', 'sales'],
    ];
    echo '<header class="app-header"><nav class="app-nav" aria-label="Navigation principale">';
    echo '<a class="app-brand" href="' . htmlspecialchars($dashboard, ENT_QUOTES, 'UTF-8') . '"><span class="brand-mark">' . app_icon('products') . '</span><span>Gestion<span class="brand-accent">Stock</span></span></a>';
    echo '<div class="app-nav-links">';
    foreach ($links as $key => $link) {
        echo '<a class="app-nav-link' . ($active === $key ? ' is-active' : '') . '" href="' . $link[1] . '">' . app_icon($link[2]) . '<span>' . $link[0] . '</span></a>';
    }
    echo '</div><div class="app-user"><span class="user-name">' . htmlspecialchars($_SESSION['user_nom'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>';
    echo '<form method="post" action="logout.php" class="logout-form" data-confirm-title="Se déconnecter ?" data-confirm-message="Votre session actuelle sera fermée. Vous devrez vous reconnecter pour accéder à nouveau à l’application." data-confirm-label="Se déconnecter" data-confirm-type="logout">' . app_csrf_field() . '<button type="submit" class="app-nav-link logout-button">' . app_icon('logout') . '<span>Quitter</span></button></form>';
    echo '</div></nav></header>';
    echo '<div class="app-modal-backdrop" id="app-confirm-modal" hidden>
        <section class="app-modal" role="dialog" aria-modal="true" aria-labelledby="app-confirm-title">
            <h2 id="app-confirm-title">Confirmer cette action ?</h2>
            <p id="app-confirm-message">Confirmez-vous cette opération ?</p>
            <div class="app-modal-actions">
                <button type="button" class="btn btn-outline-secondary" id="app-confirm-cancel">Annuler</button>
                <button type="button" class="btn btn-danger" id="app-confirm-submit">Confirmer</button>
            </div>
        </section>
    </div>';

    $message = '';
    if (isset($_GET['logout'])) {
        $message = 'Vous avez été déconnecté avec succès.';
    } elseif (($_GET['success'] ?? '') === 'updated') {
        $message = 'Les modifications ont été enregistrées avec succès.';
    } elseif (($_GET['success'] ?? '') === '1') {
        $message = 'L\'opération a été effectuée avec succès.';
    } elseif (($_GET['deleted'] ?? '') === '1') {
        $message = 'La suppression a été effectuée avec succès.';
    } elseif (($_GET['success'] ?? '') === 'sale') {
        $message = 'La vente a été enregistrée et le stock a été mis à jour.';
    }
    if ($message !== '') {
        echo '<div class="app-toast is-visible" role="status">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
    }
}
