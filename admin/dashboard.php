<?php
/**
 * Dashboard Administrateur - Vite & Gourmand
 * ECF : Toutes fonctionnalités employé + gestion employés + stats MongoDB (graphiques, CA)
 */

require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/CommandeRepository.php';
require_once __DIR__ . '/../repositories/AvisRepository.php';
require_once __DIR__ . '/../repositories/MenuRepository.php';
require_once __DIR__ . '/../repositories/HoraireRepository.php';
require_once __DIR__ . '/../repositories/UtilisateurRepository.php';
require_once __DIR__ . '/../repositories/StatistiqueRepository.php';

// Protection : admin seulement
requireAdmin();

$userId = getCurrentUserId();
$userPrenom = getCurrentUserPrenom();

$commandeRepo = new CommandeRepository();
$avisRepo = new AvisRepository();
$menuRepo = new MenuRepository();
$horaireRepo = new HoraireRepository();
$userRepo = new UtilisateurRepository();
$statRepo = new StatistiqueRepository();

// Stats globales
$stats = [
    'commandes_attente' => count($commandeRepo->findAllFiltered('en attente')),
    'commandes_preparation' => count($commandeRepo->findAllFiltered('en preparation')),
    'commandes_livraison' => count($commandeRepo->findAllFiltered('en cours de livraison')),
    'commandes_livrees' => count($commandeRepo->findAllFiltered('livre')),
    'avis_attente' => count($avisRepo->findAll('EN_ATTENTE')),
    'menus_actifs' => count($menuRepo->getPdo()->query("SELECT * FROM menu WHERE is_active = 1")->fetchAll()),
    'employes_actifs' => count($userRepo->getPdo()->query("SELECT * FROM utilisateur WHERE role = 'employe' AND is_active = 1")->fetchAll()),
];

// Filtres commandes
$filtreStatut = $_GET['statut'] ?? '';
$filtreClient = $_GET['client_id'] ?? '';
$commandes = $commandeRepo->findAllFiltered($filtreStatut ?: null, $filtreClient ?: null);

// Avis en attente
$avisEnAttente = $avisRepo->findAll('EN_ATTENTE');

// Menus
$menus = $menuRepo->findAllFiltered(['is_active' => 1]);

// Horaires
$horaires = $horaireRepo->findAll();

// Employés
$employes = $userRepo->findAllEmployes();

// Stats MongoDB pour graphiques
$cmdParMenu = $statRepo->getNombreCommandesParMenu();
$caParMenu = $statRepo->getChiffreAffairesDetails();

