<?php
/**
 * Gestion des Menus - Administration
 * ECF : Liste, création, modification, suppression des menus
 */

require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/MenuRepository.php';
require_once __DIR__ . '/../repositories/PlatRepository.php';

// Protection : admin seulement
requireAdmin();

$menuRepo = new MenuRepository();

// Récupération de tous les menus (actifs et inactifs)
$menus = $menuRepo->getPdo()->query("
    SELECT m.*, t.libelle AS theme_libelle, r.libelle AS regime_libelle
    FROM menu m
    JOIN theme t ON m.theme_id = t.theme_id
    JOIN regime r ON m.regime_id = r.regime_id
    ORDER BY m.date_creation DESC
")->fetchAll();

// Récupération des thèmes et régimes pour le modal de création rapide
$themes = $menuRepo->getAllThemes();
$regimes = $menuRepo->getAllRegimes();

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestion des menus - Administration Vite & Gourmand">
    <title>Gestion des Menus | Administration</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                    <div class="admin-user__avatar"><?= htmlspecialchars(mb_substr($_SESSION['prenom'] ?? 'A', 0, 1)) ?></div>
                    <h3><?= htmlspecialchars($_SESSION['prenom'] ?? 'Admin') ?></h3>
                    <span class="badge badge--admin">Administrateur</span>
                </div>
                <nav class="admin-nav" aria-label="Navigation administration">
                    <ul>
                        <li>
                            <a href="dashboard.php" class="admin-nav__link">
                                <span class="icon">📊</span> Tableau de bord
                            </a>
                        </li>
                        <li>
                            <a href="gestion_menus.php" class="admin-nav__link is-active">
                                <span class="icon">🍽️</span> Gestion des Menus
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#commandes" class="admin-nav__link">
                                <span class="icon">📦</span> Commandes
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#avis" class="admin-nav__link">
                                <span class="icon">⭐</span> Avis
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#horaires" class="admin-nav__link">
                                <span class="icon">🕐</span> Horaires
                            </a>
                        </li>
                        <li class="admin-nav__divider"></li>
                        <li>
                            <a href="dashboard.php#employes" class="admin-nav__link">
                                <span class="icon">👥</span> Équipe
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php#statistiques" class="admin-nav__link">
                                <span class="icon">📈</span> Statistiques
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Contenu Principal -->
            <div class="admin-content">
                <button class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Ouvrir le menu" aria-expanded="false">
                    <span class="icon">☰</span> Menu
                </button>

                <header class="admin-header">
                    <div class="admin-header__row">
                        <div>
                            <h1 class="admin-header__title">Gestion des Menus</h1>
                            <p class="admin-header__subtitle">Créer, modifier, supprimer les menus du catalogue</p>
                        </div>
                        <a href="modifier_menu.php" class="btn btn--primary">
                            <span class="icon">+</span> Nouveau menu
                        </a>
                    </div>
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

                <!-- Tableau des Menus -->
                <section class="admin-section">
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
                                <?php if (empty($menus)): ?>
                                    <tr>
                                        <td colspan="9" class="admin-table__empty">
                                            <div class="admin-empty">
                                                <div class="admin-empty__icon">🍽️</div>
                                                <h3>Aucun menu</h3>
                                                <p>Créez votre premier menu pour commencer.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($menus as $menu): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= htmlspecialchars($menu['image_url'] ?? '../public/img/presentation-photo.webp') ?>"
                                                     alt="" class="admin-table__thumb" loading="lazy">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($menu['titre']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($menu['description'], 0, 60)) ?>...</small>
                                            </td>
                                            <td><span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span></td>
                                            <td><span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span></td>
                                            <td><?= (int)$menu['nb_personne_min'] ?></td>
                                            <td><?= number_format((float)$menu['prix_base_min'], 2, ',', ' ') ?> €</td>
                                            <td>
                                                <span class="<?= (int)$menu['stock_disponible'] <= 3 && (int)$menu['stock_disponible'] > 0 ? 'text-warning' : '' ?>">
                                                    <?= (int)$menu['stock_disponible'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $menu['is_active'] ? 'badge--success' : 'badge--muted' ?>">
                                                    <?= $menu['is_active'] ? 'Actif' : 'Inactif' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="admin-actions">
                                                    <a href="modifier_menu.php?id=<?= (int)$menu['menu_id'] ?>" class="btn btn--outline btn--sm" title="Modifier">
                                                        ✏️ Modifier
                                                    </a>
                                                    <button type="button" class="btn btn--danger btn--sm btn-delete-menu"
                                                            data-menu-id="<?= (int)$menu['menu_id'] ?>"
                                                            data-menu-titre="<?= htmlspecialchars($menu['titre'], ENT_QUOTES) ?>"
                                                            title="Supprimer">
                                                        🗑️ Supprimer
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <!-- MODALE CONFIRMATION SUPPRESSION -->
        <dialog class="modal" id="modalDeleteMenu" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title">
            <form method="POST" action="../actions/admin/supprimer_menu.php" id="formDeleteMenu">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="menu_id" id="delete_menu_id">
                <header class="modal__header">
                    <h2 id="modal-delete-title">Supprimer le menu</h2>
                    <button type="button" class="modal__close" aria-label="Fermer">✕</button>
                </header>
                <div class="modal__body">
                    <p>Êtes-vous sûr de vouloir supprimer le menu <strong id="delete_menu_titre"></strong> ?</p>
                    <p class="text-warning"><strong>Attention :</strong> Cette action est irréversible. Les plats, allergènes et images associés seront également supprimés.</p>
                </div>
                <footer class="modal__footer">
                    <button type="button" class="btn btn--outline modal__close-btn">Annuler</button>
                    <button type="submit" class="btn btn--danger">Confirmer la suppression</button>
                </footer>
            </form>
        </dialog>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <!-- Scripts -->
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
                sidebar.querySelectorAll('.admin-nav__link').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 860) {
                            sidebar.classList.remove('is-open');
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                });
            }

            // Modale Suppression
            const modalDelete = document.getElementById('modalDeleteMenu');
            const deleteBtns = document.querySelectorAll('.btn-delete-menu');

            deleteBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('delete_menu_id').value = btn.dataset.menuId;
                    document.getElementById('delete_menu_titre').textContent = btn.dataset.menuTitre;
                    modalDelete?.showModal();
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
        })();
    </script>
</body>

</html>