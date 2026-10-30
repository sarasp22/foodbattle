<?php
$title = 'Duel';
require 'includes/header.php';
require 'includes/db.php';

$cat = $_GET['categorie'] ?? '';
if (!in_array($cat, ['burger', 'tacos', 'pizza'], true)) {
    $cat = '';
}

$userId = $_SESSION['user_id'] ?? null;

// Traitement du vote lorsqu'un utilisateur choisit un plat du duel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post();
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
    $winner_id = filter_input(INPUT_POST, 'winner_id', FILTER_VALIDATE_INT);

    $check = $db->prepare('SELECT id FROM dishes WHERE id = ?');
    $check->execute([$winner_id]);
    if (!$check->fetch()) {
        http_response_code(404);
        exit('Plat introuvable.');
    }

    $userId = (int) $_SESSION['user_id'];

    $insert = $db->prepare(
        'INSERT INTO votes (dish_id, user_id)
        VALUES (?, ?)
        ON CONFLICT (user_id, dish_id) DO NOTHING'
    );
    $insert->execute([$winner_id, $userId]);

    if ($insert->rowCount() === 0) {
        $redirect = '/duel.php?deja=1' . ($cat !== '' ? '&categorie=' . urlencode($cat) : '');
        header('Location: ' . $redirect);
        exit;
    }

    $redirect = '/duel.php?voted=' . $winner_id . ($cat !== '' ? '&categorie=' . urlencode($cat) : '');
    header('Location: ' . $redirect);
    exit;
}

// Si un vote vient d'être enregistré, on récupère le nom du plat choisi pour le message de confirmation
$voted_dish = null;
$voted_id = filter_input(INPUT_GET, 'voted', FILTER_VALIDATE_INT);
if ($voted_id) {
    $stmt_voted = $db->prepare('SELECT name FROM dishes WHERE id = ?');
    $stmt_voted->execute([$voted_id]);
    $voted_dish = $stmt_voted->fetchColumn() ?: null;
}

// Sélection de 2 plats aléatoires non encore votés par ce visiteur
$sql = "
    SELECT d.*, e.name AS restaurant, e.city,
           (SELECT AVG(score) FROM ratings WHERE dish_id = d.id AND status = 'published') AS average,
           (SELECT COUNT(*) FROM votes WHERE dish_id = d.id) AS votes
    FROM dishes d
    JOIN establishments e ON e.id = d.establishment_id
    WHERE 1=1
";

$params = [];

if ($userId !== null) {
    $sql .= ' AND NOT EXISTS (
        SELECT 1 FROM votes v
        WHERE v.dish_id = d.id AND v.user_id = ?
    )';
    $params[] = $userId;
}

if ($cat !== '') {
    $sql .= ' AND d.category = ?';
    $params[] = $cat;
}

$sql .= ' ORDER BY RANDOM() LIMIT 2';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$pair = $stmt->fetchAll();
?>

<section class="dish-section duel-wrapper">
    <div class="section-heading">
        <h2><span class="heading-icon">⚔️</span> Duel des plats</h2>
    </div>
    <p class="duel-subtitle">Deux spécialités s’affrontent : choisis celle qui te fait le plus envie pour lui donner <strong>+1 vote</strong> !</p>

    <div class="category-pills" style="margin-bottom: 24px;" aria-label="Filtrer le duel par catégorie">
        <a class="category-pill <?= $cat === '' ? 'active' : '' ?>" href="/duel.php">👑 Tous</a>
        <a class="category-pill <?= $cat === 'burger' ? 'active' : '' ?>" href="/duel.php?categorie=burger">🍔 Burgers</a>
        <a class="category-pill <?= $cat === 'tacos' ? 'active' : '' ?>" href="/duel.php?categorie=tacos">🌮 Tacos</a>
        <a class="category-pill <?= $cat === 'pizza' ? 'active' : '' ?>" href="/duel.php?categorie=pizza">🍕 Pizzas</a>
    </div>

    <?php if ($voted_dish): ?>
        <p class="message" role="status">🔥 +1 vote enregistré pour <strong><?= h($voted_dish) ?></strong> ! Place au duel suivant :</p>
    <?php endif; ?>

    <?php if (count($pair) === 2): ?>
        <div class="duel-arena">
            <?php foreach ($pair as $i => $d): ?>
                <article class="duel-card">
                    <a class="duel-image-link" href="/plat.php?id=<?= (int)$d['id'] ?>">
                        <img src="<?= h(image_for($d['category'], $d['image_url'] ?? null)) ?>" alt="<?= h($d['name']) ?>" onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'">
                        <span class="duel-category-tag"><?= h($d['category']) ?></span>
                    </a>
                    <div class="duel-card-body">
                        <h3><a href="/plat.php?id=<?= (int)$d['id'] ?>"><?= h($d['name']) ?></a></h3>
                        <p class="restaurant-name">🍴 <?= h($d['restaurant']) ?> · <?= h($d['city']) ?></p>
                        <p class="duel-desc"><?= h($d['description']) ?></p>
                        <div class="duel-meta">
                            <span class="price-pill"><?= euro($d['price']) ?></span>
                            <span class="rating"><span class="star">★</span> <?= $d['average'] !== null ? number_format((float)$d['average'], 1, '.', '') : '—' ?></span>
                            <span class="vote-badge">👍 <?= (int)$d['votes'] ?> votes</span>
                        </div>
                        <?php if ($userId !== null): ?>
                            <form method="post" action="/duel.php<?= $cat !== '' ? '?categorie=' . h($cat) : '' ?>">
                                <input type="hidden" name="token" value="<?= h(csrf()) ?>">
                                <input type="hidden" name="winner_id" value="<?= (int)$d['id'] ?>">
                                <button type="submit" class="duel-vote-btn">⚡ Je choisis ce plat !</button>
                            </form>
                        <?php else: ?>
                            <a href="/login.php" class="duel-vote-btn duel-login-btn">👉🏾 Connecte-toi pour choisir ce plat</a>
                        <?php endif; ?>
                    </div>
                </article>
                <?php if ($i === 0): ?>
                    <div class="duel-vs-badge" aria-hidden="true">VS</div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 22px;">
            <a href="/duel.php<?= $cat !== '' ? '?categorie=' . h($cat) : '' ?>" class="text-link">🔄 Tirer un autre duel au sort</a>
        </div>
    <?php else: ?>
        <div class="duel-empty">
            <p>🎉 Tu as déjà voté pour tous les plats disponibles dans cette catégorie !</p>
            <p style="margin-top: 12px;">
                <a href="/classement.php" class="button">👑 Voir le classement</a>
            </p>
        </div>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>
