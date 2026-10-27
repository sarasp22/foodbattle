<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = '';

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
    require_once __DIR__ . '/includes/db.php';
    $s = $db->prepare('SELECT id FROM users WHERE email=?');
    $s->execute([$email]);
    if ($s->fetch()) {
      $errors[] = 'Un compte avec cette adresse e-mail existe déjà.';
    }
  }
}
