<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Modifier un plat';
$hide_site_header = true;
require 'includes/header.php';
require 'includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$s = $db->prepare('SELECT * FROM dishes WHERE id = ?');
$s->execute([$id]);
$d = $s->fetch();

if (!$d) {
    http_response_code(404);
    echo '<h1>Plat introuvable</h1>';
    require 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post();
    $name = trim($_POST['name'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $establishment_id = filter_input(INPUT_POST, 'establishment_id', FILTER_VALIDATE_INT) ?: (int) $d['establishment_id'];

    if ($name !== '' && mb_strlen($name, 'UTF-8') <= 120 && $price !== false && $price >= 0) {
        $s = $db->prepare('UPDATE dishes SET name = ?, price = ?, establishment_id = ? WHERE id = ?');
        $s->execute([$name, $price, $establishment_id, $id]);
        $saved = true;
        $d['name'] = $name;
        $d['price'] = $price;
        $d['establishment_id'] = $establishment_id;
    } else {
        $error = 'Vérifie le nom et le prix.';
    }
}

$establishments = $db->query('SELECT id, name, city FROM establishments ORDER BY name')->fetchAll();
?>

<div class="admin-layout">
    <?php $admin_active = 'plats'; require 'includes/admin_sidebar.php'; ?>

    <div class="admin-content">
      <div class="admin-header">
        <h1>Modifier un plat</h1>
        <a class="button btn-back" href="/dashboard.php">← Retour aux plats</a>
      </div>

        <?php if (isset($saved)): ?><p class="message">Modifications enregistrées.</p><?php endif ?>
        <?php if (isset($error)): ?><p class="alert-error" role="alert"><?= h($error) ?></p><?php endif ?>

        <div class="admin-form-card">
            <form method="post" class="reviewform">
                <input type="hidden" name="token" value="<?= csrf() ?>">
                <label>Nom
                    <input name="name" value="<?= h($d['name']) ?>" maxlength="120" required>
                </label>
                <label>Établissement
                    <select name="establishment_id" required>
                        <?php foreach ($establishments as $est): ?>
                            <option value="<?= (int) $est['id'] ?>" <?= (int) $est['id'] === (int) $d['establishment_id'] ? 'selected' : '' ?>>
                                <?= h($est['name']) ?> (<?= h($est['city']) ?>)
                            </option>
                        <?php endforeach ?>
                    </select>
                </label>
                <label>Prix (€)
                    <input name="price" type="number" step="0.01" min="0" value="<?= h($d['price']) ?>" required>
                </label>
                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
