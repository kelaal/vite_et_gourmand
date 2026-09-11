<?php
/**
 * Action Annulation Commande - Employé (avec motif et contact obligatoires)
 */

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/CommandeRepository.php';

requireEmploye();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../employe/dashboard.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashError('Token invalide.');
    header('Location: ../../employe/dashboard.php');
    exit;
}

$commandeId = (int)($_POST['commande_id'] ?? 0);
$motif = trim($_POST['motif_annulation'] ?? '');
$contactMode = $_POST['contact_mode_annulation'] ?? '';
$employeId = getCurrentUserId();

if (!$commandeId || empty($motif) || empty($contactMode)) {
    setFlashError('Motif et mode de contact obligatoires.');
    header('Location: ../../employe/dashboard.php');
    exit;
}

if (!in_array($contactMode, ['gsm', 'mail'], true)) {
    setFlashError('Mode de contact invalide.');
    header('Location: ../../employe/dashboard.php');
    exit;
}

$commandeRepo = new CommandeRepository();
$success = $commandeRepo->annulerParEmploye($commandeId, $motif, $contactMode, $employeId);

if ($success) {
    setFlashSuccess('Commande annulée avec succès.');
} else {
    setFlashError('Erreur lors de l\'annulation.');
}

header('Location: ../../employe/dashboard.php');
exit;