<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_post();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $statement = $db->prepare('SELECT id, nickname, email, password_hash, role FROM users WHERE email = ?');
    $statement->execute([$email]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nickname'] = $user['nickname'];
        $_SESSION['user_role'] = $user['role'];

        header('Location: ' . ($user['role'] === 'admin' ? '/dashboard.php' : '/'));
        exit();
    }

    $error = 'Adresse e-mail ou mot de passe incorrect.';
}

$title = 'Connexion';
require_once __DIR__ . '/includes/header.php';
?>

<div class="login-page">
    <div class="login-card">
        <div class="login-crown">👑</div>
        <div class="login-logo">Food<span>Battle</span></div>
        <?php if ($success !== ''): ?>
            <p class="alert-success" role="status">
                <?= h($success) ?>
            </p>
        <?php endif; ?>
        <h1>Connexion</h1>
        <p class="sub">Connectez-vous à votre compte FoodBattle</p>

        <?php if ($error !== ''): ?>
            <p class="alert-error" role="alert"><?= h($error) ?></p>
        <?php endif ?>

        <form method="post" action="login.php">
            <label for="email">Adresse e-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Entrez votre adresse e-mail"
                autocomplete="email"
                required>
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