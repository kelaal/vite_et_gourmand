<?php
/**
 * Action Réinitialisation Mot de Passe - Vite & Gourmand
 * Validation token, hachage nouveau MDP, mise à jour base, nettoyage token
 */

session_start();
require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/UtilisateurRepository.php';
require_once __DIR__ . '/../config/database.php';

// Vérification méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../reset-password.php');
    exit;
}

// Vérification CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    $_SESSION['reset_errors'] = ['global' => 'Token de sécurité invalide.'];
    header('Location: ../reset-password.php?token=' . urlencode($_POST['token'] ?? ''));
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

$errors = [];

// Validation token présent
if (empty($token)) {
    $errors['global'] = 'Token manquant.';
}

// Validation nouveau mot de passe
$pwdValidation = validatePasswordStrength($password);
if (!$pwdValidation['valid']) {
    $errors['password'] = implode(' ', $pwdValidation['errors']);
}

// Confirmation
if (empty($passwordConfirm)) {
    $errors['password_confirm'] = 'La confirmation est obligatoire.';
} elseif ($password !== $passwordConfirm) {
    $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
}

// Si erreurs, retour
if (!empty($errors)) {
    $_SESSION['reset_errors'] = $errors;
    header('Location: ../reset-password.php?token=' . urlencode($token));
    exit;
}

// Validation token en base
global $pdo;
$stmt = $pdo->prepare("
    SELECT utilisateur_id
    FROM utilisateur
    WHERE reset_token = :token
    AND reset_token_expires_at > NOW()
");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['reset_errors'] = ['global' => 'Lien invalide ou expiré.'];
    header('Location: ../reset-password.php?token=' . urlencode($token));
    exit;
}

// Hachage nouveau mot de passe
$hashedPassword = hashPassword($password);

// Mise à jour mot de passe + suppression token
$stmt = $pdo->prepare("
    UPDATE utilisateur
    SET password = :password, reset_token = NULL, reset_token_expires_at = NULL
    WHERE utilisateur_id = :id
");
$stmt->execute([
    ':password' => $hashedPassword,
    ':id' => $user['utilisateur_id']
]);

$_SESSION['reset_success'] = true;
header('Location: ../reset-password.php?token=' . urlencode($token));
exit;