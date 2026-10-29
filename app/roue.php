<?php
// Source: Adapted with Google Maps Platform Embed API
$title = 'La Roue du Destin';
require 'includes/header.php';
require 'includes/db.php';

$cat = $_GET['categorie'] ?? '';
if (!in_array($cat, ['burger', 'tacos', 'pizza'], true)) {
    $cat = '';
}

$ville = trim($_GET['ville'] ?? '');
$cities = [];
$pool = [];
$winner = null;
$db_error = null;

try {
    $cities_stmt = $db->query('SELECT DISTINCT city FROM establishments ORDER BY city ASC');
    $cities = $cities_stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array($ville, $cities, true)) {
        $ville = '';
    }

    $sql = "
        SELECT d.*, e.name AS restaurant, e.city, e.address,
               (SELECT AVG(score) FROM ratings WHERE dish_id = d.id AND status = 'published') AS average,
               (SELECT COUNT(*) FROM votes WHERE dish_id = d.id) AS votes
        FROM dishes d
        JOIN establishments e ON e.id = d.establishment_id
        WHERE 1=1
    ";
    $params = [];

    if ($cat !== '') {
        $sql .= ' AND d.category = ?';
        $params[] = $cat;
    }

    if ($ville !== '') {
        $sql .= ' AND e.city = ?';
        $params[] = $ville;
    }

    $sql .= ' ORDER BY RANDOM()';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $pool = $stmt->fetchAll();

    if (!empty($pool)) {
        $winner = $pool[0];
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $db_error = 'Impossible de récupérer les plats pour le moment. Réessaie dans quelques instants.';
}

$maps_api_key = getenv('GOOGLE_MAPS_API_KEY') ?: '';

function build_map_embed_url(string $restaurant, string $address, string $city, string $apiKey = ''): string {
    $query = rawurlencode($restaurant . ', ' . $address . ', ' . $city . ', France');
    if ($apiKey !== '') {
        return 'https://www.google.com/maps/embed/v1/search?key=' . rawurlencode($apiKey)
            . '&solution_id=gmp_mcp_codeassist_v1_aistudio&q=' . $query . '&language=fr&region=FR';
    }
    return 'https://maps.google.com/maps?q=' . $query . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
}

$client_pool = [];
foreach ($pool as $d) {
    $client_pool[] = [
        'id'             => (int) $d['id'],
        'name'           => (string) $d['name'],
        'category'       => (string) $d['category'],
        'priceFormatted' => euro($d['price']),
        'description'    => (string) $d['description'],
        'image'          => image_for($d['category'], $d['image_url'] ?? null),
        'restaurant'     => (string) $d['restaurant'],
        'city'           => (string) $d['city'],
        'address'        => (string) $d['address'],
        'average'        => $d['average'] !== null ? number_format((float) $d['average'], 1, '.', '') : '—',
        'votes'          => (int) $d['votes'],
        'mapUrl'         => build_map_embed_url($d['restaurant'], $d['address'], $d['city'], $maps_api_key),
    ];
}

function roue_filter_url(string $targetCat, string $currentVille): string {
    $q = [];
    if ($targetCat !== '') $q['categorie'] = $targetCat;
    if ($currentVille !== '') $q['ville'] = $currentVille;
    return '/roue.php' . (!empty($q) ? '?' . http_build_query($q) : '');
}
?>

<section class="dish-section roue-wrapper">
    <div class="roue-hero-banner">
        <h1>🎰 Je ne sais pas quoi manger !</h1>
        <p>Choisis ta ville et ta catégorie préférée, puis lance <strong>La Roue du Destin</strong> pour découvrir ton prochain repas et le localiser sur la carte.</p>

        <div class="roue-filters-bar">
            <form method="get" action="/roue.php" class="roue-city-select-group">
                <?php if ($cat !== ''): ?>
                    <input type="hidden" name="categorie" value="<?= h($cat) ?>">
                <?php endif; ?>
                <label for="ville-select">📍 Ma ville :</label>
                <select id="ville-select" name="ville" onchange="this.form.submit()">
                    <option value="">Toutes les villes</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= h($c) ?>" <?= $c === $ville ? 'selected' : '' ?>><?= h($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div class="category-pills" aria-label="Filtrer par catégorie">
                <a class="category-pill <?= $cat === '' ? 'active' : '' ?>" href="<?= h(roue_filter_url('', $ville)) ?>">👑 Tous</a>
                <a class="category-pill <?= $cat === 'burger' ? 'active' : '' ?>" href="<?= h(roue_filter_url('burger', $ville)) ?>">🍔 Burgers</a>
                <a class="category-pill <?= $cat === 'tacos' ? 'active' : '' ?>" href="<?= h(roue_filter_url('tacos', $ville)) ?>">🌮 Tacos</a>
                <a class="category-pill <?= $cat === 'pizza' ? 'active' : '' ?>" href="<?= h(roue_filter_url('pizza', $ville)) ?>">🍕 Pizzas</a>
            </div>
        </div>

        <div class="roue-controls-top">
            <button type="button" id="sound-toggle-btn" class="roue-sound-btn" aria-pressed="true">
                🔊 Effets sonores : Activés
            </button>
        </div>
    </div>

    <?php if ($db_error !== null): ?>
        <p class="alert-error" role="alert"><?= h($db_error) ?></p>
    <?php elseif ($winner !== null): ?>
        <div class="roue-stage">
            <!-- Colonne gauche : La Roue animée + Historique directement dessous -->
            <div class="roue-left-col">
                <div class="roue-wheel-box">
                    <div class="wheel-container">
                        <div class="wheel-pointer" aria-hidden="true">🔻</div>
                        <div id="wheel-disc" class="wheel-disc">
                            <div class="wheel-center-hub">🎲</div>
                        </div>
                    </div>
                    <button type="button" id="spin-btn" class="roue-spin-btn">
                        🔄 Retenter ma chance !
                    </button>
                    <div id="slot-ticker" class="roue-slot-ticker" aria-live="polite">
                        ✨ Le destin a parlé !
                    </div>
                </div>

                <!-- Historique des derniers tirages -->
                <div class="roue-history-section">
                    <div class="roue-history-header">
                        <h3>📜 Historique de tes derniers tirages</h3>
                    </div>
                    <div id="roue-history-list" class="roue-history-list"></div>
                </div>
            </div>

            <!-- Colonne droite : Plat gagnant + Carte Google Maps avec Pin Jaune -->
            <div class="roue-result-panel">
                <article id="winner-card" class="roue-winner-card">
                    <a id="winner-img-link" class="roue-winner-media" href="/plat.php?id=<?= (int) $winner['id'] ?>">
                        <img
                            id="winner-img"
                            src="<?= h(image_for($winner['category'], $winner['image_url'] ?? null)) ?>"
                            alt="<?= h($winner['name']) ?>"
                            onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'"
                        >
                        <span id="winner-cat" class="roue-winner-badge"><?= h($winner['category']) ?></span>
                    </a>
                    <div class="roue-winner-body">
                        <div>
                            <span class="roue-winner-kicker">🎉 Plat tiré au sort</span>
                            <div class="roue-winner-title-row">
                                <h2 id="winner-name"><?= h($winner['name']) ?></h2>
                                <span id="winner-price" class="price-pill"><?= euro($winner['price']) ?></span>
                            </div>
                            <p class="detail-info-line">🍴 <strong id="winner-restaurant"><?= h($winner['restaurant']) ?></strong></p>
                            <p class="detail-info-line">📍 <span id="winner-address"><?= h($winner['address']) ?>, <?= h($winner['city']) ?></span></p>
                            <p id="winner-desc" class="roue-winner-desc"><?= h($winner['description']) ?></p>
                        </div>

                        <div>
                            <div class="dish-meta">
                                <span class="rating"><span class="star">★</span> <span id="winner-avg"><?= $winner['average'] !== null ? number_format((float) $winner['average'], 1, '.', '') : '—' ?></span></span>
                                <span class="vote-badge">👍 <span id="winner-votes"><?= (int) $winner['votes'] ?></span> votes</span>
                            </div>
                            <div class="roue-winner-actions">
                                <a id="winner-link" class="button" href="/plat.php?id=<?= (int) $winner['id'] ?>">
                                    🍴 Voir la fiche du plat →
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Carte Google Maps de l'établissement avec Pin Jaune -->
                <div class="roue-map-card">
                    <div class="roue-map-header">
                        <h3>🗺️ Localisation du restaurant</h3>
                        <span id="map-label" class="roue-map-address">
                            <?= h($winner['restaurant']) ?> — <?= h($winner['address']) ?>, <?= h($winner['city']) ?>
                        </span>
                    </div>
                    <div class="roue-map-container">
                        <div class="roue-yellow-pin-overlay" aria-hidden="true">
                            <div id="yellow-pin-label" class="yellow-pin-label">🍴 <?= h($winner['restaurant']) ?></div>
                            <div class="yellow-pin-marker"><span>🍔</span></div>
                            <div class="yellow-pin-pulse"></div>
                        </div>
                        <iframe
                            id="winner-map"
                            class="roue-map-frame"
                            width="600"
                            height="290"
                            frameborder="0"
                            style="border:0"
                            referrerpolicy="strict-origin-when-cross-origin"
                            src="<?= h(build_map_embed_url($winner['restaurant'], $winner['address'], $winner['city'], $maps_api_key)) ?>"
                            allowfullscreen
                            loading="lazy"
                            title="Carte du restaurant <?= h($winner['restaurant']) ?>"
                        ></iframe>
                    </div>
                    <p style="margin:6px 0 0;font-size:11px;color:var(--muted);text-align:right;">Google Maps</p>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var pool = <?= json_encode($client_pool, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            var currentRotation = 0;
            var soundEnabled = true;
            var audioCtx = null;

            var wheelDisc = document.getElementById('wheel-disc');
            var spinBtn = document.getElementById('spin-btn');
            var soundBtn = document.getElementById('sound-toggle-btn');
            var ticker = document.getElementById('slot-ticker');
            var winnerCard = document.getElementById('winner-card');
            var historyContainer = document.getElementById('roue-history-list');

            function playTone(freq, duration, type) {
                if (!soundEnabled) return;
                try {
                    var AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContextClass) return;
                    if (!audioCtx) audioCtx = new AudioContextClass();
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.type = type || 'triangle';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start();
                    osc.stop(audioCtx.currentTime + duration);
                } catch (e) {}
            }

            function playWinFanfare() {
                playTone(523.25, 0.12, 'triangle');
                setTimeout(function () { playTone(659.25, 0.12, 'triangle'); }, 110);
                setTimeout(function () { playTone(783.99, 0.25, 'triangle'); }, 220);
            }

            function saveToHistory(dish) {
                try {
                    var raw = sessionStorage.getItem('fb_roue_history');
                    var list = raw ? JSON.parse(raw) : [];
                    list = list.filter(function (item) { return item.id !== dish.id; });
                    list.unshift(dish);
                    list = list.slice(0, 4);
                    sessionStorage.setItem('fb_roue_history', JSON.stringify(list));
                    renderHistory(list);
                } catch (e) {}
            }

            function renderHistory(list) {
                if (!historyContainer) return;
                if (!list || list.length === 0) {
                    historyContainer.innerHTML = '<p style="color:var(--muted);font-size:13px;margin:0;">Aucun tirage précédent.</p>';
                    return;
                }
                historyContainer.innerHTML = list.map(function (item) {
                    var link = document.createElement('a');
                    link.className = 'roue-history-item';
                    link.href = '/plat.php?id=' + Number(item.id);

                    var img = document.createElement('img');
                    img.src = item.image;
                    img.alt = '';
                    img.onerror = function () { this.onerror = null; this.src = '/assets/images/placeholder.svg'; };

                    var div = document.createElement('div');
                    var strong = document.createElement('strong');
                    strong.textContent = item.name;
                    var span = document.createElement('span');
                    span.textContent = '🍴 ' + item.restaurant + ' (' + item.city + ')';

                    div.appendChild(strong);
                    div.appendChild(span);
                    link.appendChild(img);
                    link.appendChild(div);
                    return link.outerHTML;
                }).join('');
            }

            function updateWinnerDOM(dish) {
                document.getElementById('winner-name').textContent = dish.name;
                document.getElementById('winner-price').textContent = dish.priceFormatted;
                document.getElementById('winner-cat').textContent = dish.category;
                document.getElementById('winner-restaurant').textContent = dish.restaurant;
                document.getElementById('winner-address').textContent = dish.address + ', ' + dish.city;
                document.getElementById('winner-desc').textContent = dish.description;
                document.getElementById('winner-avg').textContent = dish.average;
                document.getElementById('winner-votes').textContent = dish.votes;
                document.getElementById('winner-img').src = dish.image;
                document.getElementById('winner-img').alt = dish.name;
                document.getElementById('winner-img-link').href = '/plat.php?id=' + dish.id;
                document.getElementById('winner-link').href = '/plat.php?id=' + dish.id;
                document.getElementById('map-label').textContent = dish.restaurant + ' — ' + dish.address + ', ' + dish.city;
                var pinLabel = document.getElementById('yellow-pin-label');
                if (pinLabel) pinLabel.textContent = '🍴 ' + dish.restaurant;
                document.getElementById('winner-map').src = dish.mapUrl;
            }

            if (soundBtn) {
                soundBtn.addEventListener('click', function () {
                    soundEnabled = !soundEnabled;
                    soundBtn.setAttribute('aria-pressed', soundEnabled ? 'true' : 'false');
                    soundBtn.textContent = soundEnabled ? '🔊 Effets sonores : Activés' : '🔇 Effets sonores : Coupés';
                });
            }

            function triggerSpin() {
                if (!pool || pool.length === 0) return;
                spinBtn.disabled = true;
                winnerCard.classList.add('is-spinning');

                var extraSpins = 1080 + Math.floor(Math.random() * 720);
                currentRotation += extraSpins;
                wheelDisc.style.transform = 'rotate(' + currentRotation + 'deg)';

                var chosen = pool[Math.floor(Math.random() * pool.length)];

                var steps = 14;
                var step = 0;
                var interval = setInterval(function () {
                    var randomPreview = pool[Math.floor(Math.random() * pool.length)];
                    ticker.textContent = '🎰 ' + randomPreview.name + ' (' + randomPreview.city + ')…';
                    playTone(300 + (step * 25), 0.05, 'sine');
                    step++;
                    if (step >= steps) {
                        clearInterval(interval);
                    }
                }, 160);

                setTimeout(function () {
                    updateWinnerDOM(chosen);
                    winnerCard.classList.remove('is-spinning');
                    ticker.textContent = '✨ C’est décidé : ' + chosen.name + ' à ' + chosen.city + ' !';
                    spinBtn.disabled = false;
                    playWinFanfare();
                    saveToHistory(chosen);
                }, 2500);
            }

            if (spinBtn) {
                spinBtn.addEventListener('click', triggerSpin);
            }

            if (pool.length > 0) {
                saveToHistory(pool[0]);
            }
        })();
        </script>
    <?php else: ?>
        <div class="duel-empty">
            <p>😕 Aucun plat n'est disponible pour cette sélection (<?= $ville !== '' ? 'ville : <strong>' . h($ville) . '</strong>' : 'toutes les villes' ?><?= $cat !== '' ? ', catégorie : <strong>' . h($cat) . '</strong>' : '' ?>).</p>
            <p style="margin-top: 14px;">
                <a href="/roue.php" class="button">🔄 Réinitialiser les filtres</a>
            </p>
        </div>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>
