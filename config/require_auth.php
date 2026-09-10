<?php
/**
 * Middleware d'authentification et d'autorisation
 * Projet ECF Vite & Gourmand
 */

// Démarrage session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['utilisateur_id']) && !empty($_SESSION['role']);
}

/**
 * Récupère l'ID de l'utilisateur connecté
 * @return int|null
 */
function getCurrentUserId(): ?int {
    return $_SESSION['utilisateur_id'] ?? null;
}

/**
 * Récupère le rôle de l'utilisateur connecté
 * @return string|null
 */
function getCurrentUserRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Récupère le prénom de l'utilisateur connecté
 * @return string
 */
function getCurrentUserPrenom(): string {
    return $_SESSION['prenom'] ?? '';
}

/**
 * Exige une connexion active
 * Redirige vers la page de connexion si non connecté
 *
 * @param string $redirectUrl URL de redirection après connexion (optionnel)
 * @return void
 */
function requireAuth(string $redirectUrl = ''): void {
    if (!isLoggedIn()) {
        $target = $redirectUrl ?: $_SERVER['REQUEST_URI'];
        header('Location: connexion.php?redirect=' . urlencode($target));
        exit;
    }
}

/**
 * Exige un rôle spécifique (ou l'un des rôles autorisés)
 * Redirige vers l'accueil avec erreur si rôle non autorisé
 *
 * @param string|array $roles Rôle(s) autorisé(s) : 'utilisateur', 'employe', 'administrateur'
 * @return void
 */
function requireRole(string|array $roles): void {
    requireAuth(); // Vérifie d'abord la connexion

    $userRole = getCurrentUserRole();
    $allowedRoles = is_array($roles) ? $roles : [$roles];

    if (!in_array($userRole, $allowedRoles, true)) {
        // Stocker message d'erreur en session
        $_SESSION['flash_error'] = 'Accès refusé : droits insuffisants.';
        header('Location: index.php');
        exit;
    }
}

/**
 * Exige le rôle employé ou administrateur (espace équipe)
 * @return void
 */
function requireEmploye(): void {
    requireRole(['employe', 'administrateur']);
}

/**
 * Exige le rôle administrateur (espace admin)
 * @return void
 */
function requireAdmin(): void {
    requireRole('administrateur');
}

/**
 * Génère un token CSRF et le stocke en session
 * @return string
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie la validité d'un token CSRF
 * @param string $token Token reçu en POST
 * @return bool
 */
function verifyCsrfToken(string $token): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Affiche les messages flash (succès/erreur) et les supprime
 * @return void
 */
function displayFlashMessages(): void {
    if (!empty($_SESSION['flash_success'])) {
        echo '<div class="alert alert--success" role="alert">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    if (!empty($_SESSION['flash_error'])) {
        echo '<div class="alert alert--error" role="alert">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
}

/**
 * Définit un message flash de succès
 * @param string $message
 * @return void
 */
function setFlashSuccess(string $message): void {
    $_SESSION['flash_success'] = $message;
}

/**
 * Définit un message flash d'erreur
 * @param string $message
 * @return void
 */
function setFlashError(string $message): void {
    $_SESSION['flash_error'] = $message;
}

/**
 * Vérifie la force du mot de passe (règle ECF : min 10 car, 1 maj, 1 min, 1 chiffre, 1 spécial)
 * @param string $password
 * @return array ['valid' => bool, 'errors' => string[]]
 */
function validatePasswordStrength(string $password): array {
    $errors = [];

    if (strlen($password) < 10) {
        $errors[] = 'Le mot de passe doit contenir au moins 10 caractères.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins une minuscule.';
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
    }
    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Hache un mot de passe avec ARGON2ID (recommandé PHP 8.2+)
 * @param string $password
 * @return string
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_ARGON2ID);
}

/**
 * Vérifie un mot de passe contre son hash
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}