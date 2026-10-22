<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($title ?? 'FoodBattle') ?> · FoodBattle</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>

<body>
  <header><a class="logo" href="/">FoodBattle 🍔</a>
    <nav>
      <a href="/">Découvrir</a>
      <a href="/classement.php">Classement</a>
      
      <?php if (!empty($_SESSION['admin_logged_in'])): ?>
        <a href="/dashboard.php">Dashboard</a>
        <a href="/moderation.php">Modération</a>
        <a href="/logout.php">Déconnexion</a>
      <?php else: ?>
        <a href="/login.php">Connexion</a>
      <?php endif ?>
    </nav>
  </header>
  <main>