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

<h1>Connexion administrateur</h1>

<form method="post" action="login.php">
    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Se connecter</button>
    <input type="hidden" name="token" value="<?= h(csrf()) ?>">
</form>


<?php require_once __DIR__ . '/includes/footer.php'; ?>