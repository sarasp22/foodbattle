<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post();
    $password = $_POST['password'] ?? '';
    $hashedPassword = getenv('ADMIN_PASSWORD_HASH');

    if ($hashedPassword && password_verify($password, $hashedPassword)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: /dashboard.php');
        exit();
    } else {
        $error = 'Mot de passe incorrect.';
    }
}

$title = 'Connexion';
require_once __DIR__ . '/includes/header.php';
?>

<div class="login-page">
    <div class="login-card">
        <div class="login-crown">👑</div>
        <div class="login-logo">Food<span>Battle</span></div>
        <h1>Connexion administrateur</h1>
        <p class="sub">Accédez à l’espace d’administration pour gérer les plats et les avis.</p>

        <?php if (isset($error)): ?>
            <p class="alert-error" role="alert"><?= h($error) ?></p>
        <?php endif ?>

        <form method="post" action="login.php">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
            <input type="hidden" name="token" value="<?= h(csrf()) ?>">
            <button type="submit" class="btn-full">Se connecter</button>
        </form>

        <a href="/" class="back-home-link">Retour à l’accueil</a>

        <div class="login-mascot-footer">
            <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'">
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
