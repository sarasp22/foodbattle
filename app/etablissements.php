<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Établissements';
$hide_site_header = true;
require 'includes/header.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post();
    $name = trim($_POST['name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (
        $name !== '' && mb_strlen($name, 'UTF-8') <= 120 &&
        $city !== '' && mb_strlen($city, 'UTF-8') <= 80 &&
        $address !== ''
    ) {
        $s = $db->prepare('INSERT INTO establishments(name, city, address) VALUES(?, ?, ?)');
        $s->execute([$name, $city, $address]);
        header('Location: /etablissements.php?added=1');
        exit;
    } else {
        $error = "Vérifie les champs de l'établissement.";
    }
}

$restaurants = $db->query('
    SELECT e.*, COUNT(d.id) AS nb_plats
    FROM establishments e
    LEFT JOIN dishes d ON d.establishment_id = e.id
    GROUP BY e.id
    ORDER BY e.name
')->fetchAll();
?>

<div class="admin-layout">
    <?php $admin_active = 'etablissements'; require 'includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Gestion des établissements</h1>
            <button type="button" onclick="document.getElementById('add-establishment-box').open = !document.getElementById('add-establishment-box').open">+ Ajouter un établissement</button>
        </div>

        <?php if (isset($error)): ?><p class="alert-error" role="alert"><?= h($error) ?></p><?php endif ?>
        <?php if (isset($_GET['added'])): ?><p class="message">L'établissement a été ajouté.</p><?php endif ?>
        <?php if (isset($_GET['deleted'])): ?><p class="message">L'établissement a été supprimé.</p><?php endif ?>

        <details id="add-establishment-box" class="admin-box">
            <summary>Nouvel établissement</summary>
            <form method="post" style="max-width: 520px; margin-top: 14px;">
                <input type="hidden" name="token" value="<?= csrf() ?>">
                <label>Nom <input name="name" maxlength="120" required></label>
                <label>Ville <input name="city" maxlength="80" required></label>
                <label>Adresse <input name="address" required></label>
                <button type="submit" style="margin-top: 12px;">Enregistrer l'établissement</button>
            </form>
        </details>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Adresse</th>
                    <th>Plats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $e): ?>
                <tr>
                    <td><strong><?= h($e['name']) ?></strong></td>
                    <td><?= h($e['city']) ?></td>
                    <td><?= h($e['address']) ?></td>
                    <td><?= (int) $e['nb_plats'] ?></td>
                    <td>
                        <div class="action-icons">
                            <a class="icon-action" href="/modifier-etablissement.php?id=<?= (int) $e['id'] ?>" title="Modifier">✏️</a>
                            <form method="post" action="/supprimer-etablissement.php" style="display:inline" data-confirm="Supprimer « <?= h($e['name']) ?> » ?">
                                <input type="hidden" name="token" value="<?= csrf() ?>">
                                <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
                                <button class="icon-action" title="Supprimer">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<script src="/assets/app.js"></script>
</body>
</html>
