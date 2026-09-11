<?php
/**
 * Action Toggle Menu Actif/Inactif - Employé/Admin
 * Retourne JSON pour AJAX
 */

header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/MenuRepository.php';

requireEmploye();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Token invalide']);
    exit;
}

$menuId = (int)($_POST['menu_id'] ?? 0);
$isActive = (int)($_POST['is_active'] ?? 0);

if (!$menuId || !in_array($isActive, [0, 1], true)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$menuRepo = new MenuRepository();
$pdo = $menuRepo->getPdo();

$stmt = $pdo->prepare("UPDATE menu SET is_active = :active WHERE menu_id = :id");
$result = $stmt->execute([':active' => $isActive, ':id' => $menuId]);

if ($result) {
    echo json_encode(['success' => true, 'message' => $isActive ? 'Menu activé' : 'Menu désactivé']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
}