<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Modifier un établissement';
$hide_site_header = true;
require 'includes/header.php';
require 'includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$s = $db->prepare('SELECT * FROM establishments WHERE id = ?');
$s->execute([$id]);
$e = $s->fetch();

if (!$e) {
    http_response_code(404);
    echo '<h1>Établissement introuvable</h1>';
    require 'includes/footer.php';
    exit;
}

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
        $s = $db->prepare('UPDATE establishments SET name = ?, city = ?, address = ? WHERE id = ?');
        $s->execute([$name, $city, $address, $id]);
        $saved = true;
        $e['name'] = $name;
        $e['city'] = $city;
        $e['address'] = $address;
    } else {
        $error = "Vérifie le nom, la ville et l'adresse.";
    }
}
?>

<div class="admin-layout">
    <?php $admin_active = 'etablissements'; require 'includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
          <h1>Modifier un établissement</h1>
          <a class="button btn-back" href="/etablissements.php">← Retour aux établissements</a>
        </div>

        <?php if (isset($saved)): ?><p class="message">Modifications enregistrées.</p><?php endif ?>
        <?php if (isset($error)): ?><p class="alert-error" role="alert"><?= h($error) ?></p><?php endif ?>

        <div class="admin-form-card">
            <form method="post" class="reviewform">
                <input type="hidden" name="token" value="<?= csrf() ?>">
                <label>Nom
                    <input name="name" value="<?= h($e['name']) ?>" maxlength="120" required>
                </label>
                <label>Ville
                    <input name="city" value="<?= h($e['city']) ?>" maxlength="80" required>
                </label>
                <label>Adresse
                    <input name="address" value="<?= h($e['address']) ?>" required>
                </label>
                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
