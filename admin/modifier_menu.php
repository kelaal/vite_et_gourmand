<?php
/**
 * Formulaire Création/Modification Menu - Administration
 * ECF : Formulaire complet pré-rempli en mode édition
 */

require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/MenuRepository.php';
require_once __DIR__ . '/../repositories/PlatRepository.php';
require_once __DIR__ . '/../repositories/AllergeneRepository.php';

requireAdmin();

$menuRepo = new MenuRepository();
$platRepo = new PlatRepository();
require_once __DIR__ . '/../repositories/AllergeneRepository.php';
$allergeneRepo = new AllergeneRepository();

$menuId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = $menuId !== null;

$menu = null;
$platsMenu = [];
$allergenesParPlat = [];
$imagesMenu = [];

if ($isEdit) {
    $menu = $menuRepo->findDetails($menuId);
    if (!$menu) {
        header('Location: gestion_menus.php?error=notfound');
        exit;
    }
    $platsMenu = $platRepo->findByMenu($menuId);
    foreach ($platsMenu as &$p) {
        $p['allergenes'] = $platRepo->getAllergenes($p['plat_id']);
    }
    $imagesMenu = $menu['images'] ?? [];
}

// Récupération données pour formulaires
$themes = $menuRepo->getAllThemes();
$regimes = $menuRepo->getAllRegimes();
$allPlats = $platRepo->findAll();
$allergenes = $allergeneRepo->findAll();

