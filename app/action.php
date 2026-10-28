<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'includes/functions.php';
require 'includes/db.php';
check_post();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$s = $db->prepare('SELECT id FROM dishes WHERE id=?');
$s->execute([$id]);
if (!$s->fetch()) {
  http_response_code(404);
  exit('Plat introuvable.');
}
if (($_POST['action'] ?? '') === 'vote') {
  // Le vote est réservé aux utilisateurs connectés.
  if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
  }
  $userId = (int) $_SESSION['user_id'];
  $s = $db->prepare(
    'INSERT INTO votes (dish_id, user_id)
     VALUES (?, ?)
     ON CONFLICT (user_id, dish_id) DO NOTHING'
  );
  $s->execute([$id, $userId]);
  if ($s->rowCount() === 0) {
    header('Location: /plat.php?id=' . $id . '&deja=1');
    exit;
  }
} elseif (($_POST['action'] ?? '') === 'rate') {
    // Un avis est réservé aux utilisateurs connectés.
  if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
  }
  $userId = (int) $_SESSION['user_id'];
  $score = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT);
  $nick = trim($_POST['nickname'] ?? '');
  $comment = trim($_POST['comment'] ?? '');
  if (!$score || $score < 1 || $score > 5 || $nick === '' || mb_strlen($nick, 'UTF-8') > 60 || $comment === '' || mb_strlen($comment, 'UTF-8') > 1000) {
    http_response_code(422);
    exit('Vérifie les champs de ton avis.');
  }
    // Vérifie si l'utilisateur a déjà un avis actif pour ce plat.
  $s = $db->prepare(
    "SELECT id FROM ratings
     WHERE user_id = ?
       AND dish_id = ?
       AND status IN ('pending', 'published')"
  );
  $s->execute([$userId, $id]);
  if ($s->fetch()) {
    header('Location: /plat.php?id=' . $id . '&deja=1');
    exit;
  }
  $s = $db->prepare(
    "INSERT INTO ratings (dish_id, user_id, nickname, score, comment, status)
    VALUES (?, ?, ?, ?, ?, 'pending')"
  );
  $s->execute([$id, $userId, $nick, $score, $comment]);
} else {
  http_response_code(400);
  exit('Action inconnue.');
}
header('Location: /plat.php?id=' . $id . '&ok=1');
exit;
