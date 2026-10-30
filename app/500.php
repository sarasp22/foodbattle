<?php
http_response_code(500);
$title = 'Erreur 500 — Erreur interne du serveur';
require_once __DIR__ . '/includes/header.php';
?>

<section class="error-page">
    <div class="error-card">
        <img
            class="error-illustration"
            src="/assets/images/500.png"
            alt="Erreur 500 - Oups ! Erreur interne du serveur. Une tempête fait rage en cuisine !"
        >
        <a class="button error-home-btn" href="/">🏠 Retour à l’accueil</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
