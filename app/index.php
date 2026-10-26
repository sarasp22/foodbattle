<?php
$title = 'Découvrir';
require 'includes/header.php';
require 'includes/db.php';

$q = trim($_GET['q'] ?? '');
$cat = $_GET['categorie'] ?? '';
$view = $_GET['view'] ?? '';

$sql = "SELECT d.*, e.name AS restaurant, e.city,
    (SELECT AVG(score) FROM ratings WHERE dish_id=d.id AND status='published') AS average,
    (SELECT COUNT(*) FROM ratings WHERE dish_id=d.id AND status='published') AS reviews_count,
    (SELECT COUNT(*) FROM votes WHERE dish_id=d.id) AS votes
    FROM dishes d JOIN establishments e ON e.id=d.establishment_id WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= ' AND (d.name ILIKE ? OR e.name ILIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if (in_array($cat, ['burger', 'tacos', 'pizza'], true)) {
    $sql .= ' AND d.category = ?';
    $params[] = $cat; 
}
$sql .= ' ORDER BY votes DESC, d.id ASC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$dishes = $stmt->fetchAll();
?>

<?php if ($view !== 'plats'): ?>
<section class="intro">
    <div class="intro-content">
        <h1>Les meilleurs <span class="highlight">plats</span><br> s’affrontent&nbsp;!</h1>
        <p>Découvre des spécialités culinaires, vote pour tes préférées et partage ton avis.</p>
        <form class="search-bar" method="get" action="/">
            <span aria-hidden="true">🔍</span>
            <input name="q" value="<?= h($q) ?>" placeholder="Rechercher un plat, un restaurant…" aria-label="Rechercher">
            <?php if ($cat !== ''): ?><input type="hidden" name="categorie" value="<?= h($cat) ?>"><?php endif; ?>
        </form>
        <div class="category-pills" aria-label="Filtrer par catégorie">
            <a class="category-pill <?= $cat === '' ? 'active' : '' ?>" href="/">👑 Tous</a>
            <a class="category-pill <?= $cat === 'burger' ? 'active' : '' ?>" href="/?categorie=burger">🍔 Burgers</a>
            <a class="category-pill <?= $cat === 'tacos' ? 'active' : '' ?>" href="/?categorie=tacos">🌮 Tacos</a>
            <a class="category-pill <?= $cat === 'pizza' ? 'active' : '' ?>" href="/?categorie=pizza">🍕 Pizzas</a>
        </div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <img class="mascot-logo" src="/assets/images/logo.png" alt="Mascotte FoodBattle">
    </div>
</section>
<?php endif; ?>

<section class="dish-section" id="tous-les-plats">
    <?php if ($view === 'plats'): ?>
        <div class="section-heading">
            <h2><span class="heading-icon">🍴</span> Tous les plats</h2>
        </div>
        <form class="search-bar full-width" method="get" action="/">
            <input type="hidden" name="view" value="plats">
            <span aria-hidden="true">🔍</span>
            <input name="q" value="<?= h($q) ?>" placeholder="Rechercher un plat...">
        </form>
        <div class="category-pills" style="margin-bottom: 24px;">
            <a class="category-pill <?= $cat === '' ? 'active' : '' ?>" href="/?view=plats">👑 Tous</a>
            <a class="category-pill <?= $cat === 'burger' ? 'active' : '' ?>" href="/?view=plats&categorie=burger">🍔 Burgers</a>
            <a class="category-pill <?= $cat === 'tacos' ? 'active' : '' ?>" href="/?view=plats&categorie=tacos">🌮 Tacos</a>
            <a class="category-pill <?= $cat === 'pizza' ? 'active' : '' ?>" href="/?view=plats&categorie=pizza">🍕 Pizzas</a>
        </div>
    <?php else: ?>
        <div class="section-heading">
            <h2><span class="heading-icon">👑</span> Top 10 des meilleurs plats</h2>
            <a href="/classement.php" class="text-link">Voir le classement →</a>
        </div>
    <?php endif; ?>

    <div class="cards <?= $view === 'plats' ? 'grid-3' : '' ?>">
        <?php foreach ($dishes as $d): ?>
            <article class="dish-card">
                <a class="dish-image-link" href="/plat.php?id=<?= $d['id'] ?>">
                    <img src="<?= h(image_for($d['category'])) ?>" alt="<?= h($d['name']) ?>" onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'">
                    <span class="image-heart" aria-hidden="true">♥</span>
                </a>
                <div class="cardbody">
                    <h3><a href="/plat.php?id=<?= $d['id'] ?>"><?= h($d['name']) ?></a></h3>
                    <p class="restaurant-name"><?= h($d['restaurant']) ?></p>
                    <div class="dish-meta">
                        <span class="rating"><span class="star">★</span> <?= $d['average'] ? number_format((float)$d['average'], 1, '.', '') : '—' ?></span>
                        <span class="review-count">(<?= (int)$d['reviews_count'] ?>)</span>
                    </div>
                    <a class="vote-badge" href="/plat.php?id=<?= $d['id'] ?>">👍 <?= (int)$d['votes'] ?> votes</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (!$dishes): ?>
        <p class="empty-state">Aucun résultat trouvé.</p>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>
