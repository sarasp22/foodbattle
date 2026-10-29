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

$userId = (int) $_SESSION['user_id'];

$statement = $db->prepare(
  'SELECT nickname, email, password_hash FROM users WHERE id = ?'
);
$statement->execute([$userId]);
$user = $statement->fetch();

if (!$user) {
  header('Location: /logout.php');
  exit();
}

$editing = isset($_GET['modifier']) && $_GET['modifier'] === '1';
$errors = [];
$success = '';

$nickname = $user['nickname'];
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $editing = true;
  check_post();

  $nickname = trim($_POST['nickname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $currentPassword = $_POST['current_password'] ?? '';
  $newPassword = $_POST['new_password'] ?? '';
  $passwordConfirm = $_POST['password_confirm'] ?? '';

  if ($nickname === '') {
    $errors[] = 'Le pseudo est obligatoire.';
  } elseif (mb_strlen($nickname, 'UTF-8') > 60) {
    $errors[] = 'Le pseudo ne doit pas dépasser 60 caractères.';
  }

  if ($email === '') {
    $errors[] = 'L’adresse e-mail est obligatoire.';
  } elseif (mb_strlen($email, 'UTF-8') > 255) {
    $errors[] = 'L’adresse e-mail ne doit pas dépasser 255 caractères.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'L’adresse e-mail n’est pas valide.';
  }

  if ($newPassword !== '') {
    if ($currentPassword === '') {
      $errors[] = 'Saisissez votre mot de passe actuel pour le modifier.';
    } elseif (!password_verify($currentPassword, $user['password_hash'])) {
      $errors[] = 'Le mot de passe actuel est incorrect.';
    }

    if (mb_strlen($newPassword, 'UTF-8') < 12) {
      $errors[] = 'Le nouveau mot de passe doit contenir au moins 12 caractères.';
    }

    if ($passwordConfirm === '') {
      $errors[] = 'Confirmez votre nouveau mot de passe.';
    } elseif ($newPassword !== $passwordConfirm) {
      $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
    }
  }

  if (empty($errors)) {
    $statement = $db->prepare(
      'SELECT id FROM users WHERE email = ? AND id <> ?'
    );
    $statement->execute([$email, $userId]);

    if ($statement->fetch()) {
      $errors[] = 'Cette adresse e-mail est déjà utilisée.';
    }
  }

  if (empty($errors)) {
    if ($newPassword !== '') {
      $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);

      $statement = $db->prepare(
        'UPDATE users SET nickname = ?, email = ?, password_hash = ? WHERE id = ?'
      );
      $statement->execute([$nickname, $email, $passwordHash, $userId]);
    } else {
      $statement = $db->prepare(
        'UPDATE users SET nickname = ?, email = ? WHERE id = ?'
      );
      $statement->execute([$nickname, $email, $userId]);
    }

    $_SESSION['user_nickname'] = $nickname;

    $user['nickname'] = $nickname;
    $user['email'] = $email;

    $success = 'Votre profil a été mis à jour.';
    $editing = false;
  }
}

$title = 'Mon compte';

require_once __DIR__ . '/includes/header.php';
?>

<section class="login-page">
  <div class="login-card">
    <h1>Mon compte</h1>
    <?php if (!empty($errors)): ?>
      <div class="alert-error" role="alert">
        <?php foreach ($errors as $error): ?>
          <p><?= h($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
      <p class="alert-success" role="status">
        <?= h($success) ?>
      </p>
    <?php endif; ?>

    <?php if (!$editing): ?>

      <p>
        <strong>Pseudo :</strong>
        <?= h($user['nickname']) ?>
      </p>

      <p>
        <strong>Adresse e-mail :</strong>
        <?= h($user['email']) ?>
      </p>

      <a href="/compte.php?modifier=1" class="btn-full">
        Modifier mon profil
      </a>
      <a href="/" class="back-home-link">Retour à l’accueil</a>

      <div class="login-mascot-footer">
        <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'">
      </div>

    <?php else: ?>

      <form method="post" action="/compte.php?modifier=1">
        <label for="nickname">Pseudo</label>
        <input
          type="text"
          id="nickname"
          name="nickname"
          maxlength="60"
          value="<?= h($nickname) ?>"
          autocomplete="nickname"
          required>

        <label for="email">Adresse e-mail</label>
        <input
          type="email"
          id="email"
          name="email"
          maxlength="255"
          value="<?= h($email) ?>"
          autocomplete="email"
          required>

        <hr>

        <h2>Changer de mot de passe</h2>
        <p>
          Laissez ces champs vides si vous ne souhaitez pas modifier
          votre mot de passe.
        </p>

        <label for="current_password">Mot de passe actuel</label>
        <input
          type="password"
          id="current_password"
          name="current_password"
          autocomplete="current-password">

        <label for="new_password">Nouveau mot de passe</label>
        <input
          type="password"
          id="new_password"
          name="new_password"
          minlength="12"
          autocomplete="new-password">

        <label for="password_confirm">Confirmer le nouveau mot de passe</label>
        <input
          type="password"
          id="password_confirm"
          name="password_confirm"
          minlength="12"
          autocomplete="new-password">

        <input type="hidden" name="token" value="<?= h(csrf()) ?>">

        <button type="submit" class="btn-full">
          Enregistrer les modifications
        </button>

        <div class="cancel-link-container">
          <a href="/compte.php" class="back-home-link">Annuler</a>
        </div>
      </form>

      <div class="login-mascot-footer">
        <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'">
      </div>

    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>