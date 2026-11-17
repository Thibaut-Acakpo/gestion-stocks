<?php
require __DIR__ . '/config.php';

function page_accueil(string $role): string {
    return $role === 'administrateur' ? 'dashboard_administrateur.php' : 'dashboard_gestionnaire.php';
}
if ($u = utilisateur()) { header('Location: ' . page_accueil($u['role'])); exit; }

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $now = time();
    if (($_SESSION['verrou'] ?? 0) > $now) {
        $erreur = 'Trop de tentatives. Réessayez dans ' . ($_SESSION['verrou'] - $now) . ' secondes.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $mdp = (string)($_POST['password'] ?? '');
        $st = $db->prepare('SELECT utilisateur_id, nom, prenoms, mot_de_passe, role, statut FROM utilisateurs WHERE email = ?');
        $st->bind_param('s', $email);
        $st->execute();
        $u = $st->get_result()->fetch_assoc();

        if ($u && password_verify($mdp, $u['mot_de_passe'])) {
            if ($u['statut'] !== 'actif') {
                $erreur = 'Ce compte est désactivé. Contactez un administrateur.';
            } else {
                session_regenerate_id(true);
                unset($_SESSION['essais'], $_SESSION['verrou']);
                $role = strtolower($u['role']);
                $_SESSION['user'] = ['id' => (int)$u['utilisateur_id'], 'nom' => $u['nom'], 'prenoms' => $u['prenoms'], 'role' => $role];
                // Clés historiques : les pages stock existantes continuent de fonctionner
                $_SESSION['user_id'] = (int)$u['utilisateur_id'];
                $_SESSION['user_nom'] = $u['nom'];
                $_SESSION['user_prenoms'] = $u['prenoms'];
                $_SESSION['user_role'] = $role;
                header('Location: ' . page_accueil($role));
                exit;
            }
        } else {
            $_SESSION['essais'] = ($_SESSION['essais'] ?? 0) + 1;
            if ($_SESSION['essais'] >= 5) { $_SESSION['verrou'] = $now + 60; $_SESSION['essais'] = 0; }
            $erreur = 'E-mail ou mot de passe incorrect.';
        }
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion</title>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/app.css?v=20261118">
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--app-bg);color:var(--app-ink);font-family:Inter,"Segoe UI",Arial,sans-serif}
.login-shell{width:100%;min-height:100vh;display:grid;place-items:center;padding:24px}
.box{position:relative;width:min(420px,100%);background:#fff;border:1px solid var(--app-line);border-radius:16px;padding:36px;box-shadow:var(--app-shadow)}
.brand-mark{width:46px;height:46px;display:grid;place-items:center;margin-bottom:22px;border-radius:12px;background:#eef2ff;color:var(--app-primary);font-size:1.35rem;font-weight:800}
h1{font-size:2rem;line-height:1.1;margin:0 0 8px;color:var(--app-ink);letter-spacing:-.03em}
p{color:var(--app-muted);margin:0 0 24px}
label{display:block;color:#344158;font-weight:650;font-size:.92rem;margin-bottom:16px}
input{width:100%;margin-top:7px;padding:11px 13px;border-radius:9px;border:1px solid #dce3ed;background:#fff;color:var(--app-ink);font:inherit;min-height:46px}
input:focus-visible{outline:0;border-color:var(--app-primary);box-shadow:0 0 0 3px rgba(49,87,213,.12)}
button{width:100%;margin-top:8px;padding:12px 16px;border:0;border-radius:9px;background:var(--app-primary);color:#fff;font-weight:650;cursor:pointer;min-height:46px}
button:hover{background:var(--app-primary-dark)}
button:focus-visible{outline:3px solid rgba(49,87,213,.25);outline-offset:2px}
.err{background:#fff1f1;color:#a53a3a;border:1px solid #f3c9c9;padding:10px 14px;border-radius:9px;margin-bottom:16px}
.ok{background:#effaf4;color:#287449;border:1px solid #b9dfc8;padding:10px 14px;border-radius:9px;margin-bottom:16px}
@media(max-width:480px){.login-shell{padding:16px}.box{padding:26px 22px;border-radius:14px}}
</style>
</head>
<body>
<main class="login-shell">
  <div class="box">
    <div class="brand-mark" aria-hidden="true">GS</div>
    <h1>Connexion</h1>
    <p>Accédez à votre espace de gestion.</p>
    <?php if (isset($_GET['logout'])): ?><div class="ok" role="status">Vous avez été déconnecté avec succès.</div><?php endif; ?>
    <?php if ($erreur): ?><div class="err" role="alert"><?= e($erreur) ?></div><?php endif; ?>
    <form method="post" autocomplete="on">
      <?= csrf_champ() ?>
      <label>Adresse e-mail<input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></label>
      <label>Mot de passe<input type="password" name="password" required></label>
      <button type="submit">Se connecter</button>
    </form>
  </div>
</main>
</body>
</html>