// Filtres CA
$caMenuId = isset($_GET['ca_menu']) ? (int)$_GET['ca_menu'] : null;
$caDateDebut = $_GET['ca_date_debut'] ?? '';
$caDateFin = $_GET['ca_date_fin'] ?? '';
if ($caMenuId || $caDateDebut || $caDateFin) {
    $caParMenu = $statRepo->getChiffreAffairesDetails($caMenuId ?: null, $caDateDebut ?: null, $caDateFin ?: null);
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace Administrateur - Vite & Gourmand">
    <title>Administration | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js pour graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>

    <link rel="stylesheet" href="../public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'admin';
    require_once __DIR__ . '/../includes/navbar.php';
    ?>

    <main class="main-admin">
        <div class="admin-container">
            <!-- Sidebar Navigation -->
            <aside class="admin-sidebar" id="adminSidebar">
                <div class="admin-user">
                    <div class="admin-user__avatar"><?= htmlspecialchars(mb_substr($userPrenom, 0, 1)) ?></div>
                    <h3><?= htmlspecialchars($userPrenom) ?></h3>
                    <span class="badge badge--admin">Administrateur</span>
                </div>
                <nav class="admin-nav" aria-label="Navigation administration">
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
                        <li class="admin-nav__divider"></li>
                        <li>
                            <a href="dashboard.php#employes" class="admin-nav__link" data-tab="employes">
                                <span class="icon">👥</span> Gestion Équipe
                                <span class="badge badge--notification"><?= $stats['employes_actifs'] ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#statistiques" class="admin-nav__link" data-tab="statistiques">
                                <span class="icon">📈</span> Statistiques NoSQL
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="admin-sidebar__footer">
                    <a href="../employe/dashboard.php" class="admin-nav__link">
                        <span class="icon">👨‍🍳</span> Vue Employé
                    </a>
                </div>
            </aside>

            <!-- Contenu Principal -->
            <div class="admin-content">
                <button class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Ouvrir le menu" aria-expanded="false">
                    <span class="icon">☰</span> Menu
                </button>

                <header class="admin-header">
                    <h1 class="admin-header__title">Administration</h1>
                    <p class="admin-header__subtitle">Gestion complète de l'activité</p>
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
                        <article class="kpi-card kpi-card--employes">
                            <div class="kpi-card__icon">👥</div>
                            <div class="kpi-card__content">
                                <span class="kpi-card__value"><?= $stats['employes_actifs'] ?></span>
                                <span class="kpi-card__label">Employés actifs</span>
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
                                    <th>Matériel</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($commandes)): ?>
                                    <tr>
                                        <td colspan="9" class="admin-table__empty">Aucune commande trouvée</td>
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
                                                <?php if ($cmd['pret_materiel']): ?>
                                                    <span class="badge badge--warning">📦 Prêt</span>
                                                    <?php if ($cmd['restitution_materiel']): ?>
                                                        <span class="badge badge--success">✅ Restitué</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="admin-actions">
                                                    <a href="../employe/commande-detail.php?id=<?= (int)$cmd['commande_id'] ?>" class="btn btn--outline btn--sm" title="Voir détail">👁</a>
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
                        <a href="modifier_menu.php" class="btn btn--primary">+ Nouveau menu</a>
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
                                            <a href="modifier_menu.php?id=<?= (int)$menu['menu_id'] ?>" class="btn btn--outline btn--sm" title="Modifier">✏️</a>
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
                                    <div class="horaire-card__times">
                                        <div class="form-group">
                                            <label for="ouverture_<?= (int)$h['horaire_id'] ?>">Ouverture</label>
                                            <input type="time" id="ouverture_<?= (int)$h['horaire_id'] ?>" name="ouverture_<?= (int)$h['horaire_id'] ?>" class="form-input" value="<?= $h['heure_ouverture'] ? substr($h['heure_ouverture'], 0, 5) : '' ?>" <?= !$h['est_ouvert'] ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="form-group">
                                            <label for="fermeture_<?= (int)$h['horaire_id'] ?>">Fermeture</label>
                                            <input type="time" id="fermeture_<?= (int)$h['horaire_id'] ?>" name="fermeture_<?= (int)$h['horaire_id'] ?>" class="form-input" value="<?= $h['heure_fermeture'] ? substr($h['heure_fermeture'], 0, 5) : '' ?>" <?= !$h['est_ouvert'] ? 'disabled' : '' ?>>
                                        </div>
                                    </div>
                                    <div class="horaire-card__status">
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

                <!-- ONGLET EMPLOYÉS (Admin seulement) -->
                <section id="employes" class="admin-section" aria-labelledby="employes-title">
                    <header class="admin-section__header">
                        <h2 id="employes-title" class="admin-section__title">Gestion de l'Équipe</h2>
                        <button type="button" class="btn btn--primary" id="btnAddEmploye">+ Ajouter un employé</button>
                    </header>

                    <div class="admin-table-wrapper">
                        <table class="admin-table" role="grid">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employes as $emp): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($emp['prenom'] . ' ' . $emp['nom']) ?></td>
                                        <td><?= htmlspecialchars($emp['email']) ?></td>
                                        <td><?= htmlspecialchars($emp['gsm']) ?></td>
                                        <td><span class="badge badge--employe">Employé</span></td>
                                        <td>
                                            <span class="badge <?= $emp['is_active'] ? 'badge--success' : 'badge--danger' ?>">
                                                <?= $emp['is_active'] ? 'Actif' : 'Bloqué' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($emp['date_creation'])) ?></td>
                                        <td>
                                            <div class="admin-actions">
                                                <button type="button" class="btn btn--danger btn--sm btn-toggle-employe" data-user-id="<?= (int)$emp['utilisateur_id'] ?>" data-active="<?= (int)$emp['is_active'] ?>" title="<?= $emp['is_active'] ? 'Bloquer' : 'Activer' ?>">
                                                    <?= $emp['is_active'] ? '🚫' : '✅' ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ONGLET STATISTIQUES (MongoDB) -->
                <section id="statistiques" class="admin-section" aria-labelledby="stats-title">
                    <header class="admin-section__header">
                        <h2 id="stats-title" class="admin-section__title">Statistiques & Chiffre d'Affaires (MongoDB)</h2>
                    </header>

                    <!-- Filtres CA -->
                    <form method="GET" class="stats-filters" id="caFiltersForm">
                        <input type="hidden" name="ca_menu" id="ca_menu" value="<?= $caMenuId ?? '' ?>">
                        <div class="filters-row">
                            <div class="form-group">
                                <label for="ca_menu_select">Menu</label>
                                <select id="ca_menu_select" name="ca_menu" class="filter-select filter-select--sm" onchange="this.form.submit()">
                                    <option value="">Tous les menus</option>
                                    <?php foreach ($menus as $menu): ?>
                                        <option value="<?= (int)$menu['menu_id'] ?>" <?= ($caMenuId ?? '') == $menu['menu_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($menu['titre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ca_date_debut">Date début</label>
                                <input type="date" id="ca_date_debut" name="ca_date_debut" class="filter-input filter-input--sm" value="<?= htmlspecialchars($caDateDebut) ?>">
                            </div>
                            <div class="form-group">
                                <label for="ca_date_fin">Date fin</label>
                                <input type="date" id="ca_date_fin" name="ca_date_fin" class="filter-input filter-input--sm" value="<?= htmlspecialchars($caDateFin) ?>">
                            </div>
                            <div class="form-group" style="align-self: flex-end;">
                                <button type="submit" class="btn btn--primary btn--sm">Filtrer</button>
                                <?php if ($caMenuId || $caDateDebut || $caDateFin): ?>
                                    <a href="dashboard.php#statistiques" class="btn btn--outline btn--sm">Reset</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Graphiques -->
                    <div class="stats-charts-grid">
                        <!-- Graphique 1 : Commandes par menu -->
                        <article class="stats-chart-card">
                            <header class="stats-chart__header">
                                <h3>Nombre de commandes par menu</h3>
                            </header>
                            <div class="stats-chart__canvas">
                                <canvas id="chartCommandesParMenu" aria-label="Graphique en barres : nombre de commandes par menu"></canvas>
                            </div>
                            <?php if (empty($cmdParMenu)): ?>
                                <p class="stats-chart__empty">Aucune donnée</p>
                            <?php endif; ?>
                        </article>

                        <!-- Graphique 2 : CA par menu -->
                        <article class="stats-chart-card">
                            <header class="stats-chart__header">
                                <h3>Chiffre d'affaires par menu</h3>
                                <?php
                                $totalCA = array_sum(array_column($caParMenu, 'chiffre_affaires'));
                                ?>
                                <div class="stats-chart__total">Total : <?= number_format($totalCA, 2, ',', ' ') ?> €</div>
                            </header>
                            <div class="stats-chart__canvas">
                                <canvas id="chartCAParMenu" aria-label="Graphique en barres : chiffre d'affaires par menu"></canvas>
                            </div>
                            <?php if (empty($caParMenu)): ?>
                                <p class="stats-chart__empty">Aucune donnée</p>
                            <?php endif; ?>
                        </article>
                    </div>

                    <!-- Tableau détaillé CA -->
                    <div class="admin-table-wrapper mt-4">
                        <table class="admin-table" role="grid">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Thème</th>
                                    <th>Régime</th>
                                    <th>Nb commandes</th>
                                    <th>Total personnes</th>
                                    <th>Chiffre d'affaires</th>
                                    <th>Frais livraison</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($caParMenu)): ?>
                                    <tr><td colspan="7" class="admin-table__empty">Aucune donnée pour les filtres sélectionnés</td></tr>
                                <?php else: ?>
                                    <?php foreach ($caParMenu as $row): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($row['_id']) ?></strong></td>
                                            <td><?= htmlspecialchars($row['theme'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($row['regime'] ?? '—') ?></td>
                                            <td><?= (int)$row['nombre_commandes'] ?></td>
                                            <td><?= (int)$row['total_personnes'] ?></td>
                                            <td><strong><?= number_format((float)$row['chiffre_affaires'], 2, ',', ' ') ?> €</strong></td>
                                            <td><?= number_format((float)($row['total_frais_livraison'] ?? 0), 2, ',', ' ') ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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

        <!-- MODALE AJOUT EMPLOYÉ -->
        <dialog class="modal" id="modalAddEmploye" role="dialog" aria-modal="true" aria-labelledby="modal-add-employe-title">
            <form method="POST" action="../actions/admin/add-employe.php" id="formAddEmploye">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <header class="modal__header">
                    <h2 id="modal-add-employe-title">Créer un compte employé</h2>
                    <button type="button" class="modal__close" aria-label="Fermer">✕</button>
                </header>
                <div class="modal__body">
                    <p class="text-warning">Le mot de passe sera communiqué en main propre à l'employé (pas par email).</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="emp_nom">Nom *</label>
                            <input type="text" id="emp_nom" name="nom" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="emp_prenom">Prénom *</label>
                            <input type="text" id="emp_prenom" name="prenom" class="form-input" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="emp_email">Email *</label>
                        <input type="email" id="emp_email" name="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="emp_password">Mot de passe *</label>
                        <input type="password" id="emp_password" name="password" class="form-input" required autocomplete="new-password">
                        <span class="form-hint">Min 10 car, maj, min, chiffre, spécial. Sera affiché une seule fois.</span>
                    </div>
                    <div class="form-group">
                        <label for="emp_gsm">Téléphone *</label>
                        <input type="tel" id="emp_gsm" name="gsm" class="form-input" pattern="0[1-9](\s?\d{2}){4}" placeholder="06 XX XX XX XX" required>
                    </div>
                </div>
                <footer class="modal__footer">
                    <button type="button" class="btn btn--outline modal__close-btn">Annuler</button>
                    <button type="submit" class="btn btn--primary">Créer le compte</button>
                </footer>
            </form>
        </dialog>

        <!-- MODALE MOT DE PASSE GÉNÉRÉ -->
        <dialog class="modal" id="modalGeneratedPwd" role="dialog" aria-modal="true" aria-labelledby="modal-pwd-title">
            <header class="modal__header">
                <h2 id="modal-pwd-title">Compte créé - Mot de passe généré</h2>
                <button type="button" class="modal__close" aria-label="Fermer">✕</button>
            </header>
            <div class="modal__body">
                <p>Communiquez ce mot de passe <strong>uniquement en main propre</strong> à l'employé :</p>
                <div class="generated-password" id="generatedPasswordDisplay">
                    <code id="generatedPassword">—</code>
                    <button type="button" class="btn btn--outline btn--sm" id="btnCopyPwd" aria-label="Copier le mot de passe">📋 Copier</button>
                </div>
                <p class="text-warning"><strong>Important :</strong> Ce mot de passe ne sera plus affiché. Notez-le maintenant.</p>
            </div>
            <footer class="modal__footer">
                <button type="button" class="btn btn--primary modal__close-btn" id="btnPwdOk">J'ai noté le mot de passe</button>
            </footer>
        </dialog>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts Admin -->
    <script>
        (function() {
            'use strict';

            // Données pour graphiques (injectées depuis PHP)
            const chartDataCommandes = <?= json_encode($cmdParMenu, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            const chartDataCA = <?= json_encode($caParMenu, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

            // --- Initialisation Graphiques Chart.js ---
            function initCharts() {
                if (typeof Chart === 'undefined') {
                    console.warn('Chart.js non chargé');
                    return;
                }

                // Couleurs thème
                const colors = {
                    primary: '#D2691E',
                    primaryLight: 'rgba(210, 105, 30, 0.2)',
                    secondary: '#004225',
                    secondaryLight: 'rgba(0, 66, 37, 0.15)',
                    tertiary: '#f59e0b',
                    grid: 'rgba(0,0,0,0.05)',
                    text: '#1C1B1B'
                };

                // Graphique 1 : Commandes par menu
                const ctx1 = document.getElementById('chartCommandesParMenu');
                if (ctx1 && chartDataCommandes && chartDataCommandes.length > 0) {
                    const labels = chartDataCommandes.map(d => d._id);
                    const data = chartDataCommandes.map(d => d.total_commandes);

                    new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Nombre de commandes',
                                data: data,
                                backgroundColor: colors.primaryLight,
                                borderColor: colors.primary,
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            }]
                        },
                        options: getChartOptions('Nombre de commandes')
                    });
                }

                // Graphique 2 : CA par menu
                const ctx2 = document.getElementById('chartCAParMenu');
                if (ctx2 && chartDataCA && chartDataCA.length > 0) {
                    const labels = chartDataCA.map(d => d._id);
                    const data = chartDataCA.map(d => d.chiffre_affaires);

                    new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Chiffre d\'affaires (€)',
                                data: data,
                                backgroundColor: colors.secondaryLight,
                                borderColor: colors.secondary,
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            }]
                        },
                        options: getChartOptions('Chiffre d\'affaires (€)')
                    });
                }
            }

            function getChartOptions(yLabel) {
                return {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1C1B1B',
                            titleFont: { family: 'Plus Jakarta Sans', weight: '600' },
                            bodyFont: { family: 'Plus Jakarta Sans' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString('fr-FR') + (yLabel.includes('€') ? ' €' : '');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: colors.grid },
                            ticks: {
                                font: { family: 'Plus Jakarta Sans', size: 11 },
                                callback: function(value) {
                                    return value.toLocaleString('fr-FR') + (yLabel.includes('€') ? ' €' : '');
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, maxRotation: 45, minRotation: 0 }
                        }
                    }
                };
            }

            // --- Toggle Sidebar Mobile ---
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.getElementById('adminMobileToggle');
            if (sidebar && toggle) {
                toggle.addEventListener('click', () => {
                    const open = sidebar.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', open);
                });
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

            // Toggle employé actif/inactif (Admin)
            document.querySelectorAll('.btn-toggle-employe').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const userId = btn.dataset.userId;
                    const active = btn.dataset.active === '1' ? 0 : 1;
                    if (!confirm(active ? 'Activer cet employé ?' : 'Bloquer cet employé ?')) return;
                    try {
                        const resp = await fetch('../actions/admin/toggle-employe.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `csrf_token=${encodeURIComponent('<?= $csrfToken ?>')}&user_id=${userId}&is_active=${active}`
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

            // Modale Ajout Employé
            const modalAddEmploye = document.getElementById('modalAddEmploye');
            const btnAddEmploye = document.getElementById('btnAddEmploye');
            const modalGeneratedPwd = document.getElementById('modalGeneratedPwd');
            const generatedPasswordEl = document.getElementById('generatedPassword');
            const btnCopyPwd = document.getElementById('btnCopyPwd');
            const btnPwdOk = document.getElementById('btnPwdOk');

            btnAddEmploye?.addEventListener('click', () => modalAddEmploye?.showModal());

            // Gestion réponse AJAX création employé
            const formAddEmploye = document.getElementById('formAddEmploye');
            formAddEmploye?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(formAddEmploye);
                try {
                    const resp = await fetch('../actions/admin/add-employe.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await resp.json();
                    if (data.success) {
                        modalAddEmploye?.close();
                        generatedPasswordEl.textContent = data.password;
                        modalGeneratedPwd?.showModal();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                } catch (e) {
                    alert('Erreur de communication');
                }
            });

            btnCopyPwd?.addEventListener('click', () => {
                navigator.clipboard.writeText(generatedPasswordEl.textContent);
                btnCopyPwd.textContent = '✅ Copié !';
                setTimeout(() => btnCopyPwd.textContent = '📋 Copier', 2000);
            });

            btnPwdOk?.addEventListener('click', () => {
                modalGeneratedPwd?.close();
                location.reload();
            });

            // Initialisation graphiques au chargement
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts);
            } else {
                initCharts();
            }

        })();
    </script>
</body>

</html>