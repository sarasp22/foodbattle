<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/functions.php';

$title = 'Connexion';
require_once __DIR__ . '/includes/header.php';
?>

<h1>Connexion administrateur</h1>

<form method="post" action="login.php">
    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Se connecter</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>