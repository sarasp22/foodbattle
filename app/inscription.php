<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/includes/functions.php';
$title = 'Créer un compte';

$errors = [];

$nickname = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_post();
  $nickname = trim($_POST['nickname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
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
  if ($password === '') {
    $errors[] = 'Le mot de passe est obligatoire.';
  } elseif (mb_strlen($password, 'UTF-8') < 12) {
    $errors[] = 'Le mot de passe doit contenir au moins 12 caractères.';
  }
  if ($passwordConfirm === '') {
    $errors[] = 'La confirmation du mot de passe est obligatoire.';
  } elseif ($password !== $passwordConfirm) {
    $errors[] = 'Les mots de passe ne correspondent pas.';
  }
  if (empty($errors)) {
    try {
      require_once __DIR__ . '/includes/db.php';
      $s = $db->prepare('SELECT id FROM users WHERE email=?');
      $s->execute([$email]);
      if ($s->fetch()) {
        $errors[] = 'Un compte avec cette adresse e-mail existe déjà.';
      } else {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $s = $db->prepare('INSERT INTO users(nickname, email, password_hash, role) VALUES(?,?,?,?)');
        $s->execute([$nickname, $email, $passwordHash, 'user']);
        $_SESSION['success_message'] = 'Votre compte a été créé avec succès.';
        header('Location: /login.php');
        exit();
      }
    } catch (PDOException $e) {
      $errors[] = 'Une erreur est survenue lors de la création de votre compte.';
      error_log($e->getMessage());
    }
  }
}

require_once __DIR__ . '/includes/header.php'; ?>

<div class="login-page">
  <div class="login-card">
    <div class="login-crown">👑</div>
    <div class="login-logo">Food<span>Battle</span></div>

    <h1>Créer un compte</h1>
    <p class="sub">Rejoignez FoodBattle pour partager vos avis sur les plats.</p>

    <?php if (!empty($errors)): ?> 
      <div class="alert-error" role="alert">
        <?php foreach ($errors as $error): ?> 
          <p><?= h($error) ?></p> 
        <?php endforeach; ?> 
      </div> 
    <?php endif; ?>
       
    <form method="post" action="inscription.php"> 
      <label for="nickname">Pseudo</label> 
      <input 
        type="text" 
        id="nickname" 
        name="nickname" 
        placeholder="Choisissez un pseudo" 
        maxlength="60" 
        value="<?= h($nickname) ?>" 
        autocomplete="nickname" required
      > 
      <label for="email">Adresse e-mail</label> 
      <input 
        type="email" 
        id="email" 
        name="email" 
        placeholder="Entrez votre adresse e-mail" 
        maxlength="255" 
        value="<?= h($email) ?>" 
        autocomplete="email" required
      > 
      <label for="password">Mot de passe</label> 
      <input 
        type="password" 
        id="password" 
        name="password" 
        placeholder="12 caractères minimum" 
        minlength="12" 
        autocomplete="new-password" required
      > 
      <label for="password_confirm">Confirmer le mot de passe</label> 
      <input 
        type="password" 
        id="password_confirm" 
        name="password_confirm" 
        placeholder="Confirmez votre mot de passe" 
        minlength="12" 
        autocomplete="new-password" required
      > 
      <input type="hidden" name="token" value="<?= h(csrf()) ?>"> 
      <button type="submit" class="btn-full">Créer mon compte</button> 
    </form> 

    <a href="/" class="back-home-link">Retour à l’accueil</a>
    <div class="login-mascot-footer"> 
      <img src="/assets/images/logo.png" alt="" onerror="this.style.display='none'"> 
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>