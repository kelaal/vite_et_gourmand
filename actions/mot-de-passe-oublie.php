<?php
/**
 * Action Mot de Passe Oublié - Vite & Gourmand
 * Génère un token de réinitialisation, l'enregistre en base, envoie l'email
 */

session_start();
require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/UtilisateurRepository.php';
require_once __DIR__ . '/../config/database.php';

// Vérification méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../mot-de-passe-oublie.php');
    exit;
}

// Vérification CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    $_SESSION['forgot_errors'] = ['global' => 'Token de sécurité invalide.'];
    $_SESSION['forgot_old_email'] = $_POST['email'] ?? '';
    header('Location: ../mot-de-passe-oublie.php');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$errors = [];

// Validation email
if (empty($email)) {
    $errors['email'] = 'L\'adresse email est obligatoire.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format d\'email invalide.';
}

if (!empty($errors)) {
    $_SESSION['forgot_errors'] = $errors;
    $_SESSION['forgot_old_email'] = $email;
    header('Location: ../mot-de-passe-oublie.php');
    exit;
}

// Recherche utilisateur
$userRepo = new UtilisateurRepository();
$user = $userRepo->findByEmail($email);

// Sécurité : ne pas révéler si l'email existe ou non (réponse identique)
$genericSuccess = true;

// Si utilisateur trouvé et actif, générer token et envoyer email
if ($user && $user['is_active']) {
    // Générer token sécurisé (64 caractères hex = 32 bytes)
    $resetToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 heure

    // Stockage en base
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE utilisateur
        SET reset_token = :token, reset_token_expires_at = :expires
        WHERE utilisateur_id = :id
    ");
    $stmt->execute([
        ':token' => $resetToken,
        ':expires' => $expiresAt,
        ':id' => $user['utilisateur_id']
    ]);

    // TODO: Envoi email réel avec PHPMailer
    // sendResetPasswordEmail($user['email'], $user['prenom'], $resetToken);

    // Log pour développement (à supprimer en production)
    error_log("RESET TOKEN for {$user['email']}: $resetToken (expires: $expiresAt)");
}

$_SESSION['forgot_success'] = true;
header('Location: ../mot-de-passe-oublie.php');
exit;