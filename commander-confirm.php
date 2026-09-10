<?php
/**
 * Page Confirmation Commande - Vite & Gourmand
 * ECF : Récapitulatif détaillé final avant validation définitive
 */

require_once __DIR__ . '/config/require_auth.php';
require_once __DIR__ . '/repositories/MenuRepository.php';

// Protection
requireAuth();

// Récupération données en session
$pending = $_SESSION['commande_pending'] ?? null;

if (!$pending) {
    header('Location: menus.php');
    exit;
}

$formData = $pending['form_data'];
$calculation = $pending['calculation'];
$menu = $pending['menu'];
$user = $pending['user'];

$csrfToken = generateCsrfToken();

// Traitement confirmation finale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    if (isset($_POST['confirm']) && $_POST['confirm'] === '1') {
        // Créer la commande
        require_once __DIR__ . '/repositories/CommandeRepository.php';

        $commandeRepo = new CommandeRepository();

        $commandeData = [
            'date_prestation' => $formData['date_prestation'],
            'heure_prestation' => $formData['heure_prestation'],
            'adresse_prestation' => $formData['adresse_prestation'],
            'ville_prestation' => $formData['ville_prestation'],
            'distance_km' => $calculation['distance_km'],
            'nb_personnes' => $formData['nb_personnes'],
            'prix_menu_unitaire' => $calculation['prix_menu_unitaire'],
            'remise_appliquee' => $calculation['remise_appliquee'],
            'frais_livraison' => $calculation['frais_livraison'],
            'prix_total' => $calculation['prix_total'],
            'pret_materiel' => $formData['pret_materiel'],
            'utilisateur_id' => $user['utilisateur_id'],
            'menu_id' => $menu['menu_id']
        ];

        try {
            $commandeId = $commandeRepo->create($commandeData);

            // Nettoyer la session
            unset($_SESSION['commande_pending']);

            // Redirection vers confirmation succès
            header('Location: commander-success.php?id=' . $commandeId);
            exit;

        } catch (Exception $e) {
            $error = 'Erreur lors de la création de la commande : ' . $e->getMessage();
        }
    } else {
        // Retour au formulaire
        header('Location: commander.php?menu_id=' . $menu['menu_id'] . '&qty=' . $formData['nb_personnes']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Confirmation de commande - Vite & Gourmand">
    <title>Confirmer ma commande | Vite & Gourmand</title>

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

    <main class="main-confirm">
        <div class="confirm-container">
            <!-- En-tête -->
            <header class="confirm-header">
                <div class="confirm-steps">
                    <div class="step step--done">
                        <span class="step__number">1</span>
                        <span class="step__label">Menu & Quantité</span>
                    </div>
                    <div class="step__line"></div>
                    <div class="step step--done">
                        <span class="step__number">2</span>
                        <span class="step__label">Livraison</span>
                    </div>
                    <div class="step__line"></div>
                    <div class="step step--current">
                        <span class="step__number">3</span>
                        <span class="step__label">Confirmation</span>
                    </div>
                </div>
                <h1>Confirmer ma commande</h1>
                <p>Vérifiez tous les détails avant de valider définitivement</p>
            </header>

            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Récapitulatif détaillé -->
            <div class="confirm-grid">
                <!-- Colonne gauche : Détails -->
                <div class="confirm-details">
                    <!-- Menu -->
                    <section class="confirm-section">
                        <h2 class="confirm-section__title">
                            <span class="icon">🍽️</span>
                            Menu commandé
                        </h2>
                        <div class="confirm-menu">
                            <img src="<?= htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.webp') ?>" alt="" class="confirm-menu__img" loading="lazy">
                            <div class="confirm-menu__info">
                                <h3><?= htmlspecialchars($menu['titre']) ?></h3>
                                <div class="confirm-menu__meta">
                                    <span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span>
                                    <span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span>
                                </div>
                                <p class="confirm-menu__desc"><?= htmlspecialchars($menu['description']) ?></p>
                            </div>
                        </div>
                    </section>

                    <!-- Livraison -->
                    <section class="confirm-section">
                        <h2 class="confirm-section__title">
                            <span class="icon">🚚</span>
                            Livraison
                        </h2>
                        <dl class="confirm-dl">
                            <div>
                                <dt>Date</dt>
                                <dd><?= date('d/m/Y', strtotime($formData['date_prestation'])) ?></dd>
                            </div>
                            <div>
                                <dt>Heure</dt>
                                <dd><?= substr($formData['heure_prestation'], 0, 5) ?></dd>
                            </div>
                            <div>
                                <dt>Adresse</dt>
                                <dd><?= htmlspecialchars($formData['adresse_prestation']) ?>, <?= htmlspecialchars($formData['code_postal']) ?> <?= htmlspecialchars($formData['ville_prestation']) ?></dd>
                            </div>
                            <?php if ($formData['pret_materiel']): ?>
                                <div>
                                    <dt>Matériel</dt>
                                    <dd><span class="text-warning">⚠️ Prêt de matériel inclus (retour sous 10 j. ouvrés)</span></dd>
                                </div>
                            <?php endif; ?>
                            <?php if ($formData['instructions']): ?>
                                <div>
                                    <dt>Instructions</dt>
                                    <dd><?= htmlspecialchars($formData['instructions']) ?></dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                    </section>

                    <!-- Client -->
                    <section class="confirm-section">
                        <h2 class="confirm-section__title">
                            <span class="icon">👤</span>
                            Client
                        </h2>
                        <dl class="confirm-dl">
                            <div>
                                <dt>Nom</dt>
                                <dd><?= htmlspecialchars($formData['nom'] ?? $user['nom']) ?> <?= htmlspecialchars($formData['prenom'] ?? $user['prenom']) ?></dd>
                            </div>
                            <div>
                                <dt>Email</dt>
                                <dd><?= htmlspecialchars($user['email']) ?></dd>
                            </div>
                            <div>
                                <dt>Téléphone</dt>
                                <dd><?= htmlspecialchars($formData['gsm'] ?? $user['gsm']) ?></dd>
                            </div>
                        </dl>
                    </section>

                    <!-- Conditions -->
                    <section class="confirm-section confirm-section--conditions">
                        <h2 class="confirm-section__title">
                            <span class="icon">⚠️</span>
                            Conditions du menu
                        </h2>
                        <div class="conditions-box">
                            <?= nl2br(htmlspecialchars($menu['conditions_delai_stockage'])) ?>
                        </div>
                    </section>
                </div>

                <!-- Colonne droite : Facturation -->
                <aside class="confirm-summary" aria-labelledby="confirm-summary-title">
                    <div class="summary-card summary-card--confirm">
                        <h2 id="confirm-summary-title" class="summary-card__title">Détail de la facturation</h2>

                        <div class="summary-detail">
                            <div class="summary-detail__line">
                                <span class="summary-detail__label">Menu</span>
                                <span class="summary-detail__value"><?= htmlspecialchars($menu['titre']) ?></span>
                            </div>
                            <div class="summary-detail__line">
                                <span class="summary-detail__label">Prix unitaire</span>
                                <span class="summary-detail__value"><?= number_format($calculation['prix_menu_unitaire'], 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="summary-detail__line">
                                <span class="summary-detail__label">Quantité</span>
                                <span class="summary-detail__value"><?= (int)$formData['nb_personnes'] ?> personne(s)</span>
                            </div>
                            <div class="summary-detail__line summary-detail__line--subtotal">
                                <span class="summary-detail__label">Sous-total menu</span>
                                <span class="summary-detail__value"><?= number_format($calculation['prix_menu_total'], 2, ',', ' ') ?> €</span>
                            </div>

                            <!-- Remise -->
                            <?php if ($calculation['remise_appliquee'] > 0): ?>
                                <div class="summary-detail__line summary-detail__line--discount">
                                    <span class="summary-detail__label">
                                        Remise 10% (≥ <?= $calculation['remise_seuil'] ?> pers.)
                                    </span>
                                    <span class="summary-detail__value text-success">− <?= number_format($calculation['remise_appliquee'], 2, ',', ' ') ?> €</span>
                                </div>
                            <?php else: ?>
                                <div class="summary-detail__line summary-detail__line--discount summary-detail__line--muted">
                                    <span class="summary-detail__label">Remise 10%</span>
                                    <span class="summary-detail__value text-muted">Non applicable (< <?= $calculation['remise_seuil'] ?> pers.)</span>
                                </div>
                            <?php endif; ?>

                            <!-- Frais livraison -->
                            <div class="summary-detail__line summary-detail__line--delivery">
                                <span class="summary-detail__label">Frais de livraison</span>
                                <?php if ($calculation['is_bordeaux']): ?>
                                    <span class="summary-detail__value text-success">Offert (Bordeaux)</span>
                                <?php else: ?>
                                    <span class="summary-detail__value">
                                        <?= number_format($calculation['frais_livraison'], 2, ',', ' ') ?> €
                                        <span class="delivery-breakdown">
                                            (5,00 € + <?= number_format($calculation['distance_km'], 1, ',', ' ') ?> km × 0,59 €)
                                        </span>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Total -->
                            <div class="summary-detail__total">
                                <span class="summary-detail__label">Total TTC</span>
                                <span class="summary-detail__value summary-detail__value--total"><?= number_format($calculation['prix_total'], 2, ',', ' ') ?> €</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="confirm-actions">
                            <a href="commander.php?menu_id=<?= $menu['menu_id'] ?>&qty=<?= $formData['nb_personnes'] ?>" class="btn btn--outline btn--lg btn--fullwidth">
                                ← Modifier
                            </a>
                            <form method="POST" class="confirm-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="confirm" value="1">
                                <button type="submit" class="btn btn--primary btn--lg btn--fullwidth btn--confirm">
                                    <span>Valider et payer sur place</span>
                                    <span class="btn__arrow" aria-hidden="true">→</span>
                                </button>
                            </form>
                            <p class="confirm-note">
                                <strong>Paiement :</strong> Sur place le jour J (espèces, carte bancaire, chèque) ou virement anticipé.
                                <br>Un email de confirmation vous sera envoyé immédiatement.
                            </p>
                        </div>

                        <!-- Sécurité -->
                        <div class="confirm-security">
                            <div class="security-item">
                                <span class="security-icon">🔒</span>
                                <span>Transaction sécurisée</span>
                            </div>
                            <div class="security-item">
                                <span class="security-icon">📧</span>
                                <span>Confirmation par email</span>
                            </div>
                            <div class="security-item">
                                <span class="security-icon">🛡️</span>
                                <span>Données protégées RGPD</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>