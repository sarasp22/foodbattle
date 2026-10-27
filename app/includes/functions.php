<?php
function h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function csrf() {
    if (empty($_SESSION['token'])) $_SESSION['token'] = bin2hex(random_bytes(24));
    return $_SESSION['token'];
}
function check_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals(csrf(), $_POST['token'] ?? '')) {
        http_response_code(403); exit('Requête refusée.');
    }
}

function image_for($category, $image_url = null) {
    if (!empty($image_url)) {
        return $image_url;
    }
    $photos = ['burger'=>'1639557', 'tacos'=>'4958792', 'pizza'=>'315755'];
    $id = $photos[$category] ?? '1639557';
    return 'https://images.pexels.com/photos/'.$id.'/pexels-photo-'.$id.'.jpeg?auto=compress&cs=tinysrgb&w=800';
}

function euro($n) { return number_format((float)$n, 2, ',', ' ') . ' €'; }

function upload_to_cloudinary(array $file, ?string &$error = null): ?string {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

     if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $error = "L'image dépasse la taille maximale autorisée par le serveur (2 Mo par défaut, ajoutez app/.htaccess).";
        } else {
            $error = "Erreur lors du téléchargement de l'image (code " . (int)$file['error'] . ").";
        }
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $error = "L'image ne doit pas dépasser 5 Mo.";
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowed, true)) {
        $error = "Format d'image non autorisé (JPG, PNG ou WebP uniquement).";
        return null;
    }

    $cloudName = getenv('CLOUDINARY_CLOUD_NAME');
    $apiKey    = getenv('CLOUDINARY_API_KEY');
    $apiSecret = getenv('CLOUDINARY_API_SECRET');

    if (!$cloudName || !$apiKey || !$apiSecret) {
        $error = "Configuration Cloudinary manquante dans le fichier .env.";
        return null;
    }

    $timestamp = time();
    $folder    = 'foodbattle';
    $signature = sha1("folder={$folder}&timestamp={$timestamp}{$apiSecret}");

    $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
    $postFields = [
        'file'      => new CURLFile($file['tmp_name'], $mime, $file['name']),
        'api_key'   => $apiKey,
        'timestamp' => $timestamp,
        'folder'    => $folder,
        'signature' => $signature,
    ];

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $httpCode >= 400) {
        $error = "Impossible d'envoyer l'image sur Cloudinary.";
        return null;
    }

    $data = json_decode($response, true);
    if ($response === false || $httpCode >= 400) {
        $cloudinaryMsg = $data['error']['message'] ?? 'vérifiez vos clés dans .env';
        $error = "Erreur Cloudinary : " . $cloudinaryMsg;
        return null;
    }

    return $data['secure_url'] ?? null;
}
