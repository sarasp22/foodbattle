<?php
http_response_code(404);
$title = 'Erreur 404 — Page introuvable';
require_once __DIR__ . '/includes/header.php';
?>

<section class="error-page">
    <div class="error-card">
        <img
            class="error-illustration"
            src="/assets/images/404.png"
            alt="Erreur 404 - Oups ! Page introuvable. La page que vous recherchez semble avoir disparu ou n'existe plus."
        >
        <a class="button error-home-btn" href="/">🏠 Retour à l’accueil</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
