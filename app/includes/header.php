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
  <title><?= h($title ?? 'FoodBattle') ?> · FoodBattle</title>
  <link rel="stylesheet" href="/assets/style.css">
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
        <a href="/classement.php" class="<?= $current_page === 'classement.php' ? 'active' : '' ?>">Classement</a>
      </nav>
    </div>
    <div class="header-actions">
      <a class="icon-btn" href="/?view=plats#tous-les-plats" aria-label="Rechercher">🔍</a>
      <?php if (!empty($_SESSION['admin_logged_in'])): ?>
        <a class="icon-btn" href="/dashboard.php" title="Dashboard Admin">👤</a>
      <?php else: ?>
        <a class="icon-btn" href="/login.php" title="Connexion administrateur">👤</a>
      <?php endif ?>
    </div>
  </header>
  <main>
<?php endif; ?>
