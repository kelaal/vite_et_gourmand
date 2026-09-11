<?php
/**
 * Action Contact - Vite & Gourmand
 * Traitement POST : validation, envoi email notification équipe
 */

session_start();
require_once __DIR__ . '/../config/require_auth.php';

// Vérification méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.php');
    exit;
}

// Vérification CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    $_SESSION['contact_errors'] = ['global' => 'Token de sécurité invalide. Veuillez réessayer.'];
    $_SESSION['contact_old_data'] = $_POST;
    header('Location: ../contact.php');
    exit;
}

// Récupération et nettoyage
$data = [
    'sujet' => trim($_POST['sujet'] ?? ''),
    'email' => strtolower(trim($_POST['email'] ?? '')),
    'message' => trim($_POST['message'] ?? '')
];

$errors = [];

// Validation sujet
$sujetsValides = ['devis', 'menu', 'allergene', 'livraison', 'reclamation', 'autre'];
if (empty($data['sujet']) || !in_array($data['sujet'], $sujetsValides, true)) {
    $errors['sujet'] = 'Veuillez choisir un sujet valide.';
}

// Validation email
if (empty($data['email'])) {
    $errors['email'] = 'L\'adresse email est obligatoire.';
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format d\'email invalide.';
}

// Validation message
if (empty($data['message'])) {
    $errors['message'] = 'Le message est obligatoire.';
} elseif (strlen($data['message']) < 20) {
    $errors['message'] = 'Le message doit contenir au moins 20 caractères.';
} elseif (strlen($data['message']) > 5000) {
    $errors['message'] = 'Le message est trop long (max 5000 caractères).';
}

// Si erreurs, retour formulaire
if (!empty($errors)) {
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_old_data'] = $data;
    header('Location: ../contact.php');
    exit;
}

// Sujets pour email
$sujetLabels = [
    'devis' => 'Demande de devis / Commande',
    'menu' => 'Question sur un menu',
    'allergene' => 'Allergènes / Régimes spécifiques',
    'livraison' => 'Livraison / Zone de couverture',
    'reclamation' => 'Réclamation / Suivi commande',
    'autre' => 'Autre'
];

$sujetEmail = $sujetLabels[$data['sujet']] ?? 'Contact site web';

// Construction email
$to = 'contact@viteetgourmand.fr'; // Email équipe
$subject = "[Vite & Gourmand] Contact : $sujetEmail";

$emailBody = "
Nouveau message depuis le formulaire de contact du site Vite & Gourmand.

--- DÉTAILS ---
Sujet : $sujetEmail
Email expéditeur : {$data['email']}
Date : " . date('d/m/Y à H:i:s') . "

--- MESSAGE ---
{$data['message']}

---
Envoyé depuis : https://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/contact.php
";

$headers = [
    'From' => 'noreply@viteetgourmand.fr',
    'Reply-To' => $data['email'],
    'X-Mailer' => 'PHP/' . phpversion(),
    'Content-Type' => 'text/plain; charset=UTF-8'
];

// Envoi email (simulation pour développement - à remplacer par PHPMailer en production)
$emailSent = false;
$emailError = '';

try {
    // En développement, on log juste dans error_log
    // En production : décommenter mail() ou utiliser PHPMailer
    if (ini_get('display_errors')) {
        // Mode développement : log seulement
        error_log("=== EMAIL CONTACT ===\nTo: $to\nSubject: $subject\nHeaders: " . print_r($headers, true) . "\nBody:\n$emailBody\n====================");
        $emailSent = true;
    } else {
        // Production : envoi réel
        $headerString = "From: {$headers['From']}\r\n";
        $headerString .= "Reply-To: {$headers['Reply-To']}\r\n";
        $headerString .= "X-Mailer: {$headers['X-Mailer']}\r\n";
        $headerString .= "Content-Type: {$headers['Content-Type']}\r\n";

        $emailSent = mail($to, $subject, $emailBody, $headerString);
    }
} catch (Exception $e) {
    $emailError = $e->getMessage();
    $emailSent = false;
}

if ($emailSent) {
    $_SESSION['contact_success'] = true;
    header('Location: ../contact.php');
    exit;
} else {
    $errors['global'] = 'Erreur lors de l\'envoi du message. Veuillez réessayer ou nous contacter directement par téléphone au 05 56 00 00 00.';
    if ($emailError) {
        error_log("Erreur envoi email contact: $emailError");
    }
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_old_data'] = $data;
    header('Location: ../contact.php');
    exit;
}