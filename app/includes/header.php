<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" type="image/x-icon" href="/assets/images/burger.ico">
  <title><?= h($title ?? 'FoodBattle') ?> · FoodBattle</title>
  <link rel="stylesheet" href="/assets/style.css">
  <link rel="stylesheet" href="/assets/css/header.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/duel.css">
  <link rel="stylesheet" href="/assets/css/roue.css">
</head>

<body>
  <?php if (empty($hide_site_header)): ?>
    <header class="site-header">
      <div class="header-left">
        <a class="logo" href="/">
          <img src="/assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
          <div>Food<span>Battle</span></div>
        </a>
        <nav class="main-nav">
          <a href="/" class="<?= ($current_page === 'index.php' && empty($_GET['view'])) ? 'active' : '' ?>">Accueil</a>
          <a href="/?view=plats#tous-les-plats" class="<?= (!empty($_GET['view']) && $_GET['view'] === 'plats') ? 'active' : '' ?>">Plats</a>
          <a href="/duel.php" class="<?= $current_page === 'duel.php' ? 'active' : '' ?>">⚔️ Duel</a>
          <a href="/roue.php" class="<?= $current_page === 'roue.php' ? 'active' : '' ?>">🎰 Roue du Destin</a>
          <a href="/classement.php" class="<?= $current_page === 'classement.php' ? 'active' : '' ?>">Classement</a>
        </nav>
      </div>
      <div class="header-actions">
        <?php if (($_SESSION['user_role'] ?? null) === 'admin'): ?>
          <a class="icon-btn" href="/dashboard.php" title="Tableau de bord administrateur">👤</a>
          <a href="/logout.php">Déconnexion</a>
        <?php elseif (isset($_SESSION['user_id'])): ?>
          <span>Bonjour <?= h($_SESSION['user_nickname']) ?></span>
          <a href="/compte.php">Mon compte</a>
          <a href="/logout.php">Déconnexion</a>
        <?php else: ?>
          <a href="/login.php">Connexion</a>
          <a href="/inscription.php">Inscription</a>
        <?php endif ?>
        <button type="button" class="mobile-menu-toggle" aria-expanded="false" aria-label="Menu" onclick="var h=this.closest('.site-header');h.classList.toggle('nav-open');this.setAttribute('aria-expanded',h.classList.contains('nav-open'));">☰ Menu</button>
      </div>
    </header>
    <main>
  <?php endif; ?>
