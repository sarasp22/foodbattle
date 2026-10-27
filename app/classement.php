<?php
$title = 'Classement';
require 'includes/header.php';
require 'includes/db.php';

$orders = ['votes' => 'votes DESC, d.id', 'note' => 'average DESC NULLS LAST, d.id'];
$tri = $_GET['tri'] ?? 'votes';
if (!isset($orders[$tri])) { $tri = 'votes'; }

$rows = $db->query('
    SELECT d.id, d.name, d.category, d.image_url, e.name AS restaurant,
           (SELECT COUNT(*) FROM votes WHERE dish_id = d.id) AS votes,
           (SELECT AVG(score) FROM ratings WHERE dish_id = d.id AND status = \'published\') AS average
    FROM dishes d
    JOIN establishments e ON e.id = d.establishment_id
    ORDER BY '.$orders[$tri].'
    LIMIT 10
')->fetchAll();
?>

<div class="ranking-wrapper">
    <div class="section-heading" style="margin-bottom: 4px;">
        <h2><span class="heading-icon">👑</span> Classement des 10 meilleurs plats</h2>
    </div>

    <div class="toggle-tabs">
        <a class="toggle-tab <?= $tri === 'votes' ? 'active' : '' ?>" href="/classement.php?tri=votes">Par nombre de votes</a>
        <a class="toggle-tab <?= $tri === 'note' ? 'active' : '' ?>" href="/classement.php?tri=note">Par note moyenne</a>
    </div>

    <div class="ranking-list">
        <?php foreach ($rows as $i => $d): ?>
            <div class="ranking-item">
                <span class="rank-num"><?= $i + 1 ?></span>
                <img class="rank-thumb" src="<?= h(image_for($d['category'])) ?>" alt="<?= h($d['name']) ?>">
                <div class="rank-info">
                    <h3><a href="/plat.php?id=<?= $d['id'] ?>"><?= h($d['name']) ?></a></h3>
                    <p><?= h($d['restaurant']) ?></p>
                </div>
                <div class="rank-votes"><?= (int)$d['votes'] ?> votes</div>
                <div class="rank-score">
                    <span class="star">★</span> <?= $d['average'] !== null ? number_format((float)$d['average'], 1, '.', '') : '—' ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
