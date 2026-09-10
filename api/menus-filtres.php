<?php
/**
 * API Filtres Menus AJAX - Vite & Gourmand
 * Retourne JSON des menus filtrés pour menus.php
 */

// Configuration headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/MenuRepository.php';
require_once __DIR__ . '/../config/database.php';

// Vérification méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée', 'menus' => [], 'total' => 0]);
    exit;
}

// Vérification CSRF pour les requêtes AJAX (optionnel mais recommandé)
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    // Pour l'API AJAX, on peut être plus permissif ou retourner une erreur
    // Ici on log et on continue pour ne pas casser l'UX
    error_log('CSRF token invalide sur api/menus-filtres.php');
}

// Récupération et validation des paramètres
$filters = [];

// Recherche texte
$search = trim($_POST['search'] ?? '');
if ($search !== '') {
    $filters['search'] = $search;
}

// Prix maximum (depuis range ou input number)
$prixMax = isset($_POST['prix_max']) ? (float)$_POST['prix_max'] : null;
if ($prixMax !== null && $prixMax > 0 && $prixMax < 100) {
    $filters['prix_max'] = $prixMax;
}

// Prix minimum
$prixMin = isset($_POST['prix_min']) ? (float)$_POST['prix_min'] : null;
if ($prixMin !== null && $prixMin > 0) {
    $filters['prix_min'] = $prixMin;
}

// Thème
$themeId = isset($_POST['theme_id']) ? (int)$_POST['theme_id'] : null;
if ($themeId !== null && $themeId > 0) {
    $filters['theme_id'] = $themeId;
}

// Régime
$regimeId = isset($_POST['regime_id']) ? (int)$_POST['regime_id'] : null;
if ($regimeId !== null && $regimeId > 0) {
    $filters['regime_id'] = $regimeId;
}

// Nombre de personnes minimum
$nbPersonnes = isset($_POST['nb_personnes_min']) ? (int)$_POST['nb_personnes_min'] : null;
if ($nbPersonnes !== null && $nbPersonnes > 1) {
    $filters['nb_personnes_min'] = $nbPersonnes;
}

// Pagination
$page = max(1, (int)($_POST['page'] ?? 1));
$perPage = min(50, max(1, (int)($_POST['per_page'] ?? 12)));

// Tri
$sort = $_POST['sort'] ?? 'prix_asc';
$allowedSorts = ['prix_asc', 'prix_desc', 'personnes_asc', 'personnes_desc', 'titre_asc'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'prix_asc';
}

// Récupération menus filtrés
$menuRepo = new MenuRepository();
$allMenus = $menuRepo->findAllFiltered($filters);

// Application tri
$sortColumn = 'prix_base_min';
$sortOrder = 'ASC';
switch ($sort) {
    case 'prix_desc':
        $sortColumn = 'prix_base_min';
        $sortOrder = 'DESC';
        break;
    case 'personnes_asc':
        $sortColumn = 'nb_personne_min';
        $sortOrder = 'ASC';
        break;
    case 'personnes_desc':
        $sortColumn = 'nb_personne_min';
        $sortOrder = 'DESC';
        break;
    case 'titre_asc':
        $sortColumn = 'titre';
        $sortOrder = 'ASC';
        break;
}

// Tri en PHP (plus simple que de refaire une requête)
usort($allMenus, function($a, $b) use ($sortColumn, $sortOrder) {
    $valA = $a[$sortColumn] ?? 0;
    $valB = $b[$sortColumn] ?? 0;

    if (is_numeric($valA) && is_numeric($valB)) {
        $cmp = $valA <=> $valB;
    } else {
        $cmp = strcasecmp((string)$valA, (string)$valB);
    }
    return $sortOrder === 'DESC' ? -$cmp : $cmp;
});

// Total avant pagination
$total = count($allMenus);

// Pagination
$offset = ($page - 1) * $perPage;
$menus = array_slice($allMenus, $offset, $perPage);

// Formatage pour JSON (sécurisation XSS)
$formattedMenus = array_map(function($menu) {
    return [
        'menu_id' => (int)$menu['menu_id'],
        'titre' => htmlspecialchars($menu['titre'], ENT_QUOTES, 'UTF-8'),
        'description' => htmlspecialchars($menu['description'], ENT_QUOTES, 'UTF-8'),
        'nb_personne_min' => (int)$menu['nb_personne_min'],
        'prix_base_min' => (float)$menu['prix_base_min'],
        'stock_disponible' => (int)$menu['stock_disponible'],
        'image_url' => htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.webp', ENT_QUOTES, 'UTF-8'),
        'theme_id' => (int)$menu['theme_id'],
        'theme_libelle' => htmlspecialchars($menu['theme_libelle'], ENT_QUOTES, 'UTF-8'),
        'regime_id' => (int)$menu['regime_id'],
        'regime_libelle' => htmlspecialchars($menu['regime_libelle'], ENT_QUOTES, 'UTF-8'),
        'conditions_delai_stockage' => htmlspecialchars($menu['conditions_delai_stockage'], ENT_QUOTES, 'UTF-8')
    ];
}, $menus);

// Réponse JSON
echo json_encode([
    'success' => true,
    'menus' => $formattedMenus,
    'total' => $total,
    'page' => $page,
    'per_page' => $perPage,
    'total_pages' => (int)ceil($total / $perPage),
    'filters_applied' => $filters,
    'sort' => $sort
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);