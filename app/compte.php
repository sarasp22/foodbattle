<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: /login.php');
  exit();
}

require_once __DIR__ . '/includes/db.php';

$statement = $db->prepare(
  'SELECT nickname, email FROM users WHERE id = ?'
);
$statement->execute([$_SESSION['user_id']]);
$user = $statement->fetch();

if (!$user) {
  header('Location: /logout.php');
  exit();
}

$title = 'Mon compte';

require_once __DIR__ . '/includes/header.php';
?>

<section class="login-page">
  <div class="login-card">
    <h1>Mon compte</h1>
    <p>
      <strong>Pseudo :</strong>
      <?= h($user['nickname']) ?>
    </p>
    <p>
      <strong>Adresse e-mail :</strong>
      <?= h($user['email']) ?>
    </p>
    <a href="/" class="back-home-link">Retour à l’accueil</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>