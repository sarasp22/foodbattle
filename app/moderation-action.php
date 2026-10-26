<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

check_post();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';

if (!$id || !in_array($status, ['published', 'rejected'], true)) {
    http_response_code(400);
    exit('Demande invalide.');
}

$stmt = $db->prepare(
    'UPDATE ratings SET status = ? WHERE id = ? AND status = ?'
);
$stmt->execute([$status, $id, 'pending']);

header('Location: /moderation.php');
exit;