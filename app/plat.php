<?php
$title = 'Le plat';
require 'includes/header.php';
require 'includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$s = $db->prepare("
    SELECT d.*, e.name AS restaurant, e.city, e.address,
           (SELECT AVG(score) FROM ratings WHERE dish_id=d.id AND status='published') AS average,
           (SELECT COUNT(*) FROM ratings WHERE dish_id=d.id AND status='published') AS reviews_count,
           (SELECT COUNT(*) FROM votes WHERE dish_id=d.id) AS votes
    FROM dishes d
    JOIN establishments e ON e.id=d.establishment_id
    WHERE d.id=?
");
$s->execute([$id]);
$d = $s->fetch();

if (!$d) {
    http_response_code(404);
    echo '<h1>Plat introuvable</h1><p><a href="/">Retour à l’accueil</a></p>';
    require 'includes/footer.php';
    exit;
}

$s = $db->prepare("SELECT * FROM ratings WHERE dish_id=? AND status='published' ORDER BY id DESC");
$s->execute([$id]);
$reviews = $s->fetchAll();
?>

<div class="detail-container">
    <div class="detail-hero">
        <a href="/" class="detail-back" aria-label="Retour">←</a>
        <img src="<?= h(image_for($d['category'], $d['image_url'] ?? null)) ?>" alt="<?= h($d['name']) ?>" onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'">
        <span class="detail-heart">♥</span>
    </div>

    <div class="detail-body">
        <?php if (isset($_GET['ok'])): ?>
            <p class="message" role="status">Merci, ta contribution a été enregistrée.</p>
        <?php endif ?>
        <?php if (isset($_GET['deja'])): ?>
            <p class="alert-error" role="alert">Tu as déjà voté pour ce plat.</p>
        <?php endif ?>

        <div class="detail-title-row">
            <h1><?= h($d['name']) ?></h1>
            <span class="price-pill"><?= euro($d['price']) ?></span>
        </div>

        <p class="detail-info-line">🍴 <?= h($d['restaurant']) ?></p>
        <p class="detail-info-line">📍 <?= h($d['address']) ?>, <?= h($d['city']) ?></p>

        <div class="detail-rating-line">
            <span class="star">★</span> <?= $d['average'] ? number_format((float)$d['average'], 1, '.', '') : '—' ?>
            <span class="muted">(<?= (int)$d['reviews_count'] ?> avis)</span>
        </div>

        <p class="detail-desc"><?= h($d['description']) ?></p>

        <div class="vote-box">
            <form method="post" action="/action.php">
                <input type="hidden" name="token" value="<?= csrf() ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <button class="vote-btn-main" name="action" value="vote">👍 Voter pour ce plat</button>
            </form>
            <div class="vote-box-count"><?= (int)$d['votes'] ?> votes</div>
        </div>

        <div class="reviews-header">
            <h2>Avis des utilisateurs</h2>
            <a href="#form-avis" class="text-link" style="color: var(--orange-dark);">Donner mon avis →</a>
        </div>

        <?php foreach ($reviews as $r):
            $initials = strtoupper(mb_substr($r['nickname'], 0, 2, 'UTF-8'));
        ?>
            <article class="review-card">
                <div class="avatar-circle"><?= h($initials) ?></div>
                <div class="review-content">
                    <div class="review-top">
                        <strong><?= h($r['nickname']) ?></strong>
                        <span class="date"><?= h(format_date_fr($r['created_at'])) ?></span>
                    </div>
                    <div class="review-stars">
                        <?= str_repeat('★', (int)$r['score']) . str_repeat('☆', 5 - (int)$r['score']) ?>
                    </div>
                    <p><?= h($r['comment']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$reviews): ?>
            <p style="color: var(--muted); font-size: 13px;">Pas encore d’avis publié pour ce plat.</p>
        <?php endif; ?>

        <hr style="border:0; border-top:1px solid var(--line); margin: 28px 0;">

        <h2 id="form-avis" style="font-size: 17px; margin-bottom: 12px;">Ajouter un avis</h2>
        <form method="post" action="/action.php">
            <input type="hidden" name="token" value="<?= csrf() ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="action" value="rate">
            <label>Pseudo <input name="nickname" maxlength="60" required></label>
            <label>Note
                <select name="score" required>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?> / 5</option>
                    <?php endfor ?>
                </select>
            </label>
            <label>Commentaire <textarea name="comment" maxlength="1000" required></textarea></label>
            <button type="submit" style="margin-top: 10px;">Publier mon avis</button>
        </form>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
