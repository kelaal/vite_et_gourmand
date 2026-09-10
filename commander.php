<?php
/**
 * Page Commander - Vite & Gourmand
 * ECF : Formulaire commande avec calculs frais livraison, remise, double insertion MySQL+MongoDB
 */

require_once __DIR__ . '/config/require_auth.php';
require_once __DIR__ . '/repositories/MenuRepository.php';
require_once __DIR__ . '/repositories/CommandeRepository.php';
require_once __DIR__ . '/repositories/UtilisateurRepository.php';

// Protection : utilisateur connecté requis
requireAuth();

$userId = getCurrentUserId();
$menuId = (int)($_GET['menu_id'] ?? 0);
$prefillQty = (int)($_GET['qty'] ?? 0);

if (!$menuId) {
    header('Location: menus.php');
    exit;
}

$menuRepo = new MenuRepository();
$menu = $menuRepo->findById($menuId);

if (!$menu) {
    header('Location: menus.php?error=notfound');
    exit;
}

$userRepo = new UtilisateurRepository();
$user = $userRepo->findById($userId);

if (!$user) {
    header('Location: connexion.php?redirect=commander.php%3Fmenu_id%3D' . $menuId);
    exit;
}

// Quantité par défaut
$defaultQty = max($prefillQty, (int)$menu['nb_personne_min']);
$maxQty = (int)$menu['stock_disponible'] > 0 ? (int)$menu['stock_disponible'] : 50;
$defaultQty = min($defaultQty, $maxQty);

$csrfToken = generateCsrfToken();
$errors = [];
$formData = [];
$calculation = null;

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    // Récupération données formulaire
    $formData = [
        'date_prestation' => $_POST['date_prestation'] ?? '',
        'heure_prestation' => $_POST['heure_prestation'] ?? '',
        'adresse_prestation' => trim($_POST['adresse_prestation'] ?? ''),
        'ville_prestation' => trim($_POST['ville_prestation'] ?? ''),
        'code_postal' => trim($_POST['code_postal'] ?? ''),
        'nb_personnes' => (int)($_POST['nb_personnes'] ?? $defaultQty),
        'menu_id' => $menuId,
        'pret_materiel' => !empty($_POST['pret_materiel']),
        'instructions' => trim($_POST['instructions'] ?? '')
    ];

    // Validation
    if (empty($formData['date_prestation'])) {
        $errors['date_prestation'] = 'La date de livraison est obligatoire.';
    } elseif (strtotime($formData['date_prestation']) < strtotime('today')) {
        $errors['date_prestation'] = 'La date ne peut pas être dans le passé.';
    } else {
        // Vérifier délai minimum selon menu (ex: 48h, 5 jours...)
        // Pour simplifier, on accepte toute date future
    }

    if (empty($formData['heure_prestation'])) {
        $errors['heure_prestation'] = 'L\'heure de livraison est obligatoire.';
    }

    if (empty($formData['adresse_prestation'])) {
        $errors['adresse_prestation'] = 'L\'adresse de livraison est obligatoire.';
    }

    if (empty($formData['ville_prestation'])) {
        $errors['ville_prestation'] = 'La ville est obligatoire.';
    }

    if (empty($formData['code_postal']) || !preg_match('/^\d{5}$/', $formData['code_postal'])) {
        $errors['code_postal'] = 'Code postal invalide (5 chiffres).';
    }

    // Vérifier ville = Bordeaux pour frais gratuits
    $isBordeaux = stripos($formData['ville_prestation'], 'bordeaux') !== false;

    if ($formData['nb_personnes'] < (int)$menu['nb_personne_min']) {
        $errors['nb_personnes'] = 'Minimum ' . (int)$menu['nb_personne_min'] . ' personnes requis.';
    }

    if ($formData['nb_personnes'] > $maxQty) {
        $errors['nb_personnes'] = 'Stock insuffisant (max ' . $maxQty . ').';
    }

    // Calculs tarifaires
    $prixMenuUnitaire = (float)$menu['prix_base_min'];
    $prixMenuTotal = $prixMenuUnitaire * $formData['nb_personnes'];

    // Remise 10% si nb_personnes >= min + 5
    $remiseSeuil = (int)$menu['nb_personne_min'] + 5;
    $remiseAppliquee = 0;
    if ($formData['nb_personnes'] >= $remiseSeuil) {
        $remiseAppliquee = $prixMenuTotal * 0.10;
    }

    // Frais de livraison
    $fraisLivraison = 0;
    $distanceKm = 0;

    if (!$isBordeaux) {
        // Estimation distance simplifiée (en production : API géocodage)
        // Pour l'ECF, on utilise une estimation basée sur code postal
        $distanceKm = estimateDistanceFromBordeaux($formData['code_postal']);
        $fraisLivraison = 5.00 + (0.59 * $distanceKm);
    }

    $prixTotal = $prixMenuTotal - $remiseAppliquee + $fraisLivraison;

    $calculation = [
        'prix_menu_unitaire' => $prixMenuUnitaire,
        'prix_menu_total' => $prixMenuTotal,
        'remise_seuil' => $remiseSeuil,
        'remise_appliquee' => $remiseAppliquee,
        'frais_livraison' => $fraisLivraison,
        'distance_km' => $distanceKm,
        'is_bordeaux' => $isBordeaux,
        'prix_total' => $prixTotal
    ];

    // Si pas d'erreurs de validation, on peut passer à la confirmation
    if (empty($errors)) {
        // Stocker en session pour l'étape confirmation
        $_SESSION['commande_pending'] = [
            'form_data' => $formData,
            'calculation' => $calculation,
            'menu' => $menu,
            'user' => $user
        ];
        header('Location: commander-confirm.php');
        exit;
    }
}

// Fonction estimation distance depuis Bordeaux (simplifiée pour ECF)
function estimateDistanceFromBordeaux(string $codePostal): float {
    $cp = (int)$codePostal;

    // Bordeaux intra-muros : 33000, 33100, 33200, 33300, 33800
    $bordeauxCPs = [33000, 33100, 33200, 33300, 33800];
    if (in_array($cp, $bordeauxCPs)) return 0;

    // Communes limitrophes (estimation)
    $proches = [
        33700 => 8,   // Mérignac
        33170 => 7,   // Gradignan
        33600 => 6,   // Pessac
        33130 => 10,  // Bègles
        33110 => 9,   // Le Bouscat
        33200 => 5,   // Bordeaux Caudéran
        33400 => 12,  // Talence
        33610 => 15,  // Canéjan
        33700 => 8,   // Mérignac
        33185 => 12,  // Le Haillan
        33290 => 18,  // Blanquefort
        33320 => 20,  // Eysines
        33270 => 14,  // Floirac
        33150 => 15,  // Cenon
        33230 => 16,  // Lormont
    ];

    if (isset($proches[$cp])) return $proches[$cp];

    // Par défaut : estimation grossière selon département
    if ($cp >= 33000 && $cp < 34000) return 25; // Gironde
    if ($cp >= 40000 && $cp < 41000) return 60; // Landes
    if ($cp >= 24000 && $cp < 25000) return 80; // Dordogne
    if ($cp >= 16000 && $cp < 17000) return 120; // Charente
    if ($cp >= 17000 && $cp < 18000) return 140; // Charente-Maritime

    return 50; // Défaut
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Passer commande - <?= htmlspecialchars($menu['titre']) ?> - Vite & Gourmand">
    <title>Commander | <?= htmlspecialchars($menu['titre']) ?> | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'menus';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-commander">
        <div class="commander-container">
            <!-- En-tête -->
            <header class="commander-header">
                <div class="commander-header__menu">
                    <img src="<?= htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.webp') ?>" alt="" class="commander-header__img" loading="lazy">
                    <div>
                        <h1 class="commander-header__title"><?= htmlspecialchars($menu['titre']) ?></h1>
                        <div class="commander-header__badges">
                            <span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span>
                            <span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span>
                        </div>
                    </div>
                </div>
                <a href="menu-detail.php?id=<?= $menuId ?>" class="btn btn--outline btn--sm">← Modifier le menu</a>
            </header>

            <!-- Messages d'erreur globaux -->
            <?php if (!empty($errors['global'])): ?>
                <div class="alert alert--error" role="alert">
                    <?= htmlspecialchars($errors['global']) ?>
                </div>
            <?php endif; ?>

            <!-- Grille Formulaire + Récapitulatif -->
            <div class="commander-grid">
                <!-- Colonne Formulaire -->
                <form action="" method="POST" class="commander-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="menu_id" value="<?= $menuId ?>">

                    <!-- Infos Client (pré-remplies) -->
                    <fieldset class="commander-section">
                        <legend class="commander-section__title">
                            <span class="icon">👤</span>
                            Vos informations
                        </legend>
                        <p class="commander-section__desc">Pré-remplies depuis votre profil, modifiables pour cette commande.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom <span class="required">*</span></label>
                                <input type="text" id="nom" name="nom" class="form-input" value="<?= htmlspecialchars($formData['nom'] ?? $user['nom']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="prenom">Prénom <span class="required">*</span></label>
                                <input type="text" id="prenom" name="prenom" class="form-input" value="<?= htmlspecialchars($formData['prenom'] ?? $user['prenom']) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($formData['email'] ?? $user['email']) ?>" readonly>
                            <span class="form-hint">Non modifiable ici. <a href="espace.php#mon-profil">Modifier dans mon profil</a></span>
                        </div>

                        <div class="form-group">
                            <label for="gsm">Téléphone <span class="required">*</span></label>
                            <input type="tel" id="gsm" name="gsm" class="form-input" value="<?= htmlspecialchars($formData['gsm'] ?? $user['gsm']) ?>" pattern="0[1-9](\s?\d{2}){4}" required>
                            <?php if (isset($errors['gsm'])): ?>
                                <span class="form-error"><?= htmlspecialchars($errors['gsm']) ?></span>
                            <?php endif; ?>
                        </div>
                    </fieldset>

                    <!-- Livraison -->
                    <fieldset class="commander-section">
                        <legend class="commander-section__title">
                            <span class="icon">🚚</span>
                            Livraison
                        </legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_prestation">Date souhaitée <span class="required">*</span></label>
                                <input type="date"
                                    id="date_prestation"
                                    name="date_prestation"
                                    class="form-input <?= isset($errors['date_prestation']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($formData['date_prestation'] ?? '') ?>"
                                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                    required>
                                <?php if (isset($errors['date_prestation'])): ?>
                                    <span class="form-error"><?= htmlspecialchars($errors['date_prestation']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="heure_prestation">Heure souhaitée <span class="required">*</span></label>
                                <input type="time"
                                    id="heure_prestation"
                                    name="heure_prestation"
                                    class="form-input <?= isset($errors['heure_prestation']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($formData['heure_prestation'] ?? '12:00') ?>"
                                    min="08:00"
                                    max="20:00"
                                    step="1800"
                                    required>
                                <?php if (isset($errors['heure_prestation'])): ?>
                                    <span class="form-error"><?= htmlspecialchars($errors['heure_prestation']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="adresse_prestation">Adresse de livraison <span class="required">*</span></label>
                            <input type="text"
                                id="adresse_prestation"
                                name="adresse_prestation"
                                class="form-input <?= isset($errors['adresse_prestation']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($formData['adresse_prestation'] ?? $user['adresse_postale']) ?>"
                                placeholder="Numéro, rue, bâtiment, étage..."
                                required>
                            <?php if (isset($errors['adresse_prestation'])): ?>
                                <span class="form-error"><?= htmlspecialchars($errors['adresse_prestation']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="code_postal">Code postal <span class="required">*</span></label>
                                <input type="text"
                                    id="code_postal"
                                    name="code_postal"
                                    class="form-input <?= isset($errors['code_postal']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($formData['code_postal'] ?? '') ?>"
                                    placeholder="33000"
                                    pattern="\d{5}"
                                    maxlength="5"
                                    required>
                                <?php if (isset($errors['code_postal'])): ?>
                                    <span class="form-error"><?= htmlspecialchars($errors['code_postal']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="ville_prestation">Ville <span class="required">*</span></label>
                                <input type="text"
                                    id="ville_prestation"
                                    name="ville_prestation"
                                    class="form-input <?= isset($errors['ville_prestation']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($formData['ville_prestation'] ?? '') ?>"
                                    placeholder="Bordeaux"
                                    required>
                                <?php if (isset($errors['ville_prestation'])): ?>
                                    <span class="form-error"><?= htmlspecialchars($errors['ville_prestation']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group form-group--checkbox">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="pret_materiel" id="pret_materiel" value="1">
                                <span class="checkmark"></span>
                                Prêt de matériel (chafing dishes, réchauds, nappes) — Retour sous 10 jours ouvrés, sinon facturation 600 € selon CGV
                            </label>
                        </div>
                    </fieldset>

                    <!-- Menu & Quantité -->
                    <fieldset class="commander-section">
                        <legend class="commander-section__title">
                            <span class="icon">🍽️</span>
                            Menu & Quantité
                        </legend>

                        <div class="form-group">
                            <label for="nb_personnes">Nombre de personnes <span class="required">*</span></label>
                            <div class="qty-selector">
                                <button type="button" class="qty-btn qty-btn--minus" id="cmdQtyMinus" aria-label="Diminuer">−</button>
                                <input type="number"
                                    id="nb_personnes"
                                    name="nb_personnes"
                                    class="qty-input qty-input--large"
                                    value="<?= htmlspecialchars($formData['nb_personnes'] ?? $defaultQty) ?>"
                                    min="<?= (int)$menu['nb_personne_min'] ?>"
                                    max="<?= $maxQty ?>"
                                    required>
                                <button type="button" class="qty-btn qty-btn--plus" id="cmdQtyPlus" aria-label="Augmenter">+</button>
                            </div>
                            <span class="form-hint">
                                Minimum <?= (int)$menu['nb_personne_min'] ?> personnes
                                <?= $maxQty > 0 ? ' · Maximum ' . $maxQty : '' ?>
                                <?php $seuil = (int)$menu['nb_personne_min'] + 5; ?>
                                <span class="hint-highlight">Remise 10% à partir de <?= $seuil ?> personnes</span>
                            </span>
                        </div>

                        <div class="menu-preview">
                            <span class="menu-preview__price" id="previewUnitPrice">
                                <?= number_format((float)$menu['prix_base_min'], 2, ',', ' ') ?> € / pers.
                            </span>
                            <span class="menu-preview__total" id="previewTotal">
                                = <?= number_format((float)$menu['prix_base_min'] * $defaultQty, 2, ',', ' ') ?> €
                            </span>
                        </div>
                    </fieldset>

                    <!-- Instructions -->
                    <fieldset class="commander-section">
                        <legend class="commander-section__title">
                            <span class="icon">📝</span>
                            Instructions particulières
                        </legend>
                        <div class="form-group">
                            <textarea name="instructions" id="instructions" class="form-input" rows="3" placeholder="Allergies, intolérances, accès livraison, code porte, horaires préférés..."></textarea>
                        </div>
                    </fieldset>

                    <!-- Bouton validation -->
                    <div class="commander-actions">
                        <button type="submit" class="btn btn--primary btn--lg btn--fullwidth" id="btnContinuer">
                            <span>Continuer vers le récapitulatif</span>
                            <span class="btn__arrow" aria-hidden="true">→</span>
                        </button>
                        <a href="menu-detail.php?id=<?= $menuId ?>" class="btn btn--outline btn--lg btn--fullwidth">Annuler</a>
                    </div>
                </form>

                <!-- Colonne Récapitulatif (Sticky) -->
                <aside class="commander-summary" aria-labelledby="summary-title">
                    <div class="summary-card" id="summaryCard">
                        <h2 id="summary-title" class="summary-card__title">Récapitulatif</h2>

                        <div class="summary-lines">
                            <div class="summary-line">
                                <span>Menu</span>
                                <span><?= htmlspecialchars($menu['titre']) ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Prix / personne</span>
                                <span class="text-terracotta" id="sumUnitPrice"><?= number_format((float)$menu['prix_base_min'], 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="summary-line">
                                <span>Quantité</span>
                                <span id="sumQty"><?= $defaultQty ?></span> pers.
                            </div>
                            <div class="summary-line summary-line--subtotal">
                                <span>Sous-total menu</span>
                                <span id="sumSubtotal"><?= number_format((float)$menu['prix_base_min'] * $defaultQty, 2, ',', ' ') ?> €</span>
                            </div>
                        </div>

                        <!-- Remise -->
                        <div class="summary-line summary-line--discount" id="discountLine" style="display: none;">
                            <span class="discount-label">
                                Remise 10% (<span id="discountThreshold"></span> pers. min)
                            </span>
                            <span class="text-success" id="sumDiscount">− 0,00 €</span>
                        </div>

                        <!-- Frais livraison -->
                        <div class="summary-line summary-line--delivery" id="deliveryLine">
                            <span>Frais de livraison</span>
                            <span id="sumDelivery">
                                <span class="text-terracotta">À calculer</span>
                                <button type="button" class="delivery-info-btn" id="deliveryInfoBtn" aria-label="Comment sont calculés les frais">ℹ️</button>
                            </span>
                        </div>

                        <!-- Total -->
                        <div class="summary-total">
                            <span>Total estimé</span>
                            <strong id="sumTotal"><?= number_format((float)$menu['prix_base_min'] * $defaultQty, 2, ',', ' ') ?> €</strong>
                        </div>

                        <!-- Info livraison -->
                        <div class="summary-info" id="deliveryInfo" hidden>
                            <h4>🚚 Règle des frais de livraison</h4>
                            <ul>
                                <li><strong>Bordeaux intra-muros :</strong> Gratuit</li>
                                <li><strong>Hors Bordeaux :</strong> 5,00 € + 0,59 € / km</li>
                                <li>Distance estimée depuis votre code postal</li>
                            </ul>
                            <p class="info-note">Calcul exact au récapitulatif final après validation de l'adresse.</p>
                        </div>

                        <!-- Conditions -->
                        <div class="summary-conditions">
                            <h4>⚠️ Conditions du menu</h4>
                            <p><?= nl2br(htmlspecialchars($menu['conditions_delai_stockage'])) ?></p>
                        </div>

                        <!-- Paiement -->
                        <div class="summary-payment">
                            <h4>💳 Paiement</h4>
                            <p>Paiement sur place le jour J (espèces, carte, chèque) ou virement anticipé.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Modale Info Livraison -->
    <dialog class="modal" id="modalDeliveryInfo" role="dialog" aria-modal="true" aria-labelledby="modal-delivery-title">
        <form method="dialog">
            <header class="modal__header">
                <h2 id="modal-delivery-title">Frais de livraison</h2>
                <button type="button" class="modal__close" aria-label="Fermer">✕</button>
            </header>
            <div class="modal__body">
                <h3>Comment sont calculés les frais ?</h3>
                <ul class="delivery-rules">
                    <li><strong>Bordeaux (33000, 33100, 33200, 33300, 33800) :</strong> Livraison <strong>offerte</strong></li>
                    <li><strong>Hors Bordeaux :</strong> Forfait <strong>5,00 €</strong> + <strong>0,59 € par km</strong></li>
                    <li>La distance est estimée depuis votre code postal vers Bordeaux centre</li>
                </ul>
                <h3>Exemples</h3>
                <table class="delivery-examples">
                    <thead>
                        <tr><th>Ville</th><th>Code postal</th><th>Distance</th><th>Frais</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Bordeaux</td><td>33000</td><td>0 km</td><td class="text-success">Offert</td></tr>
                        <tr><td>Mérignac</td><td>33700</td><td>~8 km</td><td>5,00 € + 4,72 € = <strong>9,72 €</strong></td></tr>
                        <tr><td>Pessac</td><td>33600</td><td>~6 km</td><td>5,00 € + 3,54 € = <strong>8,54 €</strong></td></tr>
                        <tr><td>Libourne</td><td>33500</td><td>~35 km</td><td>5,00 € + 20,65 € = <strong>25,65 €</strong></td></tr>
                    </tbody>
                </table>
                <p class="delivery-note">Le montant exact sera confirmé avant paiement.</p>
            </div>
            <footer class="modal__footer">
                <button type="button" class="btn btn--primary modal__close-btn">Compris</button>
            </footer>
        </form>
    </dialog>

    <!-- Scripts Commander -->
    <script>
        (function() {
            'use strict';

            // Données menu
            const menuData = {
                prixBase: <?= (float)$menu['prix_base_min'] ?>,
                nbPersonneMin: <?= (int)$menu['nb_personne_min'] ?>,
                stock: <?= $maxQty ?>,
                remiseSeuil: <?= (int)$menu['nb_personne_min'] + 5 ?>
            };

            // Éléments DOM
            const qtyInput = document.getElementById('nb_personnes');
            const qtyMinus = document.getElementById('cmdQtyMinus');
            const qtyPlus = document.getElementById('cmdQtyPlus');
            const codePostalInput = document.getElementById('code_postal');
            const villeInput = document.getElementById('ville_prestation');

            // Résumé
            const sumQty = document.getElementById('sumQty');
            const sumUnitPrice = document.getElementById('sumUnitPrice');
            const sumSubtotal = document.getElementById('sumSubtotal');
            const discountLine = document.getElementById('discountLine');
            const discountThreshold = document.getElementById('discountThreshold');
            const sumDiscount = document.getElementById('sumDiscount');
            const deliveryLine = document.getElementById('deliveryLine');
            const sumDelivery = document.getElementById('sumDelivery');
            const sumTotal = document.getElementById('sumTotal');

            // Preview dans formulaire
            const previewUnitPrice = document.getElementById('previewUnitPrice');
            const previewTotal = document.getElementById('previewTotal');

            // Modal livraison
            const deliveryInfoBtn = document.getElementById('deliveryInfoBtn');
            const modalDelivery = document.getElementById('modalDeliveryInfo');
            const deliveryInfo = document.getElementById('deliveryInfo');

            function formatPrice(value) {
                return value.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
            }

            function updateSummary() {
                const qty = parseInt(qtyInput?.value, 10) || menuData.nbPersonneMin;
                const prixUnitaire = menuData.prixBase;
                const sousTotal = prixUnitaire * qty;

                // Remise
                let remise = 0;
                if (qty >= menuData.remiseSeuil) {
                    remise = sousTotal * 0.10;
                }

                // Frais livraison (estimation côté client)
                let frais = 0;
                let isBordeaux = false;
                const cp = codePostalInput?.value?.trim();
                const ville = villeInput?.value?.trim().toLowerCase();

                if (cp) {
                    const cpNum = parseInt(cp, 10);
                    const bordeauxCPs = [33000, 33100, 33200, 33300, 33800];
                    if (bordeauxCPs.includes(cpNum) || ville.includes('bordeaux')) {
                        isBordeaux = true;
                        frais = 0;
                    } else {
                        // Estimation simplifiée
                        const distances = {
                            33700: 8, 33170: 7, 33600: 6, 33130: 10, 33110: 9,
                            33400: 12, 33610: 15, 33185: 12, 33290: 18, 33320: 20,
                            33270: 14, 33150: 15, 33230: 16
                        };
                        const dist = distances[cpNum] || 25;
                        frais = 5.00 + (0.59 * dist);
                    }
                }

                const total = sousTotal - remise + frais;

                // Mise à jour DOM
                if (sumQty) sumQty.textContent = qty;
                if (sumSubtotal) sumSubtotal.textContent = formatPrice(sousTotal);
                if (previewTotal) previewTotal.textContent = '= ' + formatPrice(sousTotal);

                // Remise
                if (discountLine && discountThreshold && sumDiscount) {
                    if (remise > 0) {
                        discountLine.style.display = 'flex';
                        discountThreshold.textContent = menuData.remiseSeuil;
                        sumDiscount.textContent = '− ' + formatPrice(remise);
                    } else {
                        discountLine.style.display = 'none';
                    }
                }

                // Livraison
                if (sumDelivery) {
                    if (isBordeaux) {
                        sumDelivery.innerHTML = '<span class="text-success">Offert (Bordeaux)</span>';
                    } else if (cp) {
                        const dist = distances[parseInt(cp, 10)] || 25;
                        sumDelivery.innerHTML = formatPrice(frais) + ' <button type="button" class="delivery-info-btn" id="deliveryInfoBtn2" aria-label="Détails">ℹ️</button>';
                        // Réattacher event listener
                        const newBtn = document.getElementById('deliveryInfoBtn2');
                        if (newBtn) newBtn.addEventListener('click', () => modalDelivery?.showModal());
                    } else {
                        sumDelivery.innerHTML = '<span class="text-terracotta">À calculer</span> <button type="button" class="delivery-info-btn" id="deliveryInfoBtn2" aria-label="Détails">ℹ️</button>';
                        const newBtn = document.getElementById('deliveryInfoBtn2');
                        if (newBtn) newBtn.addEventListener('click', () => modalDelivery?.showModal());
                    }
                }

                // Total
                if (sumTotal) sumTotal.textContent = formatPrice(total);
            }

            // Distances pour calcul client
            const distances = {
                33700: 8, 33170: 7, 33600: 6, 33130: 10, 33110: 9,
                33400: 12, 33610: 15, 33185: 12, 33290: 18, 33320: 20,
                33270: 14, 33150: 15, 33230: 16
            };

            // Event listeners quantité
            function setupQtyButtons() {
                if (qtyMinus && qtyPlus && qtyInput) {
                    qtyMinus.addEventListener('click', () => {
                        const current = parseInt(qtyInput.value, 10) || menuData.nbPersonneMin;
                        if (current > menuData.nbPersonneMin) {
                            qtyInput.value = current - 1;
                            updateSummary();
                        }
                    });
                    qtyPlus.addEventListener('click', () => {
                        const current = parseInt(qtyInput.value, 10) || menuData.nbPersonneMin;
                        if (current < menuData.stock) {
                            qtyInput.value = current + 1;
                            updateSummary();
                        }
                    });
                    qtyInput.addEventListener('change', updateSummary);
                    qtyInput.addEventListener('input', updateSummary);
                }
            }

            // Code postal / ville
            if (codePostalInput) codePostalInput.addEventListener('input', updateSummary);
            if (villeInput) villeInput.addEventListener('input', updateSummary);

            // Modal
            if (deliveryInfoBtn && modalDelivery) {
                deliveryInfoBtn.addEventListener('click', () => modalDelivery.showModal());
            }
            if (deliveryInfo) {
                deliveryInfo.addEventListener('click', (e) => {
                    if (e.target.closest('.modal__close') || e.target.closest('.modal__close-btn')) {
                        modalDelivery?.close();
                    }
                });
            }

            // Initialisation
            setupQtyButtons();
            updateSummary();

            // Auto-complétion ville depuis CP (simple)
            const cpToVille = {
                '33000': 'Bordeaux', '33100': 'Bordeaux', '33200': 'Bordeaux', '33300': 'Bordeaux', '33800': 'Bordeaux',
                '33700': 'Mérignac', '33170': 'Gradignan', '33600': 'Pessac', '33130': 'Bègles',
                '33110': 'Le Bouscat', '33400': 'Talence', '33610': 'Canéjan', '33185': 'Le Haillan',
                '33290': 'Blanquefort', '33320': 'Eysines', '33270': 'Floirac', '33150': 'Cenon', '33230': 'Lormont'
            };
            if (codePostalInput && villeInput) {
                codePostalInput.addEventListener('blur', function() {
                    const cp = this.value.trim();
                    if (cp && cpToVille[cp] && !villeInput.value.trim()) {
                        villeInput.value = cpToVille[cp];
                        updateSummary();
                    }
                });
            }

        })();
    </script>
</body>

</html>