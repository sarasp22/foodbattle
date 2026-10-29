<?php
require_once __DIR__ . '/includes/auth.php';
require 'includes/functions.php';
require 'includes/db.php';

check_post();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit('Identifiant invalide.');
}

$s = $db->prepare('DELETE FROM establishments WHERE id = ?');
$s->execute([$id]);

if ($s->rowCount() === 0) {
    http_response_code(404);
    exit('Établissement introuvable.');
}

header('Location: /etablissements.php?deleted=1');
exit;