// Organisation des plats par type
$platsParType = ['entree' => [], 'plat' => [], 'dessert' => []];
foreach ($allPlats as $p) {
    $platsParType[$p['type_plat']][] = $p;
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $isEdit ? 'Modifier' : 'Créer' ?> un menu - Administration Vite & Gourmand">
    <title><?= $isEdit ? 'Modifier' : 'Créer' ?> un menu | Administration</title>

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
                            <h1 class="admin-header__title"><?= $isEdit ? 'Modifier le menu' : 'Créer un nouveau menu' ?></h1>
                            <p class="admin-header__subtitle"><?= $isEdit ? 'Édition de ' . htmlspecialchars($menu['titre']) : 'Remplissez tous les champs obligatoires' ?></p>
                        </div>
                        <a href="gestion_menus.php" class="btn btn--outline">
                            ← Retour à la liste
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
                if (!empty($errors)): ?>
                    <div class="alert alert--error" role="alert">
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Formulaire Menu -->
                <form action="../actions/admin/<?= $isEdit ? 'modifier_menu.php' : 'creer_menu.php' ?>" method="POST" enctype="multipart/form-data" class="menu-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="menu_id" value="<?= (int)$menu['menu_id'] ?>">
                    <?php endif; ?>

                    <!-- SECTION 1 : Informations générales -->
                    <section class="menu-form-section">
                        <h2 class="menu-form-section__title">Informations générales</h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="titre">Titre du menu <span class="required">*</span></label>
                                <input type="text" id="titre" name="titre" class="form-input" maxlength="150"
                                    value="<?= htmlspecialchars($menu['titre'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" class="form-input" rows="4" required><?= htmlspecialchars($menu['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="theme_id">Thème <span class="required">*</span></label>
                                <select id="theme_id" name="theme_id" class="form-input" required>
                                    <option value="">-- Choisir un thème --</option>
                                    <?php foreach ($themes as $t): ?>
                                        <option value="<?= (int)$t['theme_id'] ?>" <?= ($menu['theme_id'] ?? '') == $t['theme_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['libelle']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="regime_id">Régime alimentaire <span class="required">*</span></label>
                                <select id="regime_id" name="regime_id" class="form-input" required>
                                    <option value="">-- Choisir un régime --</option>
                                    <?php foreach ($regimes as $r): ?>
                                        <option value="<?= (int)$r['regime_id'] ?>" <?= ($menu['regime_id'] ?? '') == $r['regime_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($r['libelle']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nb_personne_min">Nombre de personnes minimum <span class="required">*</span></label>
                                <input type="number" id="nb_personne_min" name="nb_personne_min" class="form-input" min="1" max="100"
                                    value="<?= htmlspecialchars($menu['nb_personne_min'] ?? '4') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="prix_base_min">Prix de base / personne (€) <span class="required">*</span></label>
                                <input type="number" id="prix_base_min" name="prix_base_min" class="form-input" step="0.01" min="0"
                                    value="<?= htmlspecialchars($menu['prix_base_min'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="stock_disponible">Stock disponible <span class="required">*</span></label>
                                <input type="number" id="stock_disponible" name="stock_disponible" class="form-input" min="0" max="999"
                                    value="<?= htmlspecialchars($menu['stock_disponible'] ?? '10') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="is_active">Statut</label>
                                <div class="form-group--checkbox" style="align-self: flex-end;">
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" name="is_active" id="is_active" value="1" <?= !empty($menu['is_active']) || !$isEdit ? 'checked' : '' ?>>
                                        <span class="checkmark"></span>
                                        Menu actif (visible sur le site)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2 : Conditions délai & stockage -->
                    <section class="menu-form-section">
                        <h2 class="menu-form-section__title">Conditions de commande & Stockage</h2>
                        <div class="form-group">
                            <label for="conditions_delai_stockage">Conditions (délai commande, conservation, réchauffage...) <span class="required">*</span></label>
                            <textarea id="conditions_delai_stockage" name="conditions_delai_stockage" class="form-input" rows="4" required><?= htmlspecialchars($menu['conditions_delai_stockage'] ?? '') ?></textarea>
                            <span class="form-hint">Ex: "Commande minimum 48h à l'avance. Conserver entre 2°C et 4°C jusqu'au service. Instructions de réchauffage fournies."</span>
                        </div>
                    </section>

                    <!-- SECTION 3 : Image principale -->
                    <section class="menu-form-section">
                        <h2 class="menu-form-section__title">Image principale du menu</h2>
                        <div class="form-group">
                            <label for="image_principale">Image (WebP/JPEG/PNG, max 2MB)</label>
                            <input type="file" id="image_principale" name="image_principale" class="form-input" accept="image/webp,image/jpeg,image/png">
                            <?php if ($isEdit && !empty($menu['image_url'])): ?>
                                <div class="current-image">
                                    <img src="<?= htmlspecialchars($menu['image_url']) ?>" alt="Image actuelle" style="max-height: 100px; border-radius: 8px;">
                                    <span class="form-hint">Laissez vide pour conserver l'image actuelle</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- SECTION 4 : Composition du menu (Plats) -->
                    <section class="menu-form-section">
                        <h2 class="menu-form-section__title">Composition du menu <span class="required">*</span></h2>
                        <p class="form-hint">Sélectionnez les plats pour chaque catégorie. Au moins 1 entrée, 1 plat, 1 dessert requis.</p>

                        <?php foreach (['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'] as $type => $label): ?>
                            <div class="plats-selection">
                                <h3 class="plats-selection__title"><?= $label ?> (<?= $type === 'entree' ? '🥗' : ($type === 'plat' ? '🍽️' : '🍰') ?>)</h3>
                                <div class="plats-checkbox-grid">
                                    <?php foreach ($platsParType[$type] as $plat): ?>
                                        <?php
                                        $isSelected = false;
                                        foreach ($platsMenu as $pm) {
                                            if ($pm['plat_id'] === $plat['plat_id']) { $isSelected = true; break; }
                                        }
                                        ?>
                                        <label class="plat-checkbox">
                                            <input type="checkbox" name="plats[]" value="<?= (int)$plat['plat_id'] ?>" <?= $isSelected ? 'checked' : '' ?>>
                                            <span class="plat-checkbox__content">
                                                <strong><?= htmlspecialchars($plat['titre']) ?></strong>
                                                <small><?= htmlspecialchars($plat['description'] ?? '') ?></small>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>

                    <!-- SECTION 5 : Galerie d'images -->
                    <section class="menu-form-section">
                        <h2 class="menu-form-section__title">Galerie d'images (optionnel)</h2>
                        <div class="form-group">
                            <label for="images_galerie">Images supplémentaires (WebP/JPEG/PNG, max 2MB chacune)</label>
                            <input type="file" id="images_galerie" name="images_galerie[]" class="form-input" accept="image/webp,image/jpeg,image/png" multiple>
                            <span class="form-hint">Sélectionnez plusieurs fichiers (Ctrl+clic). Max 10 images.</span>
                        </div>
                        <?php if ($isEdit && !empty($imagesMenu)): ?>
                            <div class="current-gallery">
                                <p class="form-hint">Images actuelles :</p>
                                <div class="gallery-preview">
                                    <?php foreach ($imagesMenu as $img): ?>
                                        <div class="gallery-preview__item">
                                            <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="" style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            <small><?= htmlspecialchars($img['alt_text'] ?? '') ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>

                    <!-- Actions -->
                    <div class="menu-form-actions">
                        <a href="gestion_menus.php" class="btn btn--outline btn--lg">Annuler</a>
                        <button type="submit" class="btn btn--primary btn--lg">
                            <span class="icon"><?= $isEdit ? '💾' : '➕' ?></span>
                            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le menu' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <script>
        (function() {
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
            });
        })();
    </script>
</body>

</html>