<?php
/**
 * Page d'Inscription - Vite & Gourmand
 * ECF : Création compte client (rôle 'utilisateur')
 */

require_once __DIR__ . '/config/require_auth.php';

// Si déjà connecté, rediriger vers l'espace
if (isLoggedIn()) {
    header('Location: espace.php');
    exit;
}

$csrfToken = generateCsrfToken();
$errors = [];
$oldData = [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'gsm' => '',
    'adresse_postale' => ''
];

// Récupération erreurs depuis session (après POST action/inscription.php)
if (!empty($_SESSION['register_errors'])) {
    $errors = $_SESSION['register_errors'];
    $oldData = $_SESSION['register_old_data'] ?? $oldData;
    unset($_SESSION['register_errors'], $_SESSION['register_old_data']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Créer un compte Vite & Gourmand - Traiteur gastronomique à Bordeaux">
    <title>Inscription | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'inscription';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-auth">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="index.php" class="auth-logo" aria-label="Retour à l'accueil">
                        <img src="public/img/logo.svg" alt="Vite & Gourmand" width="160" height="44">
                    </a>
                    <h1>Créer mon compte</h1>
                    <p class="auth-subtitle">Rejoignez Vite & Gourmand pour commander en ligne</p>
                </div>

                <!-- Messages d'erreur globaux -->
                <?php if (!empty($errors['global'])): ?>
                    <div class="alert alert--error" role="alert">
                        <?= htmlspecialchars($errors['global']) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire d'inscription -->
                <form action="actions/inscription.php" method="POST" class="auth-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <!-- Ligne Nom / Prénom -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom <span class="required" aria-hidden="true">*</span></label>
                            <input type="text"
                                id="nom"
                                name="nom"
                                class="form-input <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($oldData['nom']) ?>"
                                placeholder="Votre nom"
                                required
                                autocomplete="family-name"
                                aria-describedby="<?= isset($errors['nom']) ? 'nom-error' : 'nom-hint' ?>">
                            <?php if (isset($errors['nom'])): ?>
                                <span class="form-error" id="nom-error" role="alert"><?= htmlspecialchars($errors['nom']) ?></span>
                            <?php else: ?>
                                <span class="form-hint" id="nom-hint">Votre nom de famille</span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom <span class="required" aria-hidden="true">*</span></label>
                            <input type="text"
                                id="prenom"
                                name="prenom"
                                class="form-input <?= isset($errors['prenom']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($oldData['prenom']) ?>"
                                placeholder="Votre prénom"
                                required
                                autocomplete="given-name"
                                aria-describedby="<?= isset($errors['prenom']) ? 'prenom-error' : 'prenom-hint' ?>">
                            <?php if (isset($errors['prenom'])): ?>
                                <span class="form-error" id="prenom-error" role="alert"><?= htmlspecialchars($errors['prenom']) ?></span>
                            <?php else: ?>
                                <span class="form-hint" id="prenom-hint">Votre prénom</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Adresse email <span class="required" aria-hidden="true">*</span></label>
                        <input type="email"
                            id="email"
                            name="email"
                            class="form-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($oldData['email']) ?>"
                            placeholder="vous@exemple.com"
                            required
                            autocomplete="email"
                            aria-describedby="<?= isset($errors['email']) ? 'email-error' : 'email-hint' ?>">
                        <?php if (isset($errors['email'])): ?>
                            <span class="form-error" id="email-error" role="alert"><?= htmlspecialchars($errors['email']) ?></span>
                        <?php else: ?>
                            <span class="form-hint" id="email-hint">Cet email sera votre identifiant de connexion</span>
                        <?php endif; ?>
                    </div>

                    <!-- GSM -->
                    <div class="form-group">
                        <label for="gsm">Numéro de téléphone <span class="required" aria-hidden="true">*</span></label>
                        <input type="tel"
                            id="gsm"
                            name="gsm"
                            class="form-input <?= isset($errors['gsm']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($oldData['gsm']) ?>"
                            placeholder="06 XX XX XX XX"
                            required
                            autocomplete="tel"
                            pattern="0[1-9](\s?\d{2}){4}"
                            aria-describedby="<?= isset($errors['gsm']) ? 'gsm-error' : 'gsm-hint' ?>">
                        <?php if (isset($errors['gsm'])): ?>
                            <span class="form-error" id="gsm-error" role="alert"><?= htmlspecialchars($errors['gsm']) ?></span>
                        <?php else: ?>
                            <span class="form-hint" id="gsm-hint">Format : 06 XX XX XX XX ou 07 XX XX XX XX</span>
                        <?php endif; ?>
                    </div>

                    <!-- Adresse postale -->
                    <div class="form-group">
                        <label for="adresse_postale">Adresse postale complète <span class="required" aria-hidden="true">*</span></label>
                        <?php
                        $adresseAria = isset($errors['adresse_postale']) ? 'adresse-error' : 'adresse-hint';
                        ?>
                        <textarea id="adresse_postale"
                            name="adresse_postale"
                            class="form-input <?= isset($errors['adresse_postale']) ? 'is-invalid' : '' ?>"
                            rows="3"
                            required
                            autocomplete="street-address"
                            aria-describedby="<?= htmlspecialchars($adresseAria) ?>"
                        ><?= htmlspecialchars($oldData['adresse_postale']) ?></textarea>
                        <?php if (isset($errors['adresse_postale'])): ?>
                            <span class="form-error" id="adresse-error" role="alert"><?= htmlspecialchars($errors['adresse_postale']) ?></span>
                        <?php else: ?>
                            <span class="form-hint" id="adresse-hint">Numéro, rue, code postal, ville (ex: 14 rue Judaïque, 33000 Bordeaux)</span>
                        <?php endif; ?>
                    </div>

                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="password">Mot de passe <span class="required" aria-hidden="true">*</span></label>
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
                            <span class="req <?= strlen($oldData['password'] ?? '') >= 10 ? 'valid' : '' ?>" data-req="length">10 caractères minimum</span>
                            <span class="req" data-req="upper">1 majuscule</span>
                            <span class="req" data-req="lower">1 minuscule</span>
                            <span class="req" data-req="digit">1 chiffre</span>
                            <span class="req" data-req="special">1 caractère spécial</span>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="form-error" id="password-error" role="alert"><?= htmlspecialchars($errors['password']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div class="form-group">
                        <label for="password_confirm">Confirmer le mot de passe <span class="required" aria-hidden="true">*</span></label>
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

                    <!-- CGV / RGPD -->
                    <div class="form-group form-group--checkbox">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="cgv" id="cgv" value="1" required>
                            <span class="checkmark"></span>
                            J'accepte les <a href="cgv.php" target="_blank">Conditions Générales de Vente</a> et la <a href="mentions-legales.php" target="_blank">Politique de Confidentialité</a> <span class="required" aria-hidden="true">*</span>
                        </label>
                        <?php if (isset($errors['cgv'])): ?>
                            <span class="form-error" role="alert"><?= htmlspecialchars($errors['cgv']) ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn--primary btn--fullwidth btn--auth-submit">
                        <span>Créer mon compte</span>
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
                    <p><a href="index.php">← Retour à l'accueil</a></p>
                </div>
            </div>

            <!-- Info rassurance -->
            <div class="auth-reassurance" aria-hidden="true">
                <div class="reassurance-item">
                    <span class="icon">📧</span>
                    <span>Email de bienvenue automatique</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">🔒</span>
                    <span>Mot de passe chiffré (ARGON2ID)</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">✅</span>
                    <span>Conforme RGPD</span>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Scripts : toggle password + validation temps réel MDP -->
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