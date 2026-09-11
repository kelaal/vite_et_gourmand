<?php
/**
 * Action Toggle Employé Actif/Inactif - Administrateur
 * Retourne JSON pour AJAX
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

$userId = (int)($_POST['user_id'] ?? 0);
$isActive = (int)($_POST['is_active'] ?? 0);

if (!$userId || !in_array($isActive, [0, 1], true)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

// Empêcher l'admin de se bloquer lui-même
if ($userId === getCurrentUserId()) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre compte.']);
    exit;
}

$userRepo = new UtilisateurRepository();
$success = $userRepo->toggleActive($userId, (bool)$isActive);

if ($success) {
    echo json_encode(['success' => true, 'message' => $isActive ? 'Employé activé' : 'Employé bloqué']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
}