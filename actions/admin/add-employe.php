<?php
/**
 * Action Création Compte Employé - Administrateur
 * Retourne JSON avec mot de passe généré
 */

header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/UtilisateurRepository.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Token invalide']);
    exit;
}

$data = [
    'nom' => trim($_POST['nom'] ?? ''),
    'prenom' => trim($_POST['prenom'] ?? ''),
    'email' => strtolower(trim($_POST['email'] ?? '')),
    'gsm' => trim($_POST['gsm'] ?? ''),
    'password' => $_POST['password'] ?? ''
];

$errors = [];

// Validation
if (empty($data['nom'])) $errors['nom'] = 'Nom obligatoire.';
if (empty($data['prenom'])) $errors['prenom'] = 'Prénom obligatoire.';
if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email invalide.';
if (empty($data['gsm']) || !preg_match('/^0[1-9](\s?\d{2}){4}$/', $data['gsm'])) $errors['gsm'] = 'Téléphone invalide (format 06 XX XX XX XX).';
if (empty($data['password'])) $errors['password'] = 'Mot de passe obligatoire.';

$pwdVal = validatePasswordStrength($data['password']);
if (!$pwdVal['valid']) $errors['password'] = implode(' ', $pwdVal['errors']);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides', 'errors' => $errors]);
    exit;
}

// Vérifier unicité email
$userRepo = new UtilisateurRepository();
if ($userRepo->findByEmail($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.', 'errors' => ['email' => 'Email déjà utilisé']]);
    exit;
}

// Hachage et création
$hashed = hashPassword($data['password']);
$userId = $userRepo->create([
    'nom' => $data['nom'],
    'prenom' => $data['prenom'],
    'email' => $data['email'],
    'password' => $hashed,
    'gsm' => $data['gsm'],
    'adresse_postale' => '', // Employé : adresse optionnelle
    'role' => 'employe',
    'is_active' => 1
]);

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Erreur création compte.']);
    exit;
}

// TODO: Envoyer email notification à l'employé (SANS le mot de passe)
// sendEmployeNotification($data['email'], $data['prenom']);

echo json_encode([
    'success' => true,
    'message' => 'Compte employé créé avec succès',
    'password' => $data['password'], // Retourne le MDP en clair UNE SEULE FOIS
    'user_id' => $userId
]);