<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Modération des avis';
$hide_site_header = true;
require 'includes/header.php';
require 'includes/db.php';

$rows = $db->query("
    SELECT r.*, d.name AS dish
    FROM ratings r
    JOIN dishes d ON d.id = r.dish_id
    ORDER BY r.id DESC
")->fetchAll();
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="logo" href="/">
            <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'">
            <div>Food<span>Battle</span></div>
        </a>
        <nav class="admin-nav">
            <a href="/">📊 Tableau de bord</a>
            <a href="/dashboard.php">🍔 Plats</a>
            <a href="/moderation.php" class="active">💬 Avis</a>
        </nav>
        <a class="admin-logout" href="/logout.php">🚪 Déconnexion</a>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Modération des avis</h1>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Plat</th>
                    <th>Pseudo</th>
                    <th>Note</th>
                    <th>Avis</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= h($r['dish']) ?></strong></td>
                    <td><?= h($r['nickname']) ?></td>
                    <td><span style="color:var(--yellow);">★</span> <?= (int)$r['score'] ?>/5</td>
                    <td><?= h($r['comment']) ?></td>
                    <td><?= h($r['status']) ?></td>
                    <td>
                        <?php if ($r['status'] === 'pending'): ?>
                            <form method="post" action="/moderation-action.php" style="display:flex; gap:8px;">
                                <input type="hidden" name="token" value="<?= csrf() ?>">
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" name="status" value="published">Approuver</button>
                                <button type="submit" name="status" value="rejected" style="background:#ececf2;">Refuser</button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
