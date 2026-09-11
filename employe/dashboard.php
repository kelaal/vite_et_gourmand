<?php
/**
 * Dashboard Employé - Vite & Gourmand
 * ECF : Gestion commandes, modération avis, catalogue menus/plats/allergènes/horaires
 */

require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/CommandeRepository.php';
require_once __DIR__ . '/../repositories/AvisRepository.php';
require_once __DIR__ . '/../repositories/MenuRepository.php';
require_once __DIR__ . '/../repositories/HoraireRepository.php';
require_once __DIR__ . '/../repositories/UtilisateurRepository.php';

// Protection : employé ou admin seulement
requireEmploye();

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();
$userPrenom = getCurrentUserPrenom();

$commandeRepo = new CommandeRepository();
$avisRepo = new AvisRepository();
$menuRepo = new MenuRepository();
$horaireRepo = new HoraireRepository();

// Récupération stats pour dashboard
$stats = [
    'commandes_attente' => count($commandeRepo->findAllFiltered('en attente')),
    'commandes_preparation' => count($commandeRepo->findAllFiltered('en preparation')),
    'commandes_livraison' => count($commandeRepo->findAllFiltered('en cours de livraison')),
    'commandes_livrees' => count($commandeRepo->findAllFiltered('livre')),
    'avis_attente' => count($avisRepo->findAll('EN_ATTENTE')),
    'menus_actifs' => count($menuRepo->getPdo()->query("SELECT * FROM menu WHERE is_active = 1")->fetchAll()),
];

// Filtres commandes
$filtreStatut = $_GET['statut'] ?? '';
$filtreClient = $_GET['client_id'] ?? '';
$commandes = $commandeRepo->findAllFiltered($filtreStatut ?: null, $filtreClient ?: null);

// Avis en attente
$avisEnAttente = $avisRepo->findAll('EN_ATTENTE');

// Menus pour gestion
$menus = $menuRepo->findAllFiltered(['is_active' => 1]);

