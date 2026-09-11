<?php
/**
 * Action Modération Avis - Employé/Admin
 */

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/AvisRepository.php';

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

$avisId = (int)($_POST['avis_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$avisId || !in_array($action, ['valider', 'refuser'], true)) {
    setFlashError('Données invalides.');
    header('Location: ../../employe/dashboard.php');
    exit;
}

$nouveauStatut = $action === 'valider' ? 'VALIDE' : 'REFUSE';

$avisRepo = new AvisRepository();
$success = $avisRepo->updateStatut($avisId, $nouveauStatut);

if ($success) {
    setFlashSuccess('Avis ' . ($action === 'valider' ? 'validé' : 'refusé') . ' avec succès.');
} else {
    setFlashError('Erreur lors de la modération.');
}

header('Location: ../../employe/dashboard.php');
exit;