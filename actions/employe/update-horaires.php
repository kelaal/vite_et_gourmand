<?php
/**
 * Action Mise à jour Horaires - Employé/Admin
 */

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/HoraireRepository.php';

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

$horaireRepo = new HoraireRepository();
$success = true;

foreach ($_POST as $key => $value) {
    if (str_starts_with($key, 'ouvert_')) {
        $id = (int)substr($key, 7);
        $estOuvert = !empty($value);

        $ouverture = null;
        $fermeture = null;

        if ($estOuvert) {
            $ouvertureKey = 'ouverture_' . $id;
            $fermetureKey = 'fermeture_' . $id;

            $ouverture = $_POST[$ouvertureKey] ?? '';
            $fermeture = $_POST[$fermetureKey] ?? '';

            if (empty($ouverture) || empty($fermeture)) {
                setFlashError('Heures d\'ouverture et fermeture obligatoires pour les jours ouverts.');
                header('Location: ../../employe/dashboard.php#horaires');
                exit;
            }

            // Format HH:MM -> HH:MM:SS
            $ouverture .= ':00';
            $fermeture .= ':00';
        }

        if (!$horaireRepo->update($id, $ouverture, $fermeture, $estOuvert)) {
            $success = false;
        }
    }
}

if ($success) {
    setFlashSuccess('Horaires mis à jour avec succès.');
} else {
    setFlashError('Erreur lors de la mise à jour.');
}

header('Location: ../../employe/dashboard.php#horaires');
exit;