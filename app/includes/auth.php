<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['user_role'] ?? null) !== 'admin') {
    header('Location: /login.php');
    exit();
}