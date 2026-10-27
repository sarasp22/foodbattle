<?php
require_once __DIR__ . '/includes/auth.php';
$title = 'Établissements';
$hide_site_header = true;
require 'includes/header.php';
require 'includes/db.php';

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
            <h1>Établissements</h1>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Adresse</th>
                    <th>Plats</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $e): ?>
                <tr>
                    <td><strong><?= h($e['name']) ?></strong></td>
                    <td><?= h($e['city']) ?></td>
                    <td><?= h($e['address']) ?></td>
                    <td><?= (int) $e['nb_plats'] ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
