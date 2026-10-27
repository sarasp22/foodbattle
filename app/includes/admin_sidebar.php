<?php
// Barre latérale commune à toutes les pages d'administration.
// La page qui l'inclut définit d'abord $admin_active : 'plats', 'etablissements' ou 'avis'.
$admin_active = $admin_active ?? '';
$admin_links = [
    'plats'          => ['/dashboard.php',      '🍔 Plats'],
    'etablissements' => ['/etablissements.php', '🏪 Établissements'],
    'avis'           => ['/moderation.php',     '💬 Avis'],
];
?>
<aside class="admin-sidebar">
    <a class="logo" href="/dashboard.php">
        <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'">
        <div>Food<span>Battle</span></div>
    </a>
    <p class="admin-nav-title">Tableau de bord</p>
    <nav class="admin-nav" aria-label="Administration">
        <?php foreach ($admin_links as $key => [$href, $label]): ?>
            <a href="<?= $href ?>"<?= $admin_active === $key ? ' class="active" aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach ?>
    </nav>
    <div class="admin-sidebar-footer">
        <a href="/">🏠 Retour au site</a>
        <a href="/logout.php">🚪 Déconnexion</a>
    </div>
</aside>
