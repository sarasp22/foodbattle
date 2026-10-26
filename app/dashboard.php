<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Gestion des plats';
$hide_site_header = true;
require 'includes/header.php';
require 'includes/db.php';

$restaurants = $db->query('SELECT * FROM establishments ORDER BY id')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post();
    $name = trim($_POST['name'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $eid = filter_input(INPUT_POST, 'establishment_id', FILTER_VALIDATE_INT);
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    if ($name !== '' && strlen($name) <= 120 && $price !== false && $price >= 0 && in_array($category, ['burger', 'tacos', 'pizza'], true) && in_array($eid, array_column($restaurants, 'id'))) {
        $s = $db->prepare('INSERT INTO dishes(establishment_id,name,category,description,price) VALUES(?,?,?,?,?)');
        $s->execute([$eid, $name, $category, $description, $price]);
        header('Location: /dashboard.php?added=1');
        exit;
    } else {
        $error = 'Vérifie les champs du plat.';
    }
}
$items = $db->query('SELECT d.*, e.name AS restaurant FROM dishes d JOIN establishments e ON e.id=d.establishment_id ORDER BY d.id')->fetchAll();
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="logo" href="/">
            <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'">
            <div>Food<span>Battle</span></div>
        </a>
        <nav class="admin-nav">
            <a href="/">📊 Tableau de bord</a>
            <a href="/dashboard.php" class="active">🍔 Plats</a>
            <a href="/moderation.php">💬 Avis</a>
            <a href="#etablissements">🏪 Établissements</a>
        </nav>
        <a class="admin-logout" href="/logout.php">🚪 Déconnexion</a>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Gestion des plats</h1>
            <button type="button" onclick="document.getElementById('add-dish-box').open = !document.getElementById('add-dish-box').open">+ Ajouter un plat</button>
        </div>

        <?php if (isset($error)): ?><p class="alert-error" role="alert"><?= h($error) ?></p><?php endif ?>
        <?php if (isset($_GET['added'])): ?><p class="message">Le plat a été ajouté.</p><?php endif ?>
        <?php if (isset($_GET['deleted'])): ?><p class="message">Le plat a été supprimé.</p><?php endif ?>

        <details id="add-dish-box" class="admin-box">
            <summary>Nouveau plat</summary>
            <form method="post" style="max-width: 520px; margin-top: 14px;">
                <input type="hidden" name="token" value="<?= csrf() ?>">
                <label>Nom <input name="name" maxlength="120" required></label>
                <label>Établissement
                    <select name="establishment_id">
                        <?php foreach ($restaurants as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= h($e['name']) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
                <label>Catégorie
                    <select name="category">
                        <option value="burger">Burger</option>
                        <option value="tacos">Tacos</option>
                        <option value="pizza">Pizza</option>
                    </select>
                </label>
                <label>Prix (€) <input type="number" name="price" min="0" step="0.01" required></label>
                <label>Description <textarea name="description"></textarea></label>
                <button type="submit" style="margin-top: 12px;">Enregistrer le plat</button>
            </form>
        </details>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Plat</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $d): ?>
                <tr>
                    <td>
                        <div class="td-dish">
                            <img src="<?= h(image_for($d['category'])) ?>" alt="">
                            <div>
                                <strong><?= h($d['name']) ?></strong>
                                <span><?= h($d['restaurant']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td style="text-transform: capitalize;"><?= h($d['category']) ?></td>
                    <td><strong><?= euro($d['price']) ?></strong></td>
                    <td>
                        <div class="action-icons">
                            <a class="icon-action" href="/modifier.php?id=<?= $d['id'] ?>" title="Modifier">✏️</a>
                            <form method="post" action="/supprimer.php" style="display:inline" data-confirm="Supprimer « <?= h($d['name']) ?> » ?">
                                <input type="hidden" name="token" value="<?= csrf() ?>">
                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button class="icon-action" title="Supprimer">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>

        <h2 id="etablissements" style="margin-top: 48px; font-size: 20px;">Les établissements</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Adresse</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $e): ?>
                <tr>
                    <td><strong><?= h($e['name']) ?></strong></td>
                    <td><?= h($e['city']) ?></td>
                    <td><?= h($e['address']) ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<script src="/assets/app.js"></script>
</body>
</html>
