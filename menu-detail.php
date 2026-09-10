<?php
/**
 * Page Détail Menu - Vite & Gourmand
 * ECF : Vue détaillée complète (galerie, plats, allergènes, conditions, stock, bouton Commander)
 */

require_once __DIR__ . '/config/require_auth.php';
require_once __DIR__ . '/repositories/MenuRepository.php';

$menuId = (int)($_GET['id'] ?? 0);

if (!$menuId) {
    header('Location: menus.php');
    exit;
}

$menuRepo = new MenuRepository();
$menu = $menuRepo->findDetails($menuId);

if (!$menu) {
    header('Location: menus.php?error=notfound');
    exit;
}

$csrfToken = generateCsrfToken();
$isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($menu['description']) ?> - Menu traiteur Vite & Gourmand à Bordeaux">
    <title><?= htmlspecialchars($menu['titre']) ?> | Vite & Gourmand</title>

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

    <main>
        <!-- HERO MENU -->
        <header class="menu-hero">
            <div class="menu-hero__gallery" id="menuGallery" role="region" aria-label="Galerie d'images du menu">
                <!-- Image principale -->
                <div class="menu-hero__main-image">
                    <picture>
                        <source srcset="<?= htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.webp') ?>" type="image/webp">
                        <img src="<?= htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.jpeg') ?>"
                             alt="<?= htmlspecialchars($menu['titre']) ?>"
                             width="1200" height="700"
                             id="mainImage">
                    </picture>
                </div>

                <!-- Miniatures galerie (si images supplémentaires) -->
                <?php if (!empty($menu['images'])): ?>
                    <div class="menu-hero__thumbnails" id="thumbnails" role="list">
                        <?php foreach ($menu['images'] as $index => $img): ?>
                            <button type="button"
                                class="menu-hero__thumb <?= $index === 0 ? 'is-active' : '' ?>"
                                data-src="<?= htmlspecialchars($img['image_url']) ?>"
                                data-alt="<?= htmlspecialchars($img['alt_text'] ?? $menu['titre']) ?>"
                                aria-label="Voir l'image <?= $index + 1 ?>"
                                aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>"
                                role="listitem">
                                <img src="<?= htmlspecialchars($img['image_url']) ?>"
                                     alt=""
                                     loading="lazy"
                                     width="120" height="80">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="menu-hero__content">
                <div class="menu-hero__badges">
                    <span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span>
                    <span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span>
                </div>

                <h1 class="menu-hero__title"><?= htmlspecialchars($menu['titre']) ?></h1>

                <p class="menu-hero__description"><?= htmlspecialchars($menu['description']) ?></p>

                <!-- Infos clés -->
                <div class="menu-hero__meta">
                    <div class="menu-meta">
                        <span class="menu-meta__icon" aria-hidden="true">👥</span>
                        <div>
                            <span class="menu-meta__label">Minimum</span>
                            <span class="menu-meta__value"><?= (int)$menu['nb_personne_min'] ?> personnes</span>
                        </div>
                    </div>

                    <div class="menu-meta">
                        <span class="menu-meta__icon" aria-hidden="true">💰</span>
                        <div>
                            <span class="menu-meta__label">Prix / personne</span>
                            <span class="menu-meta__value menu-meta__value--price">
                                <?= number_format((float)$menu['prix_base_min'], 2, ',', ' ') ?> €
                            </span>
                        </div>
                    </div>

                    <div class="menu-meta">
                        <span class="menu-meta__icon" aria-hidden="true">📦</span>
                        <div>
                            <span class="menu-meta__label">Stock</span>
                            <span class="menu-meta__value <?= (int)$menu['stock_disponible'] <= 3 ? 'menu-meta__value--low' : '' ?>">
                                <?= (int)$menu['stock_disponible'] > 0
                                    ? ((int)$menu['stock_disponible'] <= 5 ? 'Seulement ' . (int)$menu['stock_disponible'] . ' dispo' : (int)$menu['stock_disponible'] . ' disponibles')
                                    : '<span class="text-danger">Rupture</span>' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Prix total indicatif -->
                <div class="menu-hero__price-indicative" id="priceIndicative">
                    <span class="menu-hero__price-label">Total pour <?= (int)$menu['nb_personne_min'] ?> personnes :</span>
                    <span class="menu-hero__price-value" id="totalPrice">
                        <?= number_format((float)$menu['prix_base_min'] * (int)$menu['nb_personne_min'], 2, ',', ' ') ?> €
                    </span>
                </div>

                <!-- Sélecteur nombre de personnes -->
                <div class="menu-hero__quantity">
                    <label for="quantity" class="menu-hero__quantity-label">Nombre de personnes :</label>
                    <div class="menu-hero__quantity-input">
                        <button type="button" class="qty-btn qty-btn--minus" aria-label="Diminuer" id="qtyMinus">−</button>
                        <input type="number"
                            id="quantity"
                            name="quantity"
                            class="qty-input"
                            value="<?= (int)$menu['nb_personne_min'] ?>"
                            min="<?= (int)$menu['nb_personne_min'] ?>"
                            max="<?= (int)$menu['stock_disponible'] > 0 ? (int)$menu['stock_disponible'] : 50 ?>"
                            required
                            aria-describedby="qty-hint">
                        <button type="button" class="qty-btn qty-btn--plus" aria-label="Augmenter" id="qtyPlus">+</button>
                    </div>
                    <span class="form-hint" id="qty-hint">Minimum <?= (int)$menu['nb_personne_min'] ?> personnes <?= (int)$menu['stock_disponible'] > 0 ? '· Max ' . (int)$menu['stock_disponible'] : '' ?></span>
                </div>

                <!-- CTA Principal -->
                <div class="menu-hero__cta">
                    <?php if ($isLoggedIn): ?>
                        <a href="commander.php?menu_id=<?= $menuId ?>" class="btn btn--primary btn--lg btn--fullwidth" id="btnCommander">
                            <span>Commander ce menu</span>
                            <span class="btn__arrow" aria-hidden="true">→</span>
                        </a>
                    <?php else: ?>
                        <a href="connexion.php?redirect=commander.php%3Fmenu_id%3D<?= $menuId ?>" class="btn btn--primary btn--lg btn--fullwidth">
                            <span>Se connecter pour commander</span>
                        </a>
                        <p class="menu-hero__cta-note">Déjà client ? <a href="connexion.php?redirect=commander.php%3Fmenu_id%3D<?= $menuId ?>">Connectez-vous</a> ou <a href="inscription.php">créez un compte</a></p>
                    <?php endif; ?>

                    <a href="menus.php" class="btn btn--outline btn--lg btn--fullwidth">
                        ← Retour aux menus
                    </a>
                </div>
            </div>
        </header>

        <!-- CONTENU DÉTAILLÉ -->
        <div class="menu-detail">
            <div class="menu-detail__container">
                <!-- Colonne principale : Composition & Allergènes -->
                <div class="menu-detail__main">
                    <!-- Description complète -->
                    <section class="menu-section" aria-labelledby="desc-title">
                        <h2 id="desc-title" class="menu-section__title">Description complète</h2>
                        <div class="menu-section__content">
                            <p><?= nl2br(htmlspecialchars($menu['description'])) ?></p>
                        </div>
                    </section>

                    <!-- Composition des plats -->
                    <section class="menu-section" aria-labelledby="plats-title">
                        <div class="menu-section__header">
                            <h2 id="plats-title" class="menu-section__title">Composition du menu</h2>
                            <p class="menu-section__subtitle">Entrées, plats et desserts inclus</p>
                        </div>

                        <div class="menu-plats" id="platsContainer">
                            <?php if (!empty($menu['plats'])): ?>
                                <?php
                                $platsByType = ['entree' => [], 'plat' => [], 'dessert' => []];
                                foreach ($menu['plats'] as $plat) {
                                    $platsByType[$plat['type_plat']][] = $plat;
                                }
                                $typeLabels = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
                                $typeIcons = ['entree' => '🥗', 'plat' => '🍽️', 'dessert' => '🍰'];
                                ?>
                                <?php foreach (['entree', 'plat', 'dessert'] as $type): ?>
                                    <?php if (!empty($platsByType[$type])): ?>
                                        <div class="plats-group">
                                            <h3 class="plats-group__title">
                                                <span class="plats-group__icon" aria-hidden="true"><?= $typeIcons[$type] ?></span>
                                                <?= $typeLabels[$type] ?>
                                            </h3>
                                            <ul class="plats-list">
                                                <?php foreach ($platsByType[$type] as $plat): ?>
                                                    <li class="plat-item">
                                                        <div class="plat-item__main">
                                                            <h4 class="plat-item__title"><?= htmlspecialchars($plat['titre']) ?></h4>
                                                            <?php if ($plat['description']): ?>
                                                                <p class="plat-item__desc"><?= htmlspecialchars($plat['description']) ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($plat['allergenes'])): ?>
                                                            <div class="plat-item__allergenes" aria-label="Allergènes : <?= implode(', ', array_column($plat['allergenes'], 'libelle')) ?>">
                                                                <?php foreach ($plat['allergenes'] as $allergene): ?>
                                                                    <span class="allergen-tag" title="<?= htmlspecialchars($allergene['libelle']) ?>">
                                                                        <?= htmlspecialchars($allergene['libelle']) ?>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="menu-section__empty">Composition non disponible pour le moment.</p>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Allergènes globaux -->
                    <?php
                    // Collecter tous les allergènes uniques
                    $allAllergenes = [];
                    if (!empty($menu['plats'])) {
                        foreach ($menu['plats'] as $plat) {
                            if (!empty($plat['allergenes'])) {
                                foreach ($plat['allergenes'] as $a) {
                                    $allAllergenes[$a['allergene_id']] = $a['libelle'];
                                }
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($allAllergenes)): ?>
                        <section class="menu-section" aria-labelledby="allergenes-title">
                            <h2 id="allergenes-title" class="menu-section__title">Allergènes présents dans ce menu</h2>
                            <div class="menu-section__content">
                                <div class="allergenes-grid">
                                    <?php foreach ($allAllergenes as $libelle): ?>
                                        <span class="allergen-tag allergen-tag--large"><?= htmlspecialchars($libelle) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <p class="allergenes-notice">
                                    <strong>Important :</strong> Nos cuisines manipulent l'ensemble des allergènes réglementaires.
                                    Malgré notre vigilance, des traces croisées sont possibles.
                                    Pour toute allergie sévère, merci de nous contacter directement avant commande.
                                </p>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Conditions délai & stockage -->
                    <section class="menu-section menu-section--highlight" aria-labelledby="conditions-title">
                        <h2 id="conditions-title" class="menu-section__title">⚠️ Conditions importantes</h2>
                        <div class="menu-section__content">
                            <div class="conditions-box">
                                <?= nl2br(htmlspecialchars($menu['conditions_delai_stockage'])) ?>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Colonne latérale : Résumé commande -->
                <aside class="menu-detail__sidebar" aria-labelledby="sidebar-title">
                    <div class="order-summary" id="orderSummary">
                        <h2 id="sidebar-title" class="order-summary__title">Votre commande</h2>

                        <div class="order-summary__menu">
                            <img src="<?= htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.webp') ?>" alt="" class="order-summary__img" loading="lazy">
                            <div class="order-summary__info">
                                <h3><?= htmlspecialchars($menu['titre']) ?></h3>
                                <p class="order-summary__meta">
                                    <span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span>
                                    <span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="order-summary__divider"></div>

                        <div class="order-summary__quantity">
                            <label for="sidebarQuantity">Personnes</label>
                            <div class="qty-input-group">
                                <button type="button" class="qty-btn qty-btn--minus" aria-label="Diminuer" id="sidebarQtyMinus">−</button>
                                <input type="number"
                                    id="sidebarQuantity"
                                    class="qty-input qty-input--center"
                                    value="<?= (int)$menu['nb_personne_min'] ?>"
                                    min="<?= (int)$menu['nb_personne_min'] ?>"
                                    max="<?= (int)$menu['stock_disponible'] > 0 ? (int)$menu['stock_disponible'] : 50 ?>"
                                    readonly>
                                <button type="button" class="qty-btn qty-btn--plus" aria-label="Augmenter" id="sidebarQtyPlus">+</button>
                            </div>
                        </div>

                        <div class="order-summary__pricing">
                            <div class="order-summary__line">
                                <span>Menu × <span id="summaryQty"><?= (int)$menu['nb_personne_min'] ?></span></span>
                                <span id="summaryMenuPrice"><?= number_format((float)$menu['prix_base_min'] * (int)$menu['nb_personne_min'], 2, ',', ' ') ?> €</span>
                            </div>

                            <div class="order-summary__line order-summary__line--delivery" id="deliveryLine">
                                <span>Livraison</span>
                                <span id="deliveryPrice">À calculer selon votre ville</span>
                            </div>

                            <div class="order-summary__line order-summary__line--discount" id="discountLine" hidden>
                                <span>Remise (10% si +5 pers)</span>
                                <span id="discountPrice" class="text-success">− 0,00 €</span>
                            </div>

                            <div class="order-summary__total">
                                <span>Total estimé</span>
                                <strong id="summaryTotal"><?= number_format((float)$menu['prix_base_min'] * (int)$menu['nb_personne_min'], 2, ',', ' ') ?> €</strong>
                            </div>
                        </div>

                        <div class="order-summary__cta">
                            <?php if ($isLoggedIn): ?>
                                <a href="commander.php?menu_id=<?= $menuId ?>" class="btn btn--primary btn--fullwidth btn--lg" id="sidebarCommander">
                                    Commander
                                </a>
                            <?php else: ?>
                                <a href="connexion.php?redirect=commander.php%3Fmenu_id%3D<?= $menuId ?>" class="btn btn--primary btn--fullwidth btn--lg">
                                    Se connecter pour commander
                                </a>
                            <?php endif; ?>
                        </div>

                        <p class="order-summary__note">
                            * Prix indicatif. Frais de livraison calculés au moment de la commande selon votre adresse.
                            Gratuit à Bordeaux intra-muros.
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Scripts Page Détail Menu -->
    <script>
        (function() {
            'use strict';

            // --- Données menu (injectées depuis PHP) ---
            const menuData = {
                id: <?= $menuId ?>,
                prixBase: <?= (float)$menu['prix_base_min'] ?>,
                nbPersonneMin: <?= (int)$menu['nb_personne_min'] ?>,
                stock: <?= (int)$menu['stock_disponible'] ?>
            };

            // --- Galerie miniatures ---
            const mainImage = document.getElementById('mainImage');
            const thumbnails = document.querySelectorAll('.menu-hero__thumb');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    const src = this.dataset.src;
                    const alt = this.dataset.alt;

                    if (mainImage && src) {
                        mainImage.src = src;
                        mainImage.alt = alt;
                    }

                    // État actif
                    thumbnails.forEach(t => {
                        t.classList.remove('is-active');
                        t.setAttribute('aria-pressed', 'false');
                    });
                    this.classList.add('is-active');
                    this.setAttribute('aria-pressed', 'true');
                });

                // Navigation clavier
                thumb.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // --- Calcul prix dynamique ---
            const qtyInput = document.getElementById('quantity');
            const sidebarQty = document.getElementById('sidebarQuantity');
            const qtyMinus = document.getElementById('qtyMinus');
            const qtyPlus = document.getElementById('qtyPlus');
            const sidebarQtyMinus = document.getElementById('sidebarQtyMinus');
            const sidebarQtyPlus = document.getElementById('sidebarQtyPlus');

            const totalPriceEl = document.getElementById('totalPrice');
            const summaryQtyEl = document.getElementById('summaryQty');
            const summaryMenuPriceEl = document.getElementById('summaryMenuPrice');
            const summaryTotalEl = document.getElementById('summaryTotal');
            const discountLine = document.getElementById('discountLine');
            const discountPriceEl = document.getElementById('discountPrice');
            const deliveryPriceEl = document.getElementById('deliveryPrice');

            function formatPrice(value) {
                return value.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
            }

            function updatePrices(qty) {
                const prixUnitaire = menuData.prixBase;
                const menuTotal = prixUnitaire * qty;

                // Remise 10% si qty >= min + 5
                const remiseSeuil = menuData.nbPersonneMin + 5;
                let remise = 0;
                if (qty >= remiseSeuil) {
                    remise = menuTotal * 0.10;
                }

                // Mise à jour affichage principal
                if (totalPriceEl) totalPriceEl.textContent = formatPrice(menuTotal);

                // Mise à jour sidebar
                if (summaryQtyEl) summaryQtyEl.textContent = qty;
                if (summaryMenuPriceEl) summaryMenuPriceEl.textContent = formatPrice(menuTotal);

                // Affichage remise
                if (discountLine && discountPriceEl) {
                    if (remise > 0) {
                        discountLine.hidden = false;
                        discountPriceEl.textContent = '− ' + formatPrice(remise);
                    } else {
                        discountLine.hidden = true;
                    }
                }

                // Total estimé (sans frais livraison)
                const totalEstime = menuTotal - remise;
                if (summaryTotalEl) summaryTotalEl.textContent = formatPrice(totalEstime);

                // Mise à jour boutons +/- état désactivé
                const maxQty = menuData.stock > 0 ? menuData.stock : 50;
                const minQty = menuData.nbPersonneMin;

                [qtyMinus, qtyPlus, sidebarQtyMinus, sidebarQtyPlus].forEach(btn => {
                    if (!btn) return;
                    if (btn.classList.contains('qty-btn--minus')) {
                        btn.disabled = qty <= minQty;
                    } else {
                        btn.disabled = qty >= maxQty;
                    }
                });
            }

            // Sync des deux inputs quantité
            function syncQty(value, source) {
                const numValue = parseInt(value, 10);
                if (isNaN(numValue)) return;

                const maxQty = menuData.stock > 0 ? menuData.stock : 50;
                const minQty = menuData.nbPersonneMin;
                const clamped = Math.max(minQty, Math.min(maxQty, numValue));

                if (qtyInput && source !== 'main') qtyInput.value = clamped;
                if (sidebarQty && source !== 'sidebar') sidebarQty.value = clamped;

                updatePrices(clamped);
            }

            // Event listeners quantité
            [qtyInput, sidebarQty].forEach(input => {
                if (input) {
                    input.addEventListener('change', function() {
                        syncQty(this.value, this === qtyInput ? 'main' : 'sidebar');
                    });
                    input.addEventListener('input', function() {
                        // Pour input direct, sync sans clamp immédiat
                        if (this === qtyInput) sidebarQty.value = this.value;
                        else qtyInput.value = this.value;
                    });
                }
            });

            // Boutons +/-
            function setupQtyButtons(minusBtn, plusBtn, source) {
                if (!minusBtn || !plusBtn) return;

                minusBtn.addEventListener('click', function() {
                    const input = source === 'main' ? qtyInput : sidebarQty;
                    if (!input) return;
                    const current = parseInt(input.value, 10) || menuData.nbPersonneMin;
                    if (current > menuData.nbPersonneMin) {
                        input.value = current - 1;
                        syncQty(input.value, source);
                    }
                });

                plusBtn.addEventListener('click', function() {
                    const input = source === 'main' ? qtyInput : sidebarQty;
                    if (!input) return;
                    const maxQty = menuData.stock > 0 ? menuData.stock : 50;
                    const current = parseInt(input.value, 10) || menuData.nbPersonneMin;
                    if (current < maxQty) {
                        input.value = current + 1;
                        syncQty(input.value, source);
                    }
                });
            }

            setupQtyButtons(qtyMinus, qtyPlus, 'main');
            setupQtyButtons(sidebarQtyMinus, sidebarQtyPlus, 'sidebar');

            // Initialisation
            updatePrices(menuData.nbPersonneMin);

            // --- Mise à jour lien commander avec quantité ---
            const commanderLinks = document.querySelectorAll('#btnCommander, #sidebarCommander');
            commanderLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const qty = parseInt(qtyInput?.value, 10) || menuData.nbPersonneMin;
                    this.href = this.href.split('?')[0] + '?menu_id=' + menuData.id + '&qty=' + qty;
                });
            });

        })();
    </script>
</body>

</html>