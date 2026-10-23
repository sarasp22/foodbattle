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
if (($_POST['action']??'')==='vote'){
    $visitor=hash('sha256',session_id());
    $s=$db->prepare('INSERT INTO votes(dish_id,visitor_key) VALUES(?,?) ON CONFLICT (dish_id,visitor_key) DO NOTHING');
    $s->execute([$id,$visitor]);
    if($s->rowCount()===0){header('Location: /plat.php?id='.$id.'&deja=1');exit;}
}elseif (($_POST['action'] ?? '') === 'rate') {
  $score = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT);
  $nick = trim($_POST['nickname'] ?? '');
  $comment = trim($_POST['comment'] ?? '');
  if (!$score || $score < 1 || $score > 5 || $nick === '' || mb_strlen($nick, 'UTF-8') > 60 || $comment === '' || mb_strlen($comment, 'UTF-8') > 1000) {
    http_response_code(422);
    exit('Vérifie les champs de ton avis.');
  }
  $s = $db->prepare("INSERT INTO ratings(dish_id,nickname,score,comment,status) VALUES(?,?,?,?,'published')");
  $s->execute([$id, $nick, $score, $comment]);
} else {
  http_response_code(400);
  exit('Action inconnue.');
}
header('Location: /plat.php?id=' . $id . '&ok=1');
exit;