// Horaires
$horaires = $horaireRepo->findAll();

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace Employé - Vite & Gourmand">
    <title>Espace Équipe | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'employe';
    require_once __DIR__ . '/../includes/navbar.php';
    ?>

    <main class="main-admin">
        <div class="admin-container">
            <!-- Sidebar Navigation -->
            <aside class="admin-sidebar" id="adminSidebar">
                <div class="admin-user">
                    <div class="admin-user__avatar"><?= htmlspecialchars(mb_substr($userPrenom, 0, 1)) ?></div>
                    <h3><?= htmlspecialchars($userPrenom) ?></h3>
                    <span class="badge badge--employe"><?= htmlspecialchars(ucfirst($userRole)) ?></span>
                </div>
                <nav class="admin-nav" aria-label="Navigation espace équipe">
                    <ul>
                        <li>
                            <a href="dashboard.php" class="admin-nav__link is-active" data-tab="dashboard">
                                <span class="icon">📊</span> Tableau de bord
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#commandes" class="admin-nav__link" data-tab="commandes">
                                <span class="icon">📦</span> Commandes
                                <?php if ($stats['commandes_attente'] > 0): ?>
                                    <span class="badge badge--notification"><?= $stats['commandes_attente'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#avis" class="admin-nav__link" data-tab="avis">
                                <span class="icon">⭐</span> Avis
                                <?php if ($stats['avis_attente'] > 0): ?>
                                    <span class="badge badge--notification"><?= $stats['avis_attente'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#menus" class="admin-nav__link" data-tab="menus">
                                <span class="icon">🍽️</span> Menus & Plats
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#horaires" class="admin-nav__link" data-tab="horaires">
                                <span class="icon">🕐</span> Horaires
                            </a>
                        </li>
                        <?php if ($userRole === 'administrateur'): ?>
                            <li class="admin-nav__divider"></li>
                            <li>
                                <a href="../admin/dashboard.php" class="admin-nav__link admin-nav__link--admin">
                                    <span class="icon">⚙️</span> Administration
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="admin-sidebar__footer">
                    <a href="../espace.php" class="admin-nav__link admin-nav__link--back">
                        <span class="icon">👤</span> Mon espace client
                    </a>
                </div>
            </aside>

            <!-- Contenu Principal -->
            <div class="admin-content">
                <!-- Header Mobile Toggle -->
                <button class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Ouvrir le menu" aria-expanded="false">
                    <span class="icon">☰</span> Menu
                </button>

                <header class="admin-header">
                    <h1 class="admin-header__title">Espace Équipe</h1>
                    <p class="admin-header__subtitle">Gestion des opérations quotidiennes</p>
                </header>

                <!-- Messages Flash -->
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

                <!-- KPI Cards -->
                <section class="admin-kpi" aria-label="Indicateurs clés">
                    <div class="kpi-grid">
                        <article class="kpi-card kpi-card--attente">
                            <div class="kpi-card__icon">⏳</div>
                            <div class="kpi-card__content">
                                <span class="kpi-card__value"><?= $stats['commandes_attente'] ?></span>
                                <span class="kpi-card__label">En attente</span>
                            </div>
                        </article>
                        <article class="kpi-card kpi-card--preparation">
                            <div class="kpi-card__icon">🍳</div>
                            <div class="kpi-card__content">
                                <span class="kpi-card__value"><?= $stats['commandes_preparation'] ?></span>
                                <span class="kpi-card__label">En préparation</span>
                            </div>
                        </article>
                        <article class="kpi-card kpi-card--livraison">
                            <div class="kpi-card__icon">🚚</div>
                            <div class="kpi-card__content">
                                <span class="kpi-card__value"><?= $stats['commandes_livraison'] ?></span>
                                <span class="kpi-card__label">En livraison</span>
                            </div>
                        </article>
                        <article class="kpi-card kpi-card--termine">
                            <div class="kpi-card__icon">✅</div>
                            <div class="kpi-card__content">
                                <span class="kpi-card__value"><?= $stats['commandes_livrees'] ?></span>
                                <span class="kpi-card__label">Livrées / Terminées</span>
                            </div>
                        </article>
                        <article class="kpi-card kpi-card--avis">
                            <div class="kpi-card__icon">⭐</div>
                            <div class="kpi-card__content">
                                <span class="kpi-card__value"><?= $stats['avis_attente'] ?></span>
                                <span class="kpi-card__label">Avis à modérer</span>
                            </div>
                        </article>
                        <article class="kpi-card kpi-card--menus">
                            <div class="kpi-card__icon">🍽️</div>
                            <div class="kpi-card__content">
                                <span class="kpi-card__value"><?= $stats['menus_actifs'] ?></span>
                                <span class="kpi-card__label">Menus actifs</span>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- ONGLET COMMANDES -->
                <section id="commandes" class="admin-section" aria-labelledby="commandes-title">
                    <header class="admin-section__header">
                        <h2 id="commandes-title" class="admin-section__title">Gestion des Commandes</h2>
                        <div class="admin-section__filters">
                            <form method="GET" class="filters-form">
                                <select name="statut" class="filter-select filter-select--sm" onchange="this.form.submit()">
                                    <option value="">Tous les statuts</option>
                                    <option value="en attente" <?= $filtreStatut === 'en attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="accepte" <?= $filtreStatut === 'accepte' ? 'selected' : '' ?>>Acceptée</option>
                                    <option value="en preparation" <?= $filtreStatut === 'en preparation' ? 'selected' : '' ?>>En préparation</option>
                                    <option value="en cours de livraison" <?= $filtreStatut === 'en cours de livraison' ? 'selected' : '' ?>>En livraison</option>
                                    <option value="livre" <?= $filtreStatut === 'livre' ? 'selected' : '' ?>>Livrée</option>
                                    <option value="en attente du retour de materiel" <?= $filtreStatut === 'en attente du retour de materiel' ? 'selected' : '' ?>>Attente retour matériel</option>
                                    <option value="terminee" <?= $filtreStatut === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                                    <option value="annulee" <?= $filtreStatut === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                                </select>
                                <button type="submit" class="btn btn--outline btn--sm">Filtrer</button>
                                <?php if ($filtreStatut || $filtreClient): ?>
                                    <a href="dashboard.php" class="btn btn--outline btn--sm">Réinitialiser</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </header>

                    <div class="admin-table-wrapper">
                        <table class="admin-table" role="grid">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Client</th>
                                    <th>Menu</th>
                                    <th>Date prestation</th>
                                    <th>Personnes</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($commandes)): ?>
                                    <tr>
                                        <td colspan="8" class="admin-table__empty">Aucune commande trouvée</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($commandes as $cmd): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($cmd['numero_commande']) ?></strong></td>
                                            <td>
                                                <?= htmlspecialchars($cmd['client_prenom'] . ' ' . $cmd['client_nom']) ?>
                                                <br><small><?= htmlspecialchars($cmd['client_email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($cmd['menu_titre']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($cmd['date_prestation'])) ?> à <?= substr($cmd['heure_prestation'], 0, 5) ?></td>
                                            <td><?= (int)$cmd['nb_personnes'] ?></td>
                                            <td><?= number_format((float)$cmd['prix_total'], 2, ',', ' ') ?> €</td>
                                            <td>
                                                <span class="badge badge--statut badge--<?= strtolower(str_replace(' ', '-', $cmd['statut'])) ?>">
                                                    <?= htmlspecialchars(ucfirst($cmd['statut'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="admin-actions">
                                                    <a href="commande-detail.php?id=<?= (int)$cmd['commande_id'] ?>" class="btn btn--outline btn--sm" title="Voir détail">👁</a>
                                                    <button type="button" class="btn btn--primary btn--sm btn-status-change" data-commande-id="<?= (int)$cmd['commande_id'] ?>" data-current-statut="<?= htmlspecialchars($cmd['statut']) ?>" title="Changer statut">✏️</button>
                                                    <?php if (in_array($cmd['statut'], ['en attente', 'accepte'], true)): ?>
                                                        <button type="button" class="btn btn--danger btn--sm btn-cancel" data-commande-id="<?= (int)$cmd['commande_id'] ?>" data-numero="<?= htmlspecialchars($cmd['numero_commande']) ?>" title="Annuler">✕</button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ONGLET AVIS -->
                <section id="avis" class="admin-section" aria-labelledby="avis-title">
                    <header class="admin-section__header">
                        <h2 id="avis-title" class="admin-section__title">Modération des Avis</h2>
                    </header>

                    <?php if (empty($avisEnAttente)): ?>
                        <div class="admin-empty">
                            <div class="admin-empty__icon">✅</div>
                            <h3>Aucun avis en attente</h3>
                            <p>Tous les avis ont été modérés.</p>
                        </div>
                    <?php else: ?>
                        <div class="avis-moderation-list">
                            <?php foreach ($avisEnAttente as $avis): ?>
                                <article class="avis-moderation-card">
                                    <div class="avis-moderation__header">
                                        <div class="avis-moderation__stars" aria-label="Note <?= (int)$avis['note'] ?>/5">
                                            <?= str_repeat('★', (int)$avis['note']) ?><?= str_repeat('☆', 5 - (int)$avis['note']) ?>
                                        </div>
                                        <span class="badge badge--statut badge--en-attente">En attente</span>
                                    </div>
                                    <p class="avis-moderation__comment">"<?= htmlspecialchars($avis['commentaire']) ?>"</p>
                                    <div class="avis-moderation__meta">
                                        <span><strong><?= htmlspecialchars($avis['prenom']) ?></strong> — <?= date('d/m/Y', strtotime($avis['date_creation'])) ?></span>
                                        <?php if ($avis['email']): ?>
                                            <span><a href="mailto:<?= htmlspecialchars($avis['email']) ?>"><?= htmlspecialchars($avis['email']) ?></a></span>
                                        <?php endif; ?>
                                    </div>
                                    <form method="POST" action="../actions/employe/moderer-avis.php" class="avis-moderation__actions">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="avis_id" value="<?= (int)$avis['avis_id'] ?>">
                                        <button type="submit" name="action" value="valider" class="btn btn--success btn--sm">✅ Valider</button>
                                        <button type="submit" name="action" value="refuser" class="btn btn--danger btn--sm">❌ Refuser</button>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ONGLET MENUS -->
                <section id="menus" class="admin-section" aria-labelledby="menus-title">
                    <header class="admin-section__header">
                        <h2 id="menus-title" class="admin-section__title">Gestion des Menus</h2>
                        <a href="menu-form.php" class="btn btn--primary">+ Nouveau menu</a>
                    </header>

                    <div class="admin-table-wrapper">
                        <table class="admin-table" role="grid">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Thème</th>
                                    <th>Régime</th>
                                    <th>Min. pers.</th>
                                    <th>Prix / pers.</th>
                                    <th>Stock</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menus as $menu): ?>
                                    <tr>
                                        <td><img src="<?= htmlspecialchars($menu['image_url'] ?? '../public/img/presentation-photo.webp') ?>" alt="" class="admin-table__thumb"></td>
                                        <td><?= htmlspecialchars($menu['titre']) ?></td>
                                        <td><span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span></td>
                                        <td><span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span></td>
                                        <td><?= (int)$menu['nb_personne_min'] ?></td>
                                        <td><?= number_format((float)$menu['prix_base_min'], 2, ',', ' ') ?> €</td>
                                        <td><?= (int)$menu['stock_disponible'] ?></td>
                                        <td>
                                            <span class="badge <?= $menu['is_active'] ? 'badge--success' : 'badge--muted' ?>">
                                                <?= $menu['is_active'] ? 'Actif' : 'Inactif' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="menu-form.php?id=<?= (int)$menu['menu_id'] ?>" class="btn btn--outline btn--sm" title="Modifier">✏️</a>
                                            <button type="button" class="btn btn--danger btn--sm btn-toggle-menu" data-menu-id="<?= (int)$menu['menu_id'] ?>" data-active="<?= (int)$menu['is_active'] ?>" title="<?= $menu['is_active'] ? 'Désactiver' : 'Activer' ?>">
                                                <?= $menu['is_active'] ? '👁️‍🗨️' : '👁️' ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ONGLET HORAIRES -->
                <section id="horaires" class="admin-section" aria-labelledby="horaires-title">
                    <header class="admin-section__header">
                        <h2 id="horaires-title" class="admin-section__title">Horaires d'ouverture</h2>
                    </header>

                    <form method="POST" action="../actions/employe/update-horaires.php" class="horaires-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="horaires-grid">
                            <?php foreach ($horaires as $h): ?>
                                <div class="horaire-card">
                                    <h3><?= htmlspecialchars($h['jour_semaine']) ?></h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Ouverture</label>
                                            <input type="time" name="ouverture_<?= (int)$h['horaire_id'] ?>" class="form-input" value="<?= $h['heure_ouverture'] ? substr($h['heure_ouverture'], 0, 5) : '' ?>" <?= !$h['est_ouvert'] ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="form-group">
                                            <label>Fermeture</label>
                                            <input type="time" name="fermeture_<?= (int)$h['horaire_id'] ?>" class="form-input" value="<?= $h['heure_fermeture'] ? substr($h['heure_fermeture'], 0, 5) : '' ?>" <?= !$h['est_ouvert'] ? 'disabled' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="form-group form-group--checkbox">
                                        <label class="checkbox-wrapper">
                                            <input type="checkbox" name="ouvert_<?= (int)$h['horaire_id'] ?>" value="1" <?= $h['est_ouvert'] ? 'checked' : '' ?> onchange="toggleHoraireInputs(this, <?= (int)$h['horaire_id'] ?>)">
                                            <span class="checkmark"></span>
                                            Ouvert
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn--primary btn--lg mt-4">Enregistrer les horaires</button>
                    </form>
                </section>
            </div>
        </div>

        <!-- MODALE CHANGEMENT STATUT -->
        <dialog class="modal" id="modalStatusChange" role="dialog" aria-modal="true" aria-labelledby="modal-status-title">
            <form method="POST" action="../actions/employe/change-statut.php" id="formStatusChange">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="commande_id" id="status_commande_id">
                <header class="modal__header">
                    <h2 id="modal-status-title">Changer le statut</h2>
                    <button type="button" class="modal__close" aria-label="Fermer">✕</button>
                </header>
                <div class="modal__body">
                    <p>Commande <strong id="status_numero"></strong></p>
                    <div class="form-group">
                        <label for="nouveau_statut">Nouveau statut</label>
                        <select id="nouveau_statut" name="nouveau_statut" class="form-input" required>
                            <option value="en attente">En attente</option>
                            <option value="accepte">Acceptée</option>
                            <option value="en preparation">En préparation</option>
                            <option value="en cours de livraison">En cours de livraison</option>
                            <option value="livre">Livrée</option>
                            <option value="en attente du retour de materiel">Attente retour matériel</option>
                            <option value="terminee">Terminée</option>
                            <option value="annulee">Annulée</option>
                        </select>
                    </div>
                    <div class="form-group" id="annulation_fields" hidden>
                        <label for="motif_annulation">Motif d'annulation *</label>
                        <textarea name="motif_annulation" id="motif_annulation" class="form-input" rows="3" required placeholder="Motif obligatoire pour annulation"></textarea>
                    </div>
                    <div class="form-group" id="contact_fields" hidden>
                        <label for="contact_mode_annulation">Mode de contact client *</label>
                        <select name="contact_mode_annulation" id="contact_mode_annulation" class="form-input" required>
                            <option value="">-- Choisir --</option>
                            <option value="gsm">Téléphone (GSM)</option>
                            <option value="mail">Email</option>
                        </select>
                    </div>
                </div>
                <footer class="modal__footer">
                    <button type="button" class="btn btn--outline modal__close-btn">Annuler</button>
                    <button type="submit" class="btn btn--primary">Valider</button>
                </footer>
            </form>
        </dialog>

        <!-- MODALE ANNULATION -->
        <dialog class="modal" id="modalCancel" role="dialog" aria-modal="true" aria-labelledby="modal-cancel-title">
            <form method="POST" action="../actions/employe/annuler-commande.php" id="formCancel">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="commande_id" id="cancel_commande_id">
                <header class="modal__header">
                    <h2 id="modal-cancel-title">Annuler la commande</h2>
                    <button type="button" class="modal__close" aria-label="Fermer">✕</button>
                </header>
                <div class="modal__body">
                    <p>Confirmer l'annulation de <strong id="cancel_numero"></strong> ?</p>
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
                </div>
                <footer class="modal__footer">
                    <button type="button" class="btn btn--outline modal__close-btn">Non</button>
                    <button type="submit" class="btn btn--danger">Oui, annuler</button>
                </footer>
            </form>
        </dialog>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts Admin/Employé -->
    <script>
        (function() {
            'use strict';

            // Toggle Sidebar Mobile
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.getElementById('adminMobileToggle');
            if (sidebar && toggle) {
                toggle.addEventListener('click', () => {
                    const open = sidebar.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', open);
                });
                // Fermer au clic sur lien
                sidebar.querySelectorAll('.admin-nav__link').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 860) {
                            sidebar.classList.remove('is-open');
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                });
            }

            // Toggle horaire inputs
            window.toggleHoraireInputs = function(checkbox, id) {
                const ouverture = document.querySelector(`input[name="ouverture_${id}"]`);
                const fermeture = document.querySelector(`input[name="fermeture_${id}"]`);
                if (ouverture && fermeture) {
                    ouverture.disabled = !checkbox.checked;
                    fermeture.disabled = !checkbox.checked;
                }
            };

            // Modale Changement Statut
            const modalStatus = document.getElementById('modalStatusChange');
            const statusBtns = document.querySelectorAll('.btn-status-change');
            const nouveauStatutSelect = document.getElementById('nouveau_statut');
            const annulationFields = document.getElementById('annulation_fields');
            const contactFields = document.getElementById('contact_fields');

            statusBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('status_commande_id').value = btn.dataset.commandeId;
                    document.getElementById('status_numero').textContent = btn.dataset.currentStatut;
                    nouveauStatutSelect.value = btn.dataset.currentStatut;
                    toggleStatusFields(btn.dataset.currentStatut);
                    modalStatus?.showModal();
                });
            });

            nouveauStatutSelect?.addEventListener('change', () => {
                toggleStatusFields(nouveauStatutSelect.value);
            });

            function toggleStatusFields(statut) {
                const isAnnulation = statut === 'annulee';
                if (annulationFields) annulationFields.hidden = !isAnnulation;
                if (contactFields) contactFields.hidden = !isAnnulation;
                const motifInput = document.getElementById('motif_annulation');
                const contactInput = document.getElementById('contact_mode_annulation');
                if (motifInput) motifInput.required = isAnnulation;
                if (contactInput) contactInput.required = isAnnulation;
            }

            // Modale Annulation
            const modalCancel = document.getElementById('modalCancel');
            const cancelBtns = document.querySelectorAll('.btn-cancel');

            cancelBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('cancel_commande_id').value = btn.dataset.commandeId;
                    document.getElementById('cancel_numero').textContent = btn.dataset.numero;
                    modalCancel?.showModal();
                });
            });

            // Fermeture modales
            document.querySelectorAll('.modal__close, .modal__close-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = btn.closest('dialog');
                    if (modal) modal.close();
                });
            });

            document.querySelectorAll('dialog').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.close();
                });
            });

            // Toggle menu actif/inactif
            document.querySelectorAll('.btn-toggle-menu').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const menuId = btn.dataset.menuId;
                    const active = btn.dataset.active === '1' ? 0 : 1;
                    try {
                        const resp = await fetch('../actions/employe/toggle-menu.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `csrf_token=${encodeURIComponent('<?= $csrfToken ?>')}&menu_id=${menuId}&is_active=${active}`
                        });
                        const data = await resp.json();
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    } catch (e) {
                        alert('Erreur de communication');
                    }
                });
            });

        })();
    </script>
</body>

</html>