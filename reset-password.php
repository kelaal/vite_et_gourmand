<?php
/**
 * Page Réinitialisation Mot de Passe - Vite & Gourmand
 * ECF : Formulaire nouveau mot de passe via token
 */

require_once __DIR__ . '/config/require_auth.php';

// Si déjà connecté, rediriger
if (isLoggedIn()) {
    header('Location: espace.php');
    exit;
}

// Récupération token depuis URL
$token = $_GET['token'] ?? '';
$errors = [];
$success = false;
$tokenValid = false;

if (empty($token)) {
    $errors['global'] = 'Lien de réinitialisation invalide ou manquant.';
} else {
    // Validation du token en base
    require_once __DIR__ . '/config/database.php';
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT utilisateur_id, prenom, reset_token_expires_at
        FROM utilisateur
        WHERE reset_token = :token
    ");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors['global'] = 'Lien de réinitialisation invalide ou déjà utilisé.';
    } elseif (strtotime($user['reset_token_expires_at']) < time()) {
        $errors['global'] = 'Ce lien a expiré. Veuillez faire une nouvelle demande.';
    } else {
        $tokenValid = true;
    }
}

// Messages depuis session (après POST action/reset-password.php)
if (!empty($_SESSION['reset_errors'])) {
    $errors = array_merge($errors, $_SESSION['reset_errors']);
    unset($_SESSION['reset_errors']);
}
if (!empty($_SESSION['reset_success'])) {
    $success = true;
    unset($_SESSION['reset_success']);
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nouveau mot de passe - Vite & Gourmand">
    <title>Réinitialiser mon mot de passe | Vite & Gourmand</title>

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
                    <h1><?= $success ? 'Mot de passe modifié' : 'Nouveau mot de passe' ?></h1>
                    <p class="auth-subtitle">
                        <?= $success ? 'Votre mot de passe a été réinitialisé avec succès.' : 'Choisissez un nouveau mot de passe sécurisé' ?>
                    </p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert--success" role="alert">
                        <strong>✅ C'est fait !</strong> Votre mot de passe a été mis à jour. Vous pouvez maintenant vous connecter.
                    </div>
                    <div class="auth-footer">
                        <p><a href="connexion.php" class="btn btn--primary">Se connecter</a></p>
                        <p><a href="index.php">← Retour à l'accueil</a></p>
                    </div>

                <?php elseif (!empty($errors['global'])): ?>
                    <div class="alert alert--error" role="alert">
                        <?= htmlspecialchars($errors['global']) ?>
                    </div>
                    <div class="auth-footer">
                        <p><a href="mot-de-passe-oublie.php">← Nouvelle demande</a></p>
                        <p><a href="connexion.php">Retour à la connexion</a></p>
                    </div>

                <?php elseif ($tokenValid): ?>
                    <!-- Formulaire nouveau mot de passe -->
                    <form action="actions/reset-password.php" method="POST" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="form-group">
                            <label for="password">Nouveau mot de passe <span class="required" aria-hidden="true">*</span></label>
                            <div class="password-wrapper">
                                <input type="password"
                                    id="password"
                                    name="password"
                                    class="form-input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="new-password"
                                    aria-describedby="password-hint password-error">
                                <button type="button" class="password-toggle" aria-label="Afficher/Masquer le mot de passe" aria-pressed="false">
                                    <span class="eye-open" aria-hidden="true">👁</span>
                                    <span class="eye-closed" aria-hidden="true" style="display:none;">🙈</span>
                                </button>
                            </div>
                            <div class="password-requirements" id="password-hint" aria-live="polite">
                                <span class="req" data-req="length">10 caractères minimum</span>
                                <span class="req" data-req="upper">1 majuscule</span>
                                <span class="req" data-req="lower">1 minuscule</span>
                                <span class="req" data-req="digit">1 chiffre</span>
                                <span class="req" data-req="special">1 caractère spécial</span>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <span class="form-error" id="password-error" role="alert"><?= htmlspecialchars($errors['password']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirmer le nouveau mot de passe <span class="required" aria-hidden="true">*</span></label>
                            <input type="password"
                                id="password_confirm"
                                name="password_confirm"
                                class="form-input <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                                aria-describedby="<?= isset($errors['password_confirm']) ? 'confirm-error' : 'confirm-hint' ?>">
                            <?php if (isset($errors['password_confirm'])): ?>
                                <span class="form-error" id="confirm-error" role="alert"><?= htmlspecialchars($errors['password_confirm']) ?></span>
                            <?php else: ?>
                                <span class="form-hint" id="confirm-hint">Répétez le même mot de passe</span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn--primary btn--fullwidth btn--auth-submit">
                            <span>Valider le nouveau mot de passe</span>
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p><a href="connexion.php">← Retour à la connexion</a></p>
                    </div>

                <?php endif; ?>
            </div>

            <div class="auth-reassurance" aria-hidden="true">
                <div class="reassurance-item">
                    <span class="icon">🔒</span>
                    <span>Chiffrement ARGON2ID</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">🔐</span>
                    <span>Token à usage unique</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">⏱️</span>
                    <span>Session sécurisée</span>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password
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

            // Validation temps réel force MDP
            const passwordInput = document.getElementById('password');
            const requirements = document.querySelectorAll('.password-requirements .req');

            if (passwordInput && requirements.length) {
                passwordInput.addEventListener('input', function() {
                    const value = this.value;
                    const checks = {
                        length: value.length >= 10,
                        upper: /[A-Z]/.test(value),
                        lower: /[a-z]/.test(value),
                        digit: /\d/.test(value),
                        special: /[\W_]/.test(value)
                    };

                    requirements.forEach(req => {
                        const key = req.dataset.req;
                        if (checks[key]) {
                            req.classList.add('valid');
                        } else {
                            req.classList.remove('valid');
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>