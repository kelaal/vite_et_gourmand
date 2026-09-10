<?php
/**
 * Action de Déconnexion - Vite & Gourmand
 * Destruction session, nettoyage cookies, redirection accueil
 */

session_start();

// Suppression du cookie "se souvenir de moi" si présent
if (isset($_COOKIE['remember_token'])) {
    // TODO: Invalider le token en base (table remember_tokens)
    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']),
        'samesite' => 'Lax'
    ]);
}

// Destruction de toutes les variables de session
$_SESSION = [];

// Destruction du cookie de session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destruction de la session côté serveur
session_destroy();

// Redirection vers l'accueil avec message
header('Location: ../index.php?deco=1');
exit;