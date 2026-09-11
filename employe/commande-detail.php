<?php
/**
 * Détail Commande - Espace Employé/Admin
 * Affiche le détail complet d'une commande pour l'équipe
 */

require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/CommandeRepository.php';

requireEmploye();

$commandeId = (int)($_GET['id'] ?? 0);

if (!$commandeId) {
    header('Location: dashboard.php');
    exit;
}

$commandeRepo = new CommandeRepository();
$commande = $commandeRepo->findById($commandeId);

if (!$commande) {
    header('Location: dashboard.php');
    exit;
}

// Historique
$historique = $commandeRepo->getHistoriqueStatut($commandeId);

$statutLabels = [
    'en attente' => 'En attente',
    'accepte' => 'Acceptée',
    'en preparation' => 'En préparation',
    'en cours de livraison' => 'En cours de livraison',
    'livre' => 'Livrée',
    'en attente du retour de materiel' => 'En attente du retour matériel',
    'terminee' => 'Terminée',
    'annulee' => 'Annulée'
];

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Détail commande - Vite & Gourmand">
    <title>Commande <?= htmlspecialchars($commande['numero_commande']) ?> | Vite & Gourmand</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <?php
    $activePage = 'employe';
    require_once __DIR__ . '/../includes/navbar.php';
    ?>

    <main class="main-admin">
        <div class="admin-container">
            <aside class="admin-sidebar" id="adminSidebar">
                <div class="admin-user">
                    <div class="admin-user__avatar">↩</div>
                    <h3>Retour</h3>
                </div>
                <nav class="admin-nav">
                    <ul>
                        <li>
                            <a href="dashboard.php" class="admin-nav__link">
                                <span class="icon">←</span> Tableau de bord
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <div class="admin-content">
                <button class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Ouvrir le menu">
                    <span class="icon">☰</span> Menu
                </button>

                <header class="admin-header">
                    <h1 class="admin-header__title">Commande <?= htmlspecialchars($commande['numero_commande']) ?></h1>
                    <p class="admin-header__subtitle">Détail & Suivi</p>
                </header>

                <?php
                if (!empty($_SESSION['flash_success'])) {
                    echo '<div class="alert alert--success" role="alert">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
                    unset($_SESSION['flash_success']);
                }
                if (!empty($_SESSION['flash_error'])) {
                    echo '<div class="alert alert--error" role="alert">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
                    unset($_SESSION['flash_error']);
                }
                ?>

                <div class="detail-grid">
                    <!-- Colonne principale -->
                    <div class="detail-main">
                        <!-- Infos générales -->
                        <section class="detail-section">
                            <h2 class="detail-section__title">Informations générales</h2>
                            <dl class="detail-dl">
                                <div>
                                    <dt>Numéro</dt>
                                    <dd><strong><?= htmlspecialchars($commande['numero_commande']) ?></strong></dd>
                                </div>
                                <div>
                                    <dt>Date commande</dt>
                                    <dd><?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></dd>
                                </div>
                                <div>
                                    <dt>Statut actuel</dt>
                                    <dd>
                                        <span class="badge badge--statut badge--<?= strtolower(str_replace(' ', '-', $commande['statut'])) ?>">
                                            <?= $statutLabels[$commande['statut']] ?? htmlspecialchars(ucfirst($commande['statut'])) ?>
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt>Date prestation</dt>
                                    <dd><?= date('d/m/Y', strtotime($commande['date_prestation'])) ?> à <?= substr($commande['heure_prestation'], 0, 5) ?></dd>
                                </div>
                                <div>
                                    <dt>Adresse</dt>
                                    <dd><?= htmlspecialchars($commande['adresse_prestation']) ?>, <?= htmlspecialchars($commande['code_postal'] ?? '') ?> <?= htmlspecialchars($commande['ville_prestation']) ?></dd>
                                </div>
                                <?php if ((float)$commande['distance_km'] > 0): ?>
                                    <div>
                                        <dt>Distance</dt>
                                        <dd><?= number_format((float)$commande['distance_km'], 1, ',', ' ') ?> km</dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        </section>

                        <!-- Client -->
                        <section class="detail-section">
                            <h2 class="detail-section__title">Client</h2>
                            <dl class="detail-dl">
                                <div>
                                    <dt>Nom</dt>
                                    <dd><?= htmlspecialchars($commande['client_prenom'] . ' ' . $commande['client_nom']) ?></dd>
                                </div>
                                <div>
                                    <dt>Email</dt>
                                    <dd><a href="mailto:<?= htmlspecialchars($commande['client_email']) ?>"><?= htmlspecialchars($commande['client_email']) ?></a></dd>
                                </div>
                                <div>
                                    <dt>Téléphone</dt>
                                    <dd><a href="tel:<?= htmlspecialchars($commande['client_gsm']) ?>"><?= htmlspecialchars($commande['client_gsm']) ?></a></dd>
                                </div>
                            </dl>
                        </section>

                        <!-- Menu -->
                        <section class="detail-section">
                            <h2 class="detail-section__title">Menu commandé</h2>
                            <dl class="detail-dl">
                                <div>
                                    <dt>Menu</dt>
                                    <dd><?= htmlspecialchars($commande['menu_titre']) ?></dd>
                                </div>
                                <div>
                                    <dt>Description</dt>
                                    <dd><?= htmlspecialchars($commande['menu_description'] ?? '—') ?></dd>
                                </div>
                                <div>
                                    <dt>Personnes</dt>
                                    <dd><?= (int)$commande['nb_personnes'] ?></dd>
                                </div>
                                <div>
                                    <dt>Prix unitaire</dt>
                                    <dd><?= number_format((float)$commande['prix_menu_unitaire'], 2, ',', ' ') ?> €</dd>
                                </div>
                            </dl>
                        </section>

                        <!-- Facturation -->
                        <section class="detail-section">
                            <h2 class="detail-section__title">Facturation</h2>
                            <table class="detail-table">
                                <tbody>
                                    <tr>
                                        <td>Menu (<?= (int)$commande['nb_personnes'] ?> × <?= number_format((float)$commande['prix_menu_unitaire'], 2, ',', ' ') ?> €)</td>
                                        <td><?= number_format((float)$commande['prix_menu_unitaire'] * (int)$commande['nb_personnes'], 2, ',', ' ') ?> €</td>
                                    </tr>
                                    <?php if ((float)$commande['remise_appliquee'] > 0): ?>
                                        <tr class="remise">
                                            <td>Remise 10% (≥ <?= (int)$commande['nb_personnes'] ?> pers.)</td>
                                            <td>- <?= number_format((float)$commande['remise_appliquee'], 2, ',', ' ') ?> €</td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="livraison">
                                        <td>Frais de livraison
                                            <?php if ((float)$commande['frais_livraison'] > 0): ?>
                                                (<?= number_format((float)$commande['distance_km'], 1, ',', ' ') ?> km × 0,59 € + 5 €)
                                            <?php else: ?>
                                                (Bordeaux - Offert)
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ((float)$commande['frais_livraison'] > 0): ?>
                                                <?= number_format((float)$commande['frais_livraison'], 2, ',', ' ') ?> €
                                            <?php else: ?>
                                                <span class="text-success">Offert</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr class="total">
                                        <td><strong>Total TTC</strong></td>
                                        <td><strong><?= number_format((float)$commande['prix_total'], 2, ',', ' ') ?> €</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        <!-- Matériel -->
                        <?php if ($commande['pret_materiel']): ?>
                            <section class="detail-section">
                                <h2 class="detail-section__title">Matériel</h2>
                                <div class="alert alert--warning">
                                    <strong>⚠️ Prêt de matériel inclus</strong>
                                    <br>Restitution sous 10 jours ouvrés après la prestation.
                                    <?php if ($commande['restitution_materiel']): ?>
                                        <br><span class="text-success">✅ Matériel restitué le <?= date('d/m/Y', strtotime($commande['date_restitution'] ?? $commande['date_prestation'])) ?></span>
                                    <?php else: ?>
                                        <br><span class="text-warning">En attente de restitution</span>
                                    <?php endif; ?>
                                    <br>Pénalité 600 € si non restitué (CGV).
                                </div>
                            </section>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar Actions -->
                    <aside class="detail-sidebar">
                        <div class="detail-card">
                            <h3>Actions</h3>
                            <form method="POST" action="../actions/employe/change-statut.php" class="detail-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="commande_id" value="<?= (int)$commande['commande_id'] ?>">

                                <div class="form-group">
                                    <label for="nouveau_statut">Changer le statut</label>
                                    <select id="nouveau_statut" name="nouveau_statut" class="form-input" onchange="toggleAnnulationFields(this.value)">
                                        <option value="en attente" <?= $commande['statut'] === 'en attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="accepte" <?= $commande['statut'] === 'accepte' ? 'selected' : '' ?>>Acceptée</option>
                                        <option value="en preparation" <?= $commande['statut'] === 'en preparation' ? 'selected' : '' ?>>En préparation</option>
                                        <option value="en cours de livraison" <?= $commande['statut'] === 'en cours de livraison' ? 'selected' : '' ?>>En cours de livraison</option>
                                        <option value="livre" <?= $commande['statut'] === 'livre' ? 'selected' : '' ?>>Livrée</option>
                                        <option value="en attente du retour de materiel" <?= $commande['statut'] === 'en attente du retour de materiel' ? 'selected' : '' ?>>Attente retour matériel</option>
                                        <option value="terminee" <?= $commande['statut'] === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                                        <option value="annulee" <?= $commande['statut'] === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                                    </select>
                                </div>

                                <div class="form-group" id="annulation_fields" hidden>
                                    <label for="motif_annulation">Motif d'annulation *</label>
                                    <textarea name="motif_annulation" id="motif_annulation" class="form-input" rows="3" required placeholder="Motif obligatoire pour annulation"></textarea>
                                </div>

                                <div class="form-group" id="contact_fields" hidden>
                                    <label for="contact_mode_annulation">Mode de contact *</label>
                                    <select name="contact_mode_annulation" id="contact_mode_annulation" class="form-input" required>
                                        <option value="">-- Choisir --</option>
                                        <option value="gsm">Téléphone (GSM)</option>
                                        <option value="mail">Email</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn--primary btn--fullwidth">Valider le changement</button>
                            </form>

                            <div class="detail-divider"></div>

                            <?php if (in_array($commande['statut'], ['en attente', 'accepte'], true)): ?>
                                <form method="POST" action="../actions/employe/annuler-commande.php" class="detail-form" onsubmit="return confirm('Confirmer l\'annulation définitive ?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="commande_id" value="<?= (int)$commande['commande_id'] ?>">
                                    <h4 style="margin-bottom: 12px; color: var(--Terracotta);">Annuler la commande</h4>
                                    <div class="form-group">
                                        <label for="cancel_motif">Motif *</label>
                                        <textarea name="motif_annulation" id="cancel_motif" class="form-input" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="cancel_contact">Mode de contact *</label>
                                        <select name="contact_mode_annulation" id="cancel_contact" class="form-input" required>
                                            <option value="">-- Choisir --</option>
                                            <option value="gsm">Téléphone (GSM)</option>
                                            <option value="mail">Email</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn--danger btn--fullwidth">Annuler la commande</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <!-- Historique -->
                        <div class="detail-card">
                            <h3>Historique des statuts</h3>
                            <div class="detail-history">
                                <?php foreach ($historique as $index => $h): ?>
                                    <div class="detail-history__item">
                                        <div class="detail-history__dot"></div>
                                        <div class="detail-history__content">
                                            <div class="detail-history__status">
                                                <?= $statutLabels[$h['statut']] ?? htmlspecialchars(ucfirst($h['statut'])) ?>
                                            </div>
                                            <div class="detail-history__meta">
                                                <?= date('d/m/Y à H:i', strtotime($h['date_changement'])) ?>
                                                <?php if ($h['auteur_prenom']): ?>
                                                    par <?= htmlspecialchars($h['auteur_prenom']) ?> (<?= htmlspecialchars(ucfirst($h['auteur_role'] ?? 'client')) ?>)
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script>
        (function() {
            // Toggle sidebar mobile
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.getElementById('adminMobileToggle');
            if (sidebar && toggle) {
                toggle.addEventListener('click', () => {
                    const open = sidebar.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', open);
                });
            }

            // Toggle champs annulation
            const statutSelect = document.getElementById('nouveau_statut');
            const annulationFields = document.getElementById('annulation_fields');
            const contactFields = document.getElementById('contact_fields');

            function toggleAnnulationFields(statut) {
                const isAnnulation = statut === 'annulee';
                if (annulationFields) annulationFields.hidden = !isAnnulation;
                if (contactFields) contactFields.hidden = !isAnnulation;
                const motifInput = document.getElementById('motif_annulation');
                const contactInput = document.getElementById('contact_mode_annulation');
                if (motifInput) motifInput.required = isAnnulation;
                if (contactInput) contactInput.required = isAnnulation;
            }

            if (statutSelect) {
                statutSelect.addEventListener('change', () => toggleAnnulationFields(statutSelect.value));
                // Init
                toggleAnnulationFields(statutSelect.value);
            }
        })();
    </script>
</body>

</html>