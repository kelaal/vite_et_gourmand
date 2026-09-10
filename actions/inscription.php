<?php
/**
 * Action d'Inscription - Vite & Gourmand
 * Traitement POST : validation données, hachage password (ARGON2ID), insertion via UtilisateurRepository, email bienvenue
 */

session_start();
require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/UtilisateurRepository.php';

// Vérification méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../inscription.php');
    exit;
}

// Vérification CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    $_SESSION['register_errors'] = ['global' => 'Token de sécurité invalide. Veuillez réessayer.'];
    $_SESSION['register_old_data'] = $_POST;
    header('Location: ../inscription.php');
    exit;
}

// Récupération et nettoyage des données
$data = [
    'nom' => trim($_POST['nom'] ?? ''),
    'prenom' => trim($_POST['prenom'] ?? ''),
    'email' => strtolower(trim($_POST['email'] ?? '')),
    'gsm' => trim($_POST['gsm'] ?? ''),
    'adresse_postale' => trim($_POST['adresse_postale'] ?? ''),
    'password' => $_POST['password'] ?? '',
    'password_confirm' => $_POST['password_confirm'] ?? '',
    'cgv' => !empty($_POST['cgv'])
];

$errors = [];

// Validation Nom
if (empty($data['nom'])) {
    $errors['nom'] = 'Le nom est obligatoire.';
} elseif (strlen($data['nom']) > 100) {
    $errors['nom'] = 'Le nom ne peut pas dépasser 100 caractères.';
} elseif (!preg_match('/^[\p{L}\s\-\']+$/u', $data['nom'])) {
    $errors['nom'] = 'Le nom contient des caractères invalides.';
}

// Validation Prénom
if (empty($data['prenom'])) {
    $errors['prenom'] = 'Le prénom est obligatoire.';
} elseif (strlen($data['prenom']) > 100) {
    $errors['prenom'] = 'Le prénom ne peut pas dépasser 100 caractères.';
} elseif (!preg_match('/^[\p{L}\s\-\']+$/u', $data['prenom'])) {
    $errors['prenom'] = 'Le prénom contient des caractères invalides.';
}

// Validation Email
if (empty($data['email'])) {
    $errors['email'] = 'L\'adresse email est obligatoire.';
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format d\'email invalide.';
} elseif (strlen($data['email']) > 191) {
    $errors['email'] = 'L\'email est trop long (max 191 caractères).';
}

// Validation GSM (format français)
if (empty($data['gsm'])) {
    $errors['gsm'] = 'Le numéro de téléphone est obligatoire.';
} elseif (!preg_match('/^0[1-9](\s?\d{2}){4}$/', $data['gsm'])) {
    $errors['gsm'] = 'Format invalide. Utilisez 06 XX XX XX XX ou 07 XX XX XX XX.';
}

// Validation Adresse
if (empty($data['adresse_postale'])) {
    $errors['adresse_postale'] = 'L\'adresse postale est obligatoire.';
} elseif (strlen($data['adresse_postale']) > 255) {
    $errors['adresse_postale'] = 'L\'adresse est trop longue (max 255 caractères).';
}

// Validation Mot de passe (règle ECF : min 10 car, maj, min, chiffre, spécial)
$pwdValidation = validatePasswordStrength($data['password']);
if (!$pwdValidation['valid']) {
    $errors['password'] = implode(' ', $pwdValidation['errors']);
}

// Confirmation mot de passe
if (empty($data['password_confirm'])) {
    $errors['password_confirm'] = 'La confirmation du mot de passe est obligatoire.';
} elseif ($data['password'] !== $data['password_confirm']) {
    $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
}

// Validation CGV
if (!$data['cgv']) {
    $errors['cgv'] = 'Vous devez accepter les CGV et la politique de confidentialité.';
}

// Si erreurs, retour au formulaire
if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old_data'] = $data;
    header('Location: ../inscription.php');
    exit;
}

// Vérification email unique en base
$userRepo = new UtilisateurRepository();
$existingUser = $userRepo->findByEmail($data['email']);

if ($existingUser) {
    $errors['email'] = 'Cette adresse email est déjà utilisée.';
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old_data'] = $data;
    header('Location: ../inscription.php');
    exit;
}

// Hachage du mot de passe avec ARGON2ID
$hashedPassword = hashPassword($data['password']);

// Création de l'utilisateur (rôle 'utilisateur' par défaut)
$userId = $userRepo->create([
    'nom' => $data['nom'],
    'prenom' => $data['prenom'],
    'email' => $data['email'],
    'password' => $hashedPassword,
    'gsm' => $data['gsm'],
    'adresse_postale' => $data['adresse_postale'],
    'role' => 'utilisateur',
    'is_active' => 1
]);

if (!$userId) {
    $errors['global'] = 'Erreur lors de la création du compte. Veuillez réessayer.';
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old_data'] = $data;
    header('Location: ../inscription.php');
    exit;
}

// TODO: Envoi email de bienvenue
// sendWelcomeEmail($data['email'], $data['prenom']);

// Connexion automatique après inscription
$_SESSION['utilisateur_id'] = $userId;
$_SESSION['nom'] = $data['nom'];
$_SESSION['prenom'] = $data['prenom'];
$_SESSION['email'] = $data['email'];
$_SESSION['gsm'] = $data['gsm'];
$_SESSION['adresse_postale'] = $data['adresse_postale'];
$_SESSION['role'] = 'utilisateur';
$_SESSION['date_connexion'] = date('Y-m-d H:i:s');

// Régénération ID session
session_regenerate_id(true);

setFlashSuccess('Bienvenue ' . htmlspecialchars($data['prenom']) . ' ! Votre compte a été créé avec succès.');

// Redirection vers l'espace client
header('Location: ../espace.php');
exit;