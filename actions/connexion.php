<?php
/**
 * Action de Connexion - Vite & Gourmand
 * Traitement POST : vérification email, password_verify, session, redirection selon rôle
 */

session_start();
require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/UtilisateurRepository.php';

// Vérification méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../connexion.php');
    exit;
}

// Vérification CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    $_SESSION['auth_errors'] = ['global' => 'Token de sécurité invalide. Veuillez réessayer.'];
    $_SESSION['auth_old_email'] = $_POST['email'] ?? '';
    header('Location: ../connexion.php');
    exit;
}

// Récupération et nettoyage des données
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$redirect = $_POST['redirect'] ?? 'index.php';
$remember = !empty($_POST['remember']);

$errors = [];

// Validation email
if (empty($email)) {
    $errors['email'] = 'L\'adresse email est obligatoire.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format d\'email invalide.';
}

// Validation mot de passe
if (empty($password)) {
    $errors['password'] = 'Le mot de passe est obligatoire.';
}

// Si erreurs de validation, retour au formulaire
if (!empty($errors)) {
    $_SESSION['auth_errors'] = $errors;
    $_SESSION['auth_old_email'] = $email;
    header('Location: ../connexion.php' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// Recherche utilisateur en base
$userRepo = new UtilisateurRepository();
$user = $userRepo->findByEmail($email);

// Vérification utilisateur existe + mot de passe + compte actif
if (!$user) {
    // Délai pour éviter l'énumération d'emails (timing attack protection)
    usleep(200000); // 200ms
    $errors['global'] = 'Identifiants incorrects.';
} elseif (!verifyPassword($password, $user['password'])) {
    $errors['global'] = 'Identifiants incorrects.';
} elseif (!$user['is_active']) {
    $errors['global'] = 'Ce compte a été désactivé. Contactez l\'administration.';
}

// Si erreur d'authentification
if (!empty($errors)) {
    $_SESSION['auth_errors'] = $errors;
    $_SESSION['auth_old_email'] = $email;
    header('Location: ../connexion.php' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// Authentification réussie - Création de la session
$_SESSION['utilisateur_id'] = (int)$user['utilisateur_id'];
$_SESSION['nom'] = $user['nom'];
$_SESSION['prenom'] = $user['prenom'];
$_SESSION['email'] = $user['email'];
$_SESSION['gsm'] = $user['gsm'];
$_SESSION['adresse_postale'] = $user['adresse_postale'];
$_SESSION['role'] = $user['role'];
$_SESSION['date_connexion'] = date('Y-m-d H:i:s');

// Régénération de l'ID de session (sécurité fixation)
session_regenerate_id(true);

// Cookie "Se souvenir de moi" (30 jours)
if ($remember) {
    $rememberToken = bin2hex(random_bytes(32));
    // TODO: Stocker le token en base avec expiration (table remember_tokens)
    // Pour l'instant, cookie simple (à sécuriser en production)
    setcookie('remember_token', $rememberToken, [
        'expires' => time() + (30 * 24 * 60 * 60), // 30 jours
        'path' => '/',
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']), // true si HTTPS
        'samesite' => 'Lax'
    ]);
}

// Message de bienvenue
setFlashSuccess('Bienvenue ' . htmlspecialchars($user['prenom']) . ' ! Vous êtes connecté.');

// Redirection selon le rôle
$targetUrl = match ($user['role']) {
    'administrateur' => '../admin/dashboard.php',
    'employe' => '../employe/dashboard.php',
    default => '../espace.php'
};

// Si URL de redirection fournie et sûre, l'utiliser
if ($redirect && $redirect !== 'index.php') {
    $parsed = parse_url($redirect);
    $path = $parsed['path'] ?? '';
    $path = ltrim($path, '/');
    $allowedPaths = ['espace.php', 'commander.php', 'menu-detail.php', 'menus.php', 'contact.php', 'admin/dashboard.php', 'employe/dashboard.php'];
    if (in_array($path, $allowedPaths, true) || str_starts_with($path, 'admin/') || str_starts_with($path, 'employe/')) {
        $targetUrl = '../' . $redirect;
    }
}

header('Location: ' . $targetUrl);
exit;