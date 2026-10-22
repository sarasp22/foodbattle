<?php
// Vérifie si aucune session n'est active, puis démarre la session
if (session_status() === PHP_SESSION_NONE) session_start();

// Importe les fonctions utilitaires et la connexion à la base de données
require 'includes/functions.php';
require 'includes/db.php';

// Vérifie que la requête est bien de type POST
check_post();

// Récupère et valide l'identifiant (ID) reçu via POST en tant qu'entier
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Si l'ID est invalide ou absent, renvoie un code HTTP 400 et arrête le script
if (!$id) {
    http_response_code(400);
    exit('Identifiant invalide.');
}

// Prépare la requête SQL pour supprimer le plat correspondant à l'ID
$s = $db->prepare('DELETE FROM dishes WHERE id = ?');
$s->execute([$id]);

// Si aucune ligne n'a été supprimée, le plat n'existe pas : renvoie un code 404 et arrête le script
if ($s->rowCount() === 0) {
    http_response_code(404);
    exit('Plat introuvable.');
}

// Redirige l'utilisateur vers le tableau de bord avec un paramètre indiquant la suppression
header('Location: /dashboard.php?deleted=1');
exit;
