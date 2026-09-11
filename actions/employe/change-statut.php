<?php
/**
 * Action Changement Statut Commande - Employé
 */

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/CommandeRepository.php';

// Protection employé/admin
requireEmploye();

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../employe/dashboard.php');
    exit;
}

// CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashError('Token invalide.');
    header('Location: ../../employe/dashboard.php');
    exit;
}

$commandeId = (int)($_POST['commande_id'] ?? 0);
$nouveauStatut = $_POST['nouveau_statut'] ?? '';
$motif = trim($_POST['motif_annulation'] ?? '');
$contactMode = $_POST['contact_mode_annulation'] ?? '';
$employeId = getCurrentUserId();

if (!$commandeId || !$nouveauStatut) {
    setFlashError('Données manquantes.');
    header('Location: ../../employe/dashboard.php');
    exit;
}

$commandeRepo = new CommandeRepository();

// Validation statut valide
$statutsValides = ['en attente', 'accepte', 'en preparation', 'en cours de livraison', 'livre', 'en attente du retour de materiel', 'terminee', 'annulee'];
if (!in_array($nouveauStatut, $statutsValides, true)) {
    setFlashError('Statut invalide.');
    header('Location: ../../employe/dashboard.php');
    exit;
}

// Si annulation, motif et mode contact obligatoires (règle ECF)
if ($nouveauStatut === 'annulee') {
    if (empty($motif)) {
        setFlashError('Le motif d\'annulation est obligatoire.');
        header('Location: ../../employe/dashboard.php');
        exit;
    }
    if (empty($contactMode) || !in_array($contactMode, ['gsm', 'mail'], true)) {
        setFlashError('Le mode de contact (GSM/Email) est obligatoire.');
        header('Location: ../../employe/dashboard.php');
        exit;
    }
    // Utiliser la méthode d'annulation spécifique
    $success = $commandeRepo->annulerParEmploye($commandeId, $motif, $contactMode, $employeId);
} else {
    // Changement de statut normal
    $success = $commandeRepo->updateStatut($commandeId, $nouveauStatut, $employeId);
}

if ($success) {
    setFlashSuccess('Statut mis à jour avec succès.');
} else {
    setFlashError('Erreur lors de la mise à jour.');
}

header('Location: ../../employe/dashboard.php');
exit;