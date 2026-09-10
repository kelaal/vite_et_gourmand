<?php
/**
 * Page Succès Commande - Vite & Gourmand
 * ECF : Confirmation avec numéro de commande, récapitulatif, prochaines étapes
 */

require_once __DIR__ . '/config/require_auth.php';
require_once __DIR__ . '/repositories/CommandeRepository.php';

// Protection
requireAuth();

$commandeId = (int)($_GET['id'] ?? 0);
$userId = getCurrentUserId();

if (!$commandeId) {
    header('Location: espace.php');
    exit;
}

$commandeRepo = new CommandeRepository();
$commande = $commandeRepo->findById($commandeId);

// Vérification : la commande appartient à l'utilisateur
if (!$commande || $commande['utilisateur_id'] !== $userId) {
    header('Location: espace.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Commande confirmée - Vite & Gourmand">
    <title>Commande confirmée | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'espace';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-success">
        <div class="success-container">
            <!-- Animation succès -->
            <div class="success-animation" aria-hidden="true">
                <div class="success-checkmark">
                    <div class="checkmark-circle"></div>
                    <div class="checkmark-stem"></div>
                    <div class="checkmark-kick"></div>
                </div>
            </div>

            <header class="success-header">
                <h1>Commande confirmée !</h1>
                <p class="success-number">Numéro : <strong><?= htmlspecialchars($commande['numero_commande']) ?></strong></p>
                <p class="success-date">Passée le <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></p>
            </header>

            <div class="success-grid">
                <!-- Récapitulatif -->
                <section class="success-section">
                    <h2 class="success-section__title">Récapitulatif</h2>

                    <div class="success-menu">
                        <img src="<?= htmlspecialchars($commande['menu_image'] ?? 'public/img/presentation-photo.webp') ?>" alt="" class="success-menu__img" loading="lazy">
                        <div>
                            <h3><?= htmlspecialchars($commande['menu_titre']) ?></h3>
                            <p class="success-menu__meta">
                                <?= (int)$commande['nb_personnes'] ?> personne(s) · <?= date('d/m/Y', strtotime($commande['date_prestation'])) ?> à <?= substr($commande['heure_prestation'], 0, 5) ?>
                            </p>
                        </div>
                    </div>

                    <dl class="success-dl">
                        <div>
                            <dt>Adresse de livraison</dt>
                            <dd><?= htmlspecialchars($commande['adresse_prestation']) ?>, <?= htmlspecialchars($commande['ville_prestation']) ?></dd>
                        </div>
                        <div>
                            <dt>Total payé</dt>
                            <dd class="text-terracotta"><?= number_format((float)$commande['prix_total'], 2, ',', ' ') ?> €</dd>
                        </div>
                        <?php if ((float)$commande['remise_appliquee'] > 0): ?>
                            <div>
                                <dt>Remise appliquée</dt>
                                <dd class="text-success">− <?= number_format((float)$commande['remise_appliquee'], 2, ',', ' ') ?> €</dd>
                            </div>
                        <?php endif; ?>
                        <?php if ((float)$commande['frais_livraison'] > 0): ?>
                            <div>
                                <dt>Frais de livraison</dt>
                                <dd><?= number_format((float)$commande['frais_livraison'], 2, ',', ' ') ?> €</dd>
                            </div>
                        <?php else: ?>
                            <div>
                                <dt>Frais de livraison</dt>
                                <dd class="text-success">Offert (Bordeaux)</dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </section>

                <!-- Prochaines étapes -->
                <section class="success-section success-section--steps">
                    <h2 class="success-section__title">Prochaines étapes</h2>

                    <ol class="steps-list">
                        <li class="step-item">
                            <span class="step-item__number">1</span>
                            <div class="step-item__content">
                                <h3>Confirmation par email</h3>
                                <p>Vous avez reçu un email de confirmation avec le récapitulatif complet.</p>
                            </div>
                        </li>
                        <li class="step-item">
                            <span class="step-item__number">2</span>
                            <div class="step-item__content">
                                <h3>Validation par notre équipe</h3>
                                <p>Nous vérifions la disponibilité et vous confirmons sous 24h (statut : <strong>Acceptée</strong>).</p>
                            </div>
                        </li>
                        <li class="step-item">
                            <span class="step-item__number">3</span>
                            <div class="step-item__content">
                                <h3>Préparation & Livraison</h3>
                                <p>Votre commande est préparée le jour J et livrée à l'heure convenue.</p>
                            </div>
                        </li>
                        <li class="step-item">
                            <span class="step-item__number">4</span>
                            <div class="step-item__content">
                                <h3>Partagez votre avis</h3>
                                <p>Après la prestation, vous pourrez noter votre expérience depuis votre espace.</p>
                            </div>
                        </li>
                    </ol>
                </section>
            </div>

            <!-- Actions -->
            <div class="success-actions">
                <a href="espace.php#mes-commandes" class="btn btn--primary btn--lg">
                    <span>Voir mes commandes</span>
                    <span class="btn__arrow" aria-hidden="true">→</span>
                </a>
                <a href="menus.php" class="btn btn--outline btn--lg">Commander un autre menu</a>
            </div>

            <!-- Info paiement -->
            <div class="success-payment-info">
                <h3>💳 Rappel : Paiement</h3>
                <p>Le règlement s'effectue <strong>le jour de la prestation</strong> directement à notre équipe de livraison :</p>
                <ul>
                    <li>Espèces (appoint apprécié)</li>
                    <li>Carte bancaire (terminal mobile)</li>
                    <li>Chèque à l'ordre de "Vite & Gourmand"</li>
                    <li>Virement anticipé (RIB sur demande)</li>
                </ul>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>