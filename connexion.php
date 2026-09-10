<?php
/**
 * Page de Connexion - Vite & Gourmand
 * ECF : Authentification utilisateur/employé/administrateur
 */

require_once __DIR__ . '/config/require_auth.php';

// Si déjà connecté, rediriger vers l'espace approprié
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    $redirect = match ($role) {
        'administrateur' => 'admin/dashboard.php',
        'employe' => 'employe/dashboard.php',
        default => 'espace.php'
    };
    header('Location: ' . $redirect);
    exit;
}

// Récupération de l'URL de redirection après connexion
$redirectUrl = $_GET['redirect'] ?? '';
$redirectUrl = urldecode($redirectUrl);

// Validation de l'URL de redirection (sécurité : éviter les redirections externes)
$allowedPaths = ['index.php', 'menus.php', 'menu-detail.php', 'commander.php', 'espace.php', 'contact.php', 'admin/dashboard.php', 'employe/dashboard.php'];
$safeRedirect = 'index.php';
if ($redirectUrl) {
    $parsed = parse_url($redirectUrl);
    $path = $parsed['path'] ?? '';
    $path = ltrim($path, '/');
    if (in_array($path, $allowedPaths, true) || str_starts_with($path, 'admin/') || str_starts_with($path, 'employe/')) {
        $safeRedirect = $redirectUrl;
    }
}

$csrfToken = generateCsrfToken();
$errors = [];
$oldEmail = '';

// Traitement des erreurs passées en session (depuis action/connexion.php)
if (!empty($_SESSION['auth_errors'])) {
    $errors = $_SESSION['auth_errors'];
    $oldEmail = $_SESSION['auth_old_email'] ?? '';
    unset($_SESSION['auth_errors'], $_SESSION['auth_old_email']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connexion à votre espace Vite & Gourmand - Traiteur gastronomique à Bordeaux">
    <title>Connexion | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'connexion';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-auth">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="index.php" class="auth-logo" aria-label="Retour à l'accueil">
                        <img src="public/img/logo.svg" alt="Vite & Gourmand" width="160" height="44">
                    </a>
                    <h1>Connexion</h1>
                    <p class="auth-subtitle">Accédez à votre espace personnel</p>
                </div>

                <!-- Messages d'erreur globaux -->
                <?php if (!empty($errors['global'])): ?>
                    <div class="alert alert--error" role="alert">
                        <?= htmlspecialchars($errors['global']) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire de connexion -->
                <form action="actions/connexion.php" method="POST" class="auth-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($safeRedirect) ?>">

                    <div class="form-group">
                        <label for="email">Adresse email <span class="required" aria-hidden="true">*</span></label>
                        <input type="email"
                            id="email"
                            name="email"
                            class="form-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($oldEmail) ?>"
                            placeholder="vous@exemple.com"
                            required
                            autocomplete="email"
                            aria-describedby="<?= isset($errors['email']) ? 'email-error' : 'email-hint' ?>">
                        <?php if (isset($errors['email'])): ?>
                            <span class="form-error" id="email-error" role="alert"><?= htmlspecialchars($errors['email']) ?></span>
                        <?php else: ?>
                            <span class="form-hint" id="email-hint">Votre email utilisé lors de l'inscription</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe <span class="required" aria-hidden="true">*</span></label>
                        <div class="password-wrapper">
                            <input type="password"
                                id="password"
                                name="password"
                                class="form-input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                                aria-describedby="<?= isset($errors['password']) ? 'password-error' : 'password-hint' ?>">
                            <button type="button" class="password-toggle" aria-label="Afficher/Masquer le mot de passe" aria-pressed="false">
                                <span class="eye-open" aria-hidden="true">👁</span>
                                <span class="eye-closed" aria-hidden="true" style="display:none;">🙈</span>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="form-error" id="password-error" role="alert"><?= htmlspecialchars($errors['password']) ?></span>
                        <?php else: ?>
                            <span class="form-hint" id="password-hint">Minimum 10 caractères avec majuscule, minuscule, chiffre et spécial</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group form-group--remember">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember" id="remember" value="1">
                            <span class="checkmark"></span>
                            Se souvenir de moi
                        </label>
                        <a href="mot-de-passe-oublie.php" class="link-forgot">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn btn--primary btn--fullwidth btn--auth-submit">
                        <span>Se connecter</span>
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Pas encore de compte ? <a href="inscription.php">Créer mon compte</a></p>
                    <p class="auth-divider"><span>ou</span></p>
                    <p><a href="index.php">← Retour à l'accueil</a></p>
                </div>
            </div>

            <!-- Info rassurance -->
            <div class="auth-reassurance" aria-hidden="true">
                <div class="reassurance-item">
                    <span class="icon">🔒</span>
                    <span>Connexion sécurisée</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">🛡️</span>
                    <span>Données protégées RGPD</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">⚡</span>
                    <span>Accès instantané</span>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Script toggle password -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.querySelector('.password-toggle');
            const input = document.getElementById('password');
            const eyeOpen = toggle?.querySelector('.eye-open');
            const eyeClosed = toggle?.querySelector('.eye-closed');

            if (toggle && input && eyeOpen && eyeClosed) {
                toggle.addEventListener('click', function() {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    eyeOpen.style.display = isPassword ? 'none' : 'inline';
                    eyeClosed.style.display = isPassword ? 'inline' : 'none';
                    toggle.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
                });
            }
        });
    </script>
</body>

</html>