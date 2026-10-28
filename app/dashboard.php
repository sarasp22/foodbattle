<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Gestion des plats';
$hide_site_header = true;
require 'includes/header.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post();
    $name = trim($_POST['name'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $establishment_id = filter_input(INPUT_POST, 'establishment_id', FILTER_VALIDATE_INT) ?: 1;

    if ($name !== '' && mb_strlen($name, 'UTF-8') <= 120 && $price !== false && $price >= 0 && in_array($category, ['burger', 'tacos', 'pizza'], true)) {
        $upload_error = null;
        $image_url = upload_to_cloudinary($_FILES['image'] ?? [], $upload_error);

        if ($upload_error !== null) {
            $error = $upload_error;
        } else {
            $s = $db->prepare('INSERT INTO dishes(establishment_id, name, category, description, price, image_url) VALUES(?, ?, ?, ?, ?, ?)');
            $s->execute([$establishment_id, $name, $category, $description, $price, $image_url]);
            header('Location: /dashboard.php?added=1');
            exit;
        }
    } else {
        $error = 'Vérifie les champs du plat.';
    }
}

$items = $db->query('SELECT * FROM dishes ORDER BY id DESC')->fetchAll();
?>

<div class="admin-layout">

  <?php $admin_active = 'plats'; require 'includes/admin_sidebar.php'; ?>

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
            <form method="post" enctype="multipart/form-data" style="max-width: 520px; margin-top: 14px;">
                <input type="hidden" name="token" value="<?= csrf() ?>">
                <label>Nom <input name="name" maxlength="120" required></label>
                <label>Catégorie
                    <select name="category">
                        <option value="burger">Burger</option>
                        <option value="tacos">Tacos</option>
                        <option value="pizza">Pizza</option>
                    </select>
                </label>
                <label>Prix (€) <input type="number" name="price" min="0" step="0.01" required></label>
                <label>Photo (JPG, PNG, WebP - max 5 Mo)
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                </label>
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
                            <img src="<?= h(image_for($d['category'], $d['image_url'] ?? null)) ?>" alt="">
                            <div>
                                <strong><?= h($d['name']) ?></strong>
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
    </div>
</div>
<script src="/assets/app.js"></script>
</body>
</html>
