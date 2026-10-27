<?php

if (PHP_SAPI !== 'cli') {
  exit("Ce script doit être exécuté depuis la ligne de commande.\n");
}

require_once __DIR__ . '/../html/includes/db.php';

$adminEmail = getenv('ADMIN_EMAIL');
$adminPasswordHash = getenv('ADMIN_PASSWORD_HASH');
$adminNickname = getenv('ADMIN_NICKNAME');

if (!$adminEmail || !$adminPasswordHash || !$adminNickname) {
  exit("Erreur : ADMIN_EMAIL, ADMIN_PASSWORD_HASH et ADMIN_NICKNAME doivent être renseignés.\n");
}

if (mb_strlen($adminNickname, 'UTF-8') > 60) {
  exit("Erreur : ADMIN_NICKNAME ne doit pas dépasser 60 caractères.\n");
}

if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
  exit("Erreur : ADMIN_EMAIL n'est pas une adresse e-mail valide.\n");
}

$hashInfo = password_get_info($adminPasswordHash);

if ($hashInfo['algo'] === null || $hashInfo['algo'] === false) {
  exit("Erreur : ADMIN_PASSWORD_HASH ne contient pas un hash valide.\n");
}

$statement = $db->prepare(
  'SELECT role FROM users WHERE email = :email'
);

$statement->execute([
  'email' => $adminEmail,
]);

$existingUser = $statement->fetch();

if ($existingUser) {
  if ($existingUser['role'] === 'admin') {
    exit("Le compte administrateur existe déjà.\n");
  }

  exit("Erreur : cette adresse e-mail appartient déjà à un utilisateur.\n");
}

$statement = $db->prepare(
  'INSERT INTO users (nickname, email, password_hash, role)
    VALUES (:nickname, :email, :password_hash, :role)'
);

$statement->execute([
  'nickname' => $adminNickname,
  'email' => $adminEmail,
  'password_hash' => $adminPasswordHash,
  'role' => 'admin',
]);

echo "Le compte administrateur a été créé avec succès.\n";
