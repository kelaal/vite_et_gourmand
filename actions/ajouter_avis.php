<?php
session_start();
require_once __DIR__ . '/../repositories/AvisRepository.php';

// Vérification de l'authentification
if (empty($_SESSION['utilisateur_id'])) {
    header('Location: ../connexion.php?auth_required=1');
    exit;
}

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = filter_input(INPUT_POST, 'note', FILTER_VALIDATE_INT);
    $commentaire = trim($_POST['commentaire'] ?? '');
    $utilisateurId = (int)$_SESSION['utilisateur_id'];

    if ($note && $note >= 1 && $note <= 5 && !empty($commentaire)) {
        $avisRepo = new AvisRepository();
        $success = $avisRepo->create($utilisateurId, $note, $commentaire);

        if ($success) {
            header('Location: ../index.php?avis=succes#avis');
            exit;
        }
    }
}

header('Location: ../index.php#avis');
exit;
