<?php
/**
 * Page Catalogue Menus - Vite & Gourmand
 * ECF : Vue globale avec filtres dynamiques AJAX (sans rechargement)
 */

require_once __DIR__ . '/config/require_auth.php';
require_once __DIR__ . '/repositories/MenuRepository.php';

$menuRepo = new MenuRepository();

// Récupération données pour filtres (thèmes, régimes)
$themes = $menuRepo->getAllThemes();
$regimes = $menuRepo->getAllRegimes();

// Menus initiaux (sans filtre)
$menus = $menuRepo->findAllFiltered();

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tous nos menus traiteur - Vite & Gourmand : Filtrez par prix, thème, régime, nombre de personnes">
    <title>Nos Menus | Vite & Gourmand</title>

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
        <!-- HERO SECTION PAGE MENUS -->
        <header class="page-hero">
            <div class="page-hero__bg">
                <picture>
                    <source srcset="public/img/hero-bg.avif" type="image/avif">
                    <source srcset="public/img/hero-bg.webp" type="image/webp">
                    <img src="public/img/hero-bg.jpeg" alt="" width="1920" height="1080">
                </picture>
                <div class="page-hero__overlay"></div>
            </div>
            <div class="page-hero__content">
                <h1 class="page-hero__title">Nos Menus d'Exception</h1>
                <p class="page-hero__desc">Filtrez, comparez et choisissez la composition parfaite pour votre événement</p>
            </div>
        </header>

        <!-- SECTION FILTRES + GRILLE -->
        <section class="menus-catalogue">
            <div class="menus-catalogue__container">
                <!-- Sidebar Filtres -->
                <aside class="filters-sidebar" id="filtersSidebar" role="complementary" aria-label="Filtres menus">
                    <div class="filters-header">
                        <h2>Filtres</h2>
                        <button type="button" class="filters-toggle" id="filtersToggle" aria-expanded="false" aria-controls="filtersForm" aria-label="Ouvrir les filtres">
                            <span class="filters-toggle__icon">🔍</span>
                            <span>Filtrer</span>
                        </button>
                    </div>

                    <form id="filtersForm" class="filters-form" role="search" aria-label="Filtres des menus">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <!-- Recherche texte -->
                        <div class="filter-group">
                            <label for="filter-search" class="filter-label">Rechercher</label>
                            <div class="filter-input-wrapper">
                                <input type="search"
                                    id="filter-search"
                                    name="search"
                                    class="filter-input"
                                    placeholder="Nom, description..."
                                    aria-describedby="search-hint">
                                <span class="filter-input-icon" aria-hidden="true">🔍</span>
                            </div>
                            <span class="filter-hint" id="search-hint">Recherche par mot-clé</span>
                        </div>

                        <!-- Prix maximum -->
                        <div class="filter-group">
                            <label for="filter-prix-max" class="filter-label">
                                Prix max / personne
                                <span class="filter-value-display" id="prixMaxValue">—</span>
                            </label>
                            <input type="range"
                                id="filter-prix-max"
                                name="prix_max"
                                class="filter-range"
                                min="0"
                                max="100"
                                step="5"
                                value="100"
                                aria-describedby="prix-max-hint">
                            <div class="filter-range-labels">
                                <span>0 €</span>
                                <span>100 €</span>
                            </div>
                            <span class="filter-hint" id="prix-max-hint">Prix maximum par personne</span>
                        </div>

                        <!-- Fourchette de prix (min/max) -->
                        <div class="filter-group">
                            <label class="filter-label">Fourchette de prix</label>
                            <div class="filter-range-dual">
                                <div class="filter-range-input">
                                    <label for="filter-prix-min" class="visually-hidden">Prix minimum</label>
                                    <input type="number"
                                        id="filter-prix-min"
                                        name="prix_min"
                                        class="filter-input filter-input--small"
                                        placeholder="Min"
                                        min="0"
                                        max="100"
                                        step="1"
                                        aria-describedby="prix-min-hint">
                                </div>
                                <span class="filter-range-separator" aria-hidden="true">—</span>
                                <div class="filter-range-input">
                                    <label for="filter-prix-max2" class="visually-hidden">Prix maximum</label>
                                    <input type="number"
                                        id="filter-prix-max2"
                                        name="prix_max"
                                        class="filter-input filter-input--small"
                                        placeholder="Max"
                                        min="0"
                                        max="100"
                                        step="1"
                                        value="100"
                                        aria-describedby="prix-max2-hint">
                                </div>
                            </div>
                            <span class="filter-hint" id="prix-min-hint">Prix minimum par personne</span>
                        </div>

                        <!-- Thème -->
                        <div class="filter-group">
                            <label for="filter-theme" class="filter-label">Thème</label>
                            <select id="filter-theme" name="theme_id" class="filter-select">
                                <option value="">Tous les thèmes</option>
                                <?php foreach ($themes as $theme): ?>
                                    <option value="<?= (int)$theme['theme_id'] ?>">
                                        <?= htmlspecialchars($theme['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Régime -->
                        <div class="filter-group">
                            <label for="filter-regime" class="filter-label">Régime alimentaire</label>
                            <select id="filter-regime" name="regime_id" class="filter-select">
                                <option value="">Tous les régimes</option>
                                <?php foreach ($regimes as $regime): ?>
                                    <option value="<?= (int)$regime['regime_id'] ?>">
                                        <?= htmlspecialchars($regime['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Nombre de personnes minimum -->
                        <div class="filter-group">
                            <label for="filter-personnes" class="filter-label">
                                Nombre de personnes min
                                <span class="filter-value-display" id="personnesValue">—</span>
                            </label>
                            <input type="range"
                                id="filter-personnes"
                                name="nb_personnes_min"
                                class="filter-range"
                                min="1"
                                max="50"
                                step="1"
                                value="1"
                                aria-describedby="personnes-hint">
                            <div class="filter-range-labels">
                                <span>1</span>
                                <span>50+</span>
                            </div>
                            <span class="filter-hint" id="personnes-hint">Minimum de convives requis</span>
                        </div>

                        <!-- Actions filtres -->
                        <div class="filter-actions">
                            <button type="submit" class="btn btn--primary btn--fullwidth" id="btnApplyFilters">
                                <span>Appliquer</span>
                            </button>
                            <button type="button" class="btn btn--outline btn--fullwidth" id="btnResetFilters">
                                <span>Réinitialiser</span>
                            </button>
                        </div>
                    </form>

                    <!-- Badge nombre de résultats (mobile) -->
                    <div class="filters-results" id="filtersResults" aria-live="polite" hidden>
                        <span id="resultsCount">0</span> menu(s) trouvé(s)
                    </div>
                </aside>

                <!-- Zone Résultats -->
                <div class="menus-results">
                    <!-- Barre d'outils résultats -->
                    <div class="results-toolbar" id="resultsToolbar">
                        <div class="results-info">
                            <span id="resultsCountText">Chargement...</span>
                        </div>
                        <div class="results-sort">
                            <label for="sort-select" class="visually-hidden">Trier par</label>
                            <select id="sort-select" name="sort" class="filter-select filter-select--small">
                                <option value="prix_asc">Prix croissant</option>
                                <option value="prix_desc">Prix décroissant</option>
                                <option value="personnes_asc">Nb personnes croissant</option>
                                <option value="personnes_desc">Nb personnes décroissant</option>
                                <option value="titre_asc">Nom A-Z</option>
                            </select>
                        </div>
                    </div>

                    <!-- Grille des menus -->
                    <div class="menus_grid" id="menusGrid" role="list" aria-label="Liste des menus">
                        <!-- Contenu chargé via JS / SSR initial -->
                        <?php if (!empty($menus)): ?>
                            <?php foreach ($menus as $menu): ?>
                                <article class="menu_card" role="listitem" data-menu-id="<?= (int)$menu['menu_id'] ?>" data-prix="<?= (float)$menu['prix_base_min'] ?>" data-personnes="<?= (int)$menu['nb_personne_min'] ?>" data-theme="<?= (int)$menu['theme_id'] ?>" data-regime="<?= (int)$menu['regime_id'] ?>">
                                    <div class="menu_card__img_wrapper">
                                        <img src="<?= htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.webp') ?>" alt="<?= htmlspecialchars($menu['titre']) ?>" loading="lazy">
                                        <div class="menu_card__badges">
                                            <span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span>
                                            <span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span>
                                        </div>
                                    </div>

                                    <div class="menu_card__content">
                                        <h3 class="menu_card__title"><?= htmlspecialchars($menu['titre']) ?></h3>
                                        <p class="menu_card__description"><?= htmlspecialchars($menu['description']) ?></p>

                                        <div class="menu_card__info">
                                            <span class="menu_card__personnes">
                                                👥 Min. <?= (int)$menu['nb_personne_min'] ?> personnes
                                            </span>
                                            <span class="menu_card__price">
                                                <?= number_format((float)$menu['prix_base_min'], 2, ',', ' ') ?> € <small>/ pers.</small>
                                            </span>
                                        </div>

                                        <div class="menu_card__cta">
                                            <a href="menu-detail.php?id=<?= (int)$menu['menu_id'] ?>" class="btn-menu-outline">Détails</a>
                                            <a href="commander.php?menu_id=<?= (int)$menu['menu_id'] ?>" class="btn-menu-primary">Commander</a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="menus-empty" role="status">
                                <div class="menus-empty__icon">🍽️</div>
                                <h3>Aucun menu disponible</h3>
                                <p>Essayez de modifier vos filtres</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination (pour plus tard) -->
                    <div class="results-pagination" id="resultsPagination" hidden>
                        <!-- Pagination à implémenter si > 12 menus -->
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Scripts Filtres AJAX -->
    <script>
        (function() {
            'use strict';

            // Éléments DOM
            const filtersForm = document.getElementById('filtersForm');
            const filtersSidebar = document.getElementById('filtersSidebar');
            const filtersToggle = document.getElementById('filtersToggle');
            const menusGrid = document.getElementById('menusGrid');
            const resultsToolbar = document.getElementById('resultsToolbar');
            const resultsCountText = document.getElementById('resultsCountText');
            const resultsPagination = document.getElementById('resultsPagination');
            const filtersResults = document.getElementById('filtersResults');
            const resultsCount = document.getElementById('resultsCount');
            const btnResetFilters = document.getElementById('btnResetFilters');
            const sortSelect = document.getElementById('sort-select');

            // Inputs filtres
            const filterSearch = document.getElementById('filter-search');
            const filterPrixMax = document.getElementById('filter-prix-max');
            const filterPrixMaxValue = document.getElementById('prixMaxValue');
            const filterPrixMin = document.getElementById('filter-prix-min');
            const filterPrixMax2 = document.getElementById('filter-prix-max2');
            const filterTheme = document.getElementById('filter-theme');
            const filterRegime = document.getElementById('filter-regime');
            const filterPersonnes = document.getElementById('filter-personnes');
            const personnesValue = document.getElementById('personnesValue');

            // État
            let debounceTimer = null;
            const DEBOUNCE_MS = 300;
            let currentPage = 1;
            const PER_PAGE = 12;
            let isLoading = false;

            // Initialisation affichage valeurs range
            updateRangeDisplay();

            // --- Gestion affichage valeurs range ---
            function updateRangeDisplay() {
                filterPrixMaxValue.textContent = filterPrixMax.value + ' €';
                personnesValue.textContent = filterPersonnes.value === '50' ? '50+' : filterPersonnes.value;
            }

            filterPrixMax.addEventListener('input', updateRangeDisplay);
            filterPersonnes.addEventListener('input', updateRangeDisplay);

            // Sync des deux champs prix max
            filterPrixMax.addEventListener('input', function() {
                filterPrixMax2.value = this.value;
            });
            filterPrixMax2.addEventListener('input', function() {
                filterPrixMax.value = this.value;
                updateRangeDisplay();
            });

            // --- Toggle Sidebar Mobile ---
            filtersToggle?.addEventListener('click', function() {
                const isOpen = filtersSidebar.classList.toggle('is-open');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            // Fermer sidebar au clic sur un résultat (mobile)
            menusGrid?.addEventListener('click', function(e) {
                if (window.innerWidth < 860 && filtersSidebar?.classList.contains('is-open')) {
                    filtersSidebar.classList.remove('is-open');
                    filtersToggle?.setAttribute('aria-expanded', 'false');
                }
            });

            // --- Réinitialisation filtres ---
            btnResetFilters?.addEventListener('click', function() {
                filtersForm.reset();
                updateRangeDisplay();
                // Trigger recherche après reset
                setTimeout(() => performSearch(), 0);
            });

            // --- Tri ---
            sortSelect?.addEventListener('change', function() {
                currentPage = 1;
                performSearch();
            });

            // --- Soumission formulaire (prevent default, AJAX) ---
            filtersForm?.addEventListener('submit', function(e) {
                e.preventDefault();
                currentPage = 1;
                performSearch();
            });

            // --- Debounced search sur inputs ---
            const debouncedInputs = [filterSearch, filterPrixMin, filterPrixMax2, filterTheme, filterRegime, filterPersonnes];
            debouncedInputs.forEach(input => {
                if (input) {
                    input.addEventListener('input', function() {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => {
                            currentPage = 1;
                            performSearch();
                        }, DEBOUNCE_MS);
                    });

                    // Pour select, déclencher immédiatement
                    if (input.tagName === 'SELECT') {
                        input.addEventListener('change', function() {
                            clearTimeout(debounceTimer);
                            currentPage = 1;
                            performSearch();
                        });
                    }
                }
            });

            // --- Fonction principale de recherche AJAX ---
            async function performSearch() {
                if (isLoading) return;
                isLoading = true;

                // Afficher état de chargement
                showLoading(true);

                // Construction FormData
                const formData = new FormData(filtersForm);
                formData.append('page', currentPage);
                formData.append('per_page', PER_PAGE);
                formData.append('sort', sortSelect?.value || 'prix_asc');

                try {
                    const response = await fetch('api/menus-filtres.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Erreur serveur : ' + response.status);
                    }

                    const data = await response.json();

                    // Mise à jour grille
                    renderMenus(data.menus);
                    updateResultsCount(data.total);

                    // Pagination
                    if (data.total > PER_PAGE) {
                        renderPagination(data.total, currentPage, PER_PAGE);
                        resultsPagination.hidden = false;
                    } else {
                        resultsPagination.hidden = true;
                    }

                } catch (error) {
                    console.error('Erreur filtres:', error);
                    showError('Erreur lors du chargement des menus. Veuillez réessayer.');
                } finally {
                    isLoading = false;
                    showLoading(false);
                }
            }

            // --- Rendu grille menus ---
            function renderMenus(menus) {
                if (!menusGrid) return;

                if (!menus || menus.length === 0) {
                    menusGrid.innerHTML = `
                        <div class="menus-empty" role="status">
                            <div class="menus-empty__icon">🔍</div>
                            <h3>Aucun menu ne correspond</h3>
                            <p>Essayez d'élargir vos critères de recherche</p>
                            <button type="button" class="btn btn--outline" onclick="document.getElementById('btnResetFilters').click()">Réinitialiser les filtres</button>
                        </div>
                    `;
                    return;
                }

                menusGrid.innerHTML = menus.map(menu => `
                    <article class="menu_card" role="listitem"
                        data-menu-id="${menu.menu_id}"
                        data-prix="${menu.prix_base_min}"
                        data-personnes="${menu.nb_personne_min}"
                        data-theme="${menu.theme_id}"
                        data-regime="${menu.regime_id}">
                        <div class="menu_card__img_wrapper">
                            <img src="${escapeHtml(menu.image_url || 'public/img/presentation-photo.webp')}"
                                 alt="${escapeHtml(menu.titre)}"
                                 loading="lazy">
                            <div class="menu_card__badges">
                                <span class="badge badge--theme">${escapeHtml(menu.theme_libelle)}</span>
                                <span class="badge badge--regime">${escapeHtml(menu.regime_libelle)}</span>
                            </div>
                        </div>
                        <div class="menu_card__content">
                            <h3 class="menu_card__title">${escapeHtml(menu.titre)}</h3>
                            <p class="menu_card__description">${escapeHtml(menu.description)}</p>
                            <div class="menu_card__info">
                                <span class="menu_card__personnes">👥 Min. ${menu.nb_personne_min} personnes</span>
                                <span class="menu_card__price">${formatPrice(menu.prix_base_min)} € <small>/ pers.</small></span>
                            </div>
                            <div class="menu_card__cta">
                                <a href="menu-detail.php?id=${menu.menu_id}" class="btn-menu-outline">Détails</a>
                                <a href="commander.php?menu_id=${menu.menu_id}" class="btn-menu-primary">Commander</a>
                            </div>
                        </div>
                    </article>
                `).join('');
            }

            // --- Mise à jour compteur résultats ---
            function updateResultsCount(total) {
                const text = total === 0
                    ? 'Aucun menu trouvé'
                    : total === 1
                        ? '1 menu trouvé'
                        : `${total} menus trouvés`;

                if (resultsCountText) resultsCountText.textContent = text;
                if (resultsCount) resultsCount.textContent = total;
                if (filtersResults) filtersResults.hidden = total === 0;
            }

            // --- Pagination ---
            function renderPagination(total, page, perPage) {
                if (!resultsPagination) return;

                const totalPages = Math.ceil(total / perPage);
                if (totalPages <= 1) {
                    resultsPagination.hidden = true;
                    return;
                }

                let html = '<nav class="pagination" aria-label="Pagination des menus"><ul>';

                // Précédent
                html += `<li><button class="pagination-btn ${page === 1 ? 'is-disabled' : ''}"
                    data-page="${page - 1}" ${page === 1 ? 'disabled' : ''} aria-label="Page précédente">‹</button></li>`;

                // Pages
                let start = Math.max(1, page - 2);
                let end = Math.min(totalPages, page + 2);

                if (start > 1) {
                    html += `<li><button class="pagination-btn" data-page="1">1</button></li>`;
                    if (start > 2) html += `<li><span class="pagination-ellipsis">…</span></li>`;
                }

                for (let i = start; i <= end; i++) {
                    html += `<li><button class="pagination-btn ${i === page ? 'is-active' : ''}" data-page="${i}">${i}</button></li>`;
                }

                if (end < totalPages) {
                    if (end < totalPages - 1) html += `<li><span class="pagination-ellipsis">…</span></li>`;
                    html += `<li><button class="pagination-btn" data-page="${totalPages}">${totalPages}</button></li>`;
                }

                // Suivant
                html += `<li><button class="pagination-btn ${page === totalPages ? 'is-disabled' : ''}"
                    data-page="${page + 1}" ${page === totalPages ? 'disabled' : ''} aria-label="Page suivante">›</button></li>`;

                html += '</ul></nav>';
                resultsPagination.innerHTML = html;

                // Event listeners pagination
                resultsPagination.querySelectorAll('.pagination-btn:not(.is-disabled)').forEach(btn => {
                    btn.addEventListener('click', function() {
                        currentPage = parseInt(this.dataset.page, 10);
                        performSearch();
                        // Scroll vers le haut des résultats
                        resultsToolbar?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });
            }

            // --- États UI ---
            function showLoading(show) {
                if (show) {
                    menusGrid?.classList.add('is-loading');
                    resultsToolbar?.classList.add('is-loading');
                } else {
                    menusGrid?.classList.remove('is-loading');
                    resultsToolbar?.classList.remove('is-loading');
                }
            }

            function showError(message) {
                menusGrid.innerHTML = `
                    <div class="menus-empty menus-empty--error" role="alert">
                        <div class="menus-empty__icon">⚠️</div>
                        <h3>Erreur</h3>
                        <p>${escapeHtml(message)}</p>
                        <button type="button" class="btn btn--primary" onclick="performSearch()">Réessayer</button>
                    </div>
                `;
            }

            // --- Utilitaires ---
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function formatPrice(price) {
                return Number(price).toLocaleString('fr-FR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Initialisation : mise à jour compteur initial
            const initialCount = menusGrid?.querySelectorAll('.menu_card').length || 0;
            updateResultsCount(initialCount);

        })();
    </script>
</body>

</html>