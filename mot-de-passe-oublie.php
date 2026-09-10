<?php
/**
 * Page Mot de Passe Oublié - Vite & Gourmand
 * ECF : Demande de réinitialisation par email
 */

require_once __DIR__ . '/config/require_auth.php';

// Si déjà connecté, rediriger
if (isLoggedIn()) {
    header('Location: espace.php');
    exit;
}

$csrfToken = generateCsrfToken();
$errors = [];
$success = false;
$oldEmail = '';

// Messages depuis session
if (!empty($_SESSION['forgot_errors'])) {
    $errors = $_SESSION['forgot_errors'];
    $oldEmail = $_SESSION['forgot_old_email'] ?? '';
    unset($_SESSION['forgot_errors'], $_SESSION['forgot_old_email']);
}
if (!empty($_SESSION['forgot_success'])) {
    $success = true;
    unset($_SESSION['forgot_success']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Réinitialiser votre mot de passe - Vite & Gourmand">
    <title>Mot de passe oublié | Vite & Gourmand</title>

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
                    <h1>Mot de passe oublié</h1>
                    <p class="auth-subtitle">Entrez votre email pour recevoir un lien de réinitialisation</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert--success" role="alert">
                        <strong>Email envoyé !</strong> Si cette adresse existe dans notre base, vous recevrez sous peu un lien pour réinitialiser votre mot de passe. Vérifiez vos spams.
                    </div>
                    <div class="auth-footer">
                        <p><a href="connexion.php">← Retour à la connexion</a></p>
                    </div>
                <?php else: ?>

                    <!-- Messages d'erreur globaux -->
                    <?php if (!empty($errors['global'])): ?>
                        <div class="alert alert--error" role="alert">
                            <?= htmlspecialchars($errors['global']) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire demande reset -->
                    <form action="actions/mot-de-passe-oublie.php" method="POST" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

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
                                <span class="form-hint" id="email-hint">L'email associé à votre compte</span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn--primary btn--fullwidth btn--auth-submit">
                            <span>Recevoir le lien de réinitialisation</span>
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Vous souvenez-vous de votre mot de passe ? <a href="connexion.php">Se connecter</a></p>
                        <p>Pas encore de compte ? <a href="inscription.php">Créer mon compte</a></p>
                        <p><a href="index.php">← Retour à l'accueil</a></p>
                    </div>

                <?php endif; ?>
            </div>

            <div class="auth-reassurance" aria-hidden="true">
                <div class="reassurance-item">
                    <span class="icon">🔗</span>
                    <span>Lien sécurisé à usage unique</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">⏱️</span>
                    <span>Valide 1 heure</span>
                </div>
                <div class="reassurance-item">
                    <span class="icon">🛡️</span>
                    <span>Aucune donnée exposée</span>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>