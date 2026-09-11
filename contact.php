<?php
/**
 * Page Contact - Vite & Gourmand
 * ECF : Formulaire public de contact (titre, message, email) + envoi notification équipe
 */

require_once __DIR__ . '/config/require_auth.php';

$csrfToken = generateCsrfToken();
$errors = [];
$success = false;
$oldData = ['sujet' => '', 'email' => '', 'message' => ''];

// Messages flash depuis session (après POST action/contact.php)
if (!empty($_SESSION['contact_errors'])) {
    $errors = $_SESSION['contact_errors'];
    $oldData = $_SESSION['contact_old_data'] ?? $oldData;
    unset($_SESSION['contact_errors'], $_SESSION['contact_old_data']);
}
if (!empty($_SESSION['contact_success'])) {
    $success = true;
    unset($_SESSION['contact_success']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contactez Vite & Gourmand - Traiteur gastronomique à Bordeaux pour vos événements">
    <title>Contact | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'contact';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-contact">
        <div class="contact-container">
            <!-- Hero Contact -->
            <header class="contact-hero">
                <div class="contact-hero__bg">
                    <picture>
                        <source srcset="public/img/hero-bg.avif" type="image/avif">
                        <source srcset="public/img/hero-bg.webp" type="image/webp">
                        <img src="public/img/hero-bg.jpeg" alt="" width="1920" height="1080">
                    </picture>
                    <div class="contact-hero__overlay"></div>
                </div>
                <div class="contact-hero__content">
                    <h1 class="contact-hero__title">Contactez-nous</h1>
                    <p class="contact-hero__desc">Vous avez un projet d'événement ? Une question sur nos menus ? Nous sommes à votre écoute.</p>
                </div>
            </header>

            <div class="contact-grid">
                <!-- Formulaire -->
                <section class="contact-form-section">
                    <div class="contact-card">
                        <?php if ($success): ?>
                            <div class="contact-success" role="status" aria-live="polite">
                                <div class="contact-success__icon" aria-hidden="true">✅</div>
                                <h2>Message envoyé !</h2>
                                <p>Merci pour votre confiance. Notre équipe vous répondra dans les plus brefs délais (généralement sous 24h ouvrées).</p>
                                <a href="index.php" class="btn btn--primary btn--lg mt-4">← Retour à l'accueil</a>
                            </div>
                        <?php else: ?>

                            <!-- Messages d'erreur globaux -->
                            <?php if (!empty($errors['global'])): ?>
                                <div class="alert alert--error" role="alert">
                                    <?= htmlspecialchars($errors['global']) ?>
                                </div>
                            <?php endif; ?>

                            <form action="actions/contact.php" method="POST" class="contact-form" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                                <div class="form-group">
                                    <label for="sujet">Objet <span class="required" aria-hidden="true">*</span></label>
                                    <select id="sujet" name="sujet" class="form-input <?= isset($errors['sujet']) ? 'is-invalid' : '' ?>" required aria-describedby="<?= isset($errors['sujet']) ? 'sujet-error' : 'sujet-hint' ?>">
                                        <option value="">-- Choisir un sujet --</option>
                                        <option value="devis" <?= ($oldData['sujet'] === 'devis') ? 'selected' : '' ?>>Demande de devis / Commande</option>
                                        <option value="menu" <?= ($oldData['sujet'] === 'menu') ? 'selected' : '' ?>>Question sur un menu</option>
                                        <option value="allergene" <?= ($oldData['sujet'] === 'allergene') ? 'selected' : '' ?>>Allergènes / Régimes spécifiques</option>
                                        <option value="livraison" <?= ($oldData['sujet'] === 'livraison') ? 'selected' : '' ?>>Livraison / Zone de couverture</option>
                                        <option value="reclamation" <?= ($oldData['sujet'] === 'reclamation') ? 'selected' : '' ?>>Réclamation / Suivi commande</option>
                                        <option value="autre" <?= ($oldData['sujet'] === 'autre') ? 'selected' : '' ?>>Autre</option>
                                    </select>
                                    <?php if (isset($errors['sujet'])): ?>
                                        <span class="form-error" id="sujet-error" role="alert"><?= htmlspecialchars($errors['sujet']) ?></span>
                                    <?php else: ?>
                                        <span class="form-hint" id="sujet-hint">Sélectionnez le motif de votre contact</span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="email">Votre email <span class="required" aria-hidden="true">*</span></label>
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
                                        <span class="form-hint" id="email-hint">Nous vous répondrons à cette adresse</span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="message">Votre message <span class="required" aria-hidden="true">*</span></label>
                                    <?php
                                    $msgAriaDescribedBy = isset($errors['message']) ? 'message-error' : 'message-hint';
                                    ?>
                                    <textarea
                                        id="message"
                                        name="message"
                                        class="form-input <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                                        rows="6"
                                        placeholder="Décrivez votre demande : type d'événement, date approximative, nombre de convives, demandes particulières..."
                                        required
                                        aria-describedby="<?= htmlspecialchars($msgAriaDescribedBy) ?>"
                                    ><?= htmlspecialchars($oldData['message']) ?></textarea>
                                    <?php if (isset($errors['message'])): ?>
                                        <span class="form-error" id="message-error" role="alert"><?= htmlspecialchars($errors['message']) ?></span>
                                    <?php else: ?>
                                        <span class="form-hint" id="message-hint">Minimum 20 caractères. Plus vous êtes précis, mieux nous pourrons vous répondre.</span>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" class="btn btn--primary btn--lg btn--fullwidth">
                                    <span>Envoyer mon message</span>
                                    <span class="btn__arrow" aria-hidden="true">→</span>
                                </button>
                            </form>

                        <?php endif; ?>
                    </div>
                </section>

                <!-- Infos Contact -->
                <aside class="contact-info-section">
                    <div class="contact-card contact-card--info">
                        <h2 class="contact-card__title">Nos coordonnées</h2>

                        <div class="contact-info">
                            <div class="contact-info__item">
                                <span class="contact-info__icon" aria-hidden="true">📍</span>
                                <div>
                                    <h3>Adresse</h3>
                                    <p>12 cours de l'Intendance<br>33000 Bordeaux</p>
                                </div>
                            </div>

                            <div class="contact-info__item">
                                <span class="contact-info__icon" aria-hidden="true">📞</span>
                                <div>
                                    <h3>Téléphone</h3>
                                    <p><a href="tel:+33556000000">05 56 00 00 00</a><br><small>Du lundi au samedi, 8h-20h</small></p>
                                </div>
                            </div>

                            <div class="contact-info__item">
                                <span class="contact-info__icon" aria-hidden="true">✉️</span>
                                <div>
                                    <h3>Email</h3>
                                    <p><a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a></p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-divider"></div>

                        <div class="contact-info__item">
                            <span class="contact-info__icon" aria-hidden="true">🚚</span>
                            <div>
                                <h3>Livraison</h3>
                                <p><strong>Gratuite</strong> à Bordeaux intra-muros<br>Partout ailleurs : 5 € + 0,59 €/km</p>
                            </div>
                        </div>

                        <div class="contact-info__item">
                            <span class="contact-info__icon" aria-hidden="true">🕐</span>
                            <div>
                                <h3>Horaires</h3>
                                <p>Lundi - Samedi : 8h - 20h<br>Dimanche : Sur réservation</p>
                            </div>
                        </div>

                        <div class="contact-divider"></div>

                        <div class="contact-cta">
                            <p>Prêt à commander ?</p>
                            <a href="menus.php" class="btn btn--primary btn--fullwidth">Voir nos menus</a>
                        </div>
                    </div>

                    <!-- Carte / Plan d'accès (placeholder) -->
                    <div class="contact-card contact-card--map">
                        <h3 class="contact-card__title">Nous trouver</h3>
                        <div class="contact-map" aria-label="Plan d'accès Vite & Gourmand - 12 cours de l'Intendance, 33000 Bordeaux">
                            <iframe
                                src="https://www.openstreetmap.org/export/embed.html?bbox=-0.580%2C44.838%2C-0.570%2C44.843&layer=mapnik&marker=44.8405%2C-0.575"
                                style="border: 0; width: 100%; height: 300px;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Localisation Vite & Gourmand sur OpenStreetMap">
                            </iframe>
                        </div>
                        <p class="contact-map__note">
                            📍 12 cours de l'Intendance, 33000 Bordeaux<br>
                            <a href="https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=44.8405%2C-0.575" target="_blank" rel="noopener">Itinéraire vers notre atelier</a>
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>