<?php
$title = 'Découvrir';
require 'includes/header.php';
require 'includes/db.php';

$q = trim($_GET['q'] ?? '');
$cat = $_GET['categorie'] ?? '';
$view = $_GET['view'] ?? '';

// Récupération du Top 3 pour le podium (uniquement si pas de recherche active)
$podium_dishes = [];
if ($q === '' && $cat === '' && $view !== 'plats') {
    $podium_stmt = $db->prepare("
        SELECT d.*, e.name AS restaurant, e.city,
        (SELECT AVG(score) FROM ratings WHERE dish_id=d.id AND status='published') AS average,
        (SELECT COUNT(*) FROM ratings WHERE dish_id=d.id AND status='published') AS reviews_count,
        (SELECT COUNT(*) FROM votes WHERE dish_id=d.id) AS votes,
        CASE WHEN EXISTS (
            SELECT 1
            FROM votes
            WHERE votes.dish_id = d.id
            AND votes.user_id = ?
        ) THEN 1 ELSE 0 END AS has_voted
        FROM dishes d
        JOIN establishments e ON e.id=d.establishment_id
        ORDER BY votes DESC, d.id ASC
        LIMIT 3
    ");
    $podium_stmt->execute([(int) ($_SESSION['user_id'] ?? 0)]);
    $podium_dishes = $podium_stmt->fetchAll();
}

// Requête principale pour la grille des plats
$sql = "SELECT d.*, e.name AS restaurant, e.city,
    (SELECT AVG(score) FROM ratings WHERE dish_id=d.id AND status='published') AS average,
    (SELECT COUNT(*) FROM ratings WHERE dish_id=d.id AND status='published') AS reviews_count,
    (SELECT COUNT(*) FROM votes WHERE dish_id=d.id) AS votes,
    CASE WHEN EXISTS (
        SELECT 1
        FROM votes
        WHERE votes.dish_id = d.id
        AND votes.user_id = ?
    ) THEN 1 ELSE 0 END AS has_voted
    FROM dishes d
    JOIN establishments e ON e.id=d.establishment_id
    WHERE 1=1";

$params = [(int) ($_SESSION['user_id'] ?? 0)];

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

<?php if (!empty($podium_dishes) && count($podium_dishes) === 3): ?>
    <section class="dish-section">
        <div class="section-heading">
            <h2><span class="heading-icon">🏆</span> Le Podium des 3 meilleurs plats</h2>
            <a href="/classement.php" class="text-link">Voir le classement →</a>
        </div>

        <div class="podium-container">
            <!-- 2ème position -->
            <article class="podium-card">
                <span class="podium-rank-badge">2</span>
                <a class="podium-image-link" href="/plat.php?id=<?= $podium_dishes[1]['id'] ?>">
                    <img src="<?= h(image_for($podium_dishes[1]['category'], $podium_dishes[1]['image_url'] ?? null)) ?>" alt="<?= h($podium_dishes[1]['name']) ?>" onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'">
                    <?php if (!empty($podium_dishes[1]['has_voted'])): ?>
                        <span class="detail-heart">♥</span>
                    <?php endif; ?>                </a>
                <div class="podium-body">
                    <h3><a href="/plat.php?id=<?= $podium_dishes[1]['id'] ?>"><?= h($podium_dishes[1]['name']) ?></a></h3>
                    <p class="restaurant-name"><?= h($podium_dishes[1]['restaurant']) ?></p>
                    <div class="dish-meta">
                        <span class="rating"><span class="star">★</span> <?= number_format((float)$podium_dishes[1]['average'], 1, '.', '') ?></span>
                    </div>
                    <a class="vote-badge" href="/plat.php?id=<?= $podium_dishes[1]['id'] ?>">👍 <?= (int)$podium_dishes[1]['votes'] ?> votes</a>
                </div>
            </article>

            <!-- 1ère position (Mise en avant / Surélevée) -->
            <article class="podium-card first">
                <span class="podium-rank-badge">1</span>
                <a class="podium-image-link" href="/plat.php?id=<?= $podium_dishes[0]['id'] ?>">
                    <img src="<?= h(image_for($podium_dishes[0]['category'], $podium_dishes[0]['image_url'] ?? null)) ?>" alt="<?= h($podium_dishes[0]['name']) ?>" onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'">
                    <?php if (!empty($podium_dishes[0]['has_voted'])): ?>
                        <span class="detail-heart">♥</span>
                    <?php endif; ?>                
                </a>
                <div class="podium-body">
                    <h3><a href="/plat.php?id=<?= $podium_dishes[0]['id'] ?>"><?= h($podium_dishes[0]['name']) ?></a></h3>
                    <p class="restaurant-name"><?= h($podium_dishes[0]['restaurant']) ?></p>
                    <div class="dish-meta">
                        <span class="rating"><span class="star">★</span> <?= number_format((float)$podium_dishes[0]['average'], 1, '.', '') ?></span>
                    </div>
                    <a class="vote-badge" href="/plat.php?id=<?= $podium_dishes[0]['id'] ?>">👍 <?= (int)$podium_dishes[0]['votes'] ?> votes</a>
                </div>
            </article>

            <!-- 3ème position -->
            <article class="podium-card">
                <span class="podium-rank-badge">3</span>
                <a class="podium-image-link" href="/plat.php?id=<?= $podium_dishes[2]['id'] ?>">
                    <img src="<?= h(image_for($podium_dishes[2]['category'], $podium_dishes[2]['image_url'] ?? null)) ?>" alt="<?= h($podium_dishes[2]['name']) ?>" onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'">
                    <?php if (!empty($podium_dishes[2]['has_voted'])): ?>
                        <span class="detail-heart">♥</span>
                    <?php endif; ?>                
                </a>
                <div class="podium-body">
                    <h3><a href="/plat.php?id=<?= $podium_dishes[2]['id'] ?>"><?= h($podium_dishes[2]['name']) ?></a></h3>
                    <p class="restaurant-name"><?= h($podium_dishes[2]['restaurant']) ?></p>
                    <div class="dish-meta">
                        <span class="rating"><span class="star">★</span> <?= number_format((float)$podium_dishes[2]['average'], 1, '.', '') ?></span>
                    </div>
                    <a class="vote-badge" href="/plat.php?id=<?= $podium_dishes[2]['id'] ?>">👍 <?= (int)$podium_dishes[2]['votes'] ?> votes</a>
                </div>
            </article>
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
            <h2><span class="heading-icon">🍽️</span> Tous les autres plats</h2>
        </div>
    <?php endif; ?>

    <div class="cards <?= $view === 'plats' ? 'grid-3' : '' ?>">
        <?php foreach ($dishes as $d): ?>
            <article class="dish-card">
                <a class="dish-image-link" href="/plat.php?id=<?= $d['id'] ?>">
                    <img src="<?= h(image_for($d['category'], $d['image_url'] ?? null)) ?>" alt="<?= h($d['name']) ?>" onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'">
                    <?php if (!empty($d['has_voted'])): ?>
                        <span class="detail-heart">♥</span>
                    <?php endif; ?>                
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