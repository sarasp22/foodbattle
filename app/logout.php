<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Supprime les données de la session
$_SESSION = [];

// Invalide le cookie de session
if (ini_get('session.use_cookies')) {
    $cookieParams = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

// Détruit la session
session_destroy();

// Redirige vers la page d'accueil
header('Location: /');
exit();