<?php
$title = 'Classement';
require 'includes/header.php';
require 'includes/db.php';

$orders = ['votes' => 'votes DESC, d.id', 'note' => 'average DESC NULLS LAST, d.id'];
$tri = $_GET['tri'] ?? 'votes';
if (!isset($orders[$tri])) { $tri = 'votes'; }

$rows = $db->query('
    SELECT d.id, d.name, d.category, e.name AS restaurant,
           (SELECT COUNT(*) FROM votes WHERE dish_id = d.id) AS votes,
           (SELECT AVG(score) FROM ratings WHERE dish_id = d.id) AS average
    FROM dishes d
    JOIN establishments e ON e.id = d.establishment_id
    ORDER BY '.$orders[$tri].'
    LIMIT 10
')->fetchAll();
?>

<h1>Le top street food</h1>
<p>Les 10 plats <?= $tri === 'note' ? 'les mieux notés' : 'qui ont reçu le plus de votes' ?>. À toi de faire bouger le classement !</p>
<p>Trier par : <?= $tri === 'votes' ? '<strong>votes</strong>' : '<a href="/classement.php?tri=votes">votes</a>' ?> · <?= $tri === 'note' ? '<strong>note</strong>' : '<a href="/classement.php?tri=note">note</a>' ?></p>

<table>
    <thead>
        <tr>
            <th>Rang</th>
            <th>Plat</th>
            <th>Établissement</th>
            <th>Votes</th>
            <th>Note /5</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $i => $d): ?>
            <tr>
                <td>#<?= $i + 1 ?></td>
                <td><a href="/plat.php?id=<?= $d['id'] ?>"><?= h($d['name']) ?></a></td>
                <td><?= h($d['restaurant']) ?></td>
                <td><?= $d['votes'] ?></td>
                <td><?= number_format((float)$d['average'], 1, ',', '') ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php
require 'includes/footer.php';
?>
