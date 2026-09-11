<?php
/**
 * Action Création Menu - Administration
 * ECF : Traitement sécurisé formulaire création menu
 */

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/MenuRepository.php';
require_once __DIR__ . '/../../repositories/PlatRepository.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/modifier_menu.php');
    exit;
}

// CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashError('Token de sécurité invalide.');
    header('Location: ../../admin/modifier_menu.php');
    exit;
}

// Récupération et validation des données
$data = [
    'titre' => trim($_POST['titre'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'theme_id' => (int)($_POST['theme_id'] ?? 0),
    'regime_id' => (int)($_POST['regime_id'] ?? 0),
    'nb_personne_min' => (int)($_POST['nb_personne_min'] ?? 0),
    'prix_base_min' => (float)($_POST['prix_base_min'] ?? 0),
    'stock_disponible' => (int)($_POST['stock_disponible'] ?? 0),
    'conditions_delai_stockage' => trim($_POST['conditions_delai_stockage'] ?? ''),
    'is_active' => !empty($_POST['is_active']) ? 1 : 0,
    'plats' => $_POST['plats'] ?? [],
];

$errors = [];

// Validation
if (empty($data['titre'])) $errors[] = 'Le titre est obligatoire.';
elseif (strlen($data['titre']) > 150) $errors[] = 'Le titre ne peut pas dépasser 150 caractères.';

if (empty($data['description'])) $errors[] = 'La description est obligatoire.';

if ($data['theme_id'] <= 0) $errors[] = 'Le thème est obligatoire.';

if ($data['regime_id'] <= 0) $errors[] = 'Le régime est obligatoire.';

if ($data['nb_personne_min'] < 1) $errors[] = 'Le nombre minimum de personnes doit être au moins 1.';

if ($data['prix_base_min'] < 0) $errors[] = 'Le prix doit être positif.';

if ($data['stock_disponible'] < 0) $errors[] = 'Le stock ne peut pas être négatif.';

if (empty($data['conditions_delai_stockage'])) $errors[] = 'Les conditions de délai/stockage sont obligatoires.';

// Validation plats : au moins 1 par type
$platRepo = new PlatRepository();
$platsSelectionnes = $platRepo->getPdo()->query("SELECT plat_id, type_plat FROM plat WHERE plat_id IN (" . implode(',', array_map('intval', $data['plats'])) . ")")->fetchAll();
$typesRequis = ['entree', 'plat', 'dessert'];
$typesTrouves = [];
foreach ($platsSelectionnes as $p) {
    $typesTrouves[$p['type_plat']] = true;
}
foreach ($typesRequis as $type) {
    if (!isset($typesTrouves[$type])) {
        $labels = ['entree' => 'entrée', 'plat' => 'plat principal', 'dessert' => 'dessert'];
        $errors[] = "Au moins un " . $labels[$type] . " doit être sélectionné.";
    }
}

if (!empty($errors)) {
    $_SESSION['flash_error'] = implode('<br>', $errors);
    header('Location: ../../admin/modifier_menu.php');
    exit;
}

// Gestion image principale
$imageUrl = '';
if (isset($_FILES['image_principale']) && $_FILES['image_principale']['error'] === UPLOAD_ERR_OK) {
    $uploadResult = uploadImage($_FILES['image_principale']);
    if (!$uploadResult['success']) {
        setFlashError($uploadResult['message']);
        header('Location: ../../admin/modifier_menu.php');
        exit;
    }
    $imageUrl = $uploadResult['path'];
}

// Début transaction
$pdo = $menuRepo->getPdo();
$pdo->beginTransaction();

try {
    // 1. Insertion menu
    $stmt = $pdo->prepare("
        INSERT INTO menu (titre, description, nb_personne_min, prix_base_min, stock_disponible, conditions_delai_stockage, theme_id, regime_id, image_url, is_active)
        VALUES (:titre, :description, :nb_personne_min, :prix_base_min, :stock_disponible, :conditions_delai_stockage, :theme_id, :regime_id, :image_url, :is_active)
    ");
    $stmt->execute([
        ':titre' => $data['titre'],
        ':description' => $data['description'],
        ':nb_personne_min' => $data['nb_personne_min'],
        ':prix_base_min' => $data['prix_base_min'],
        ':stock_disponible' => $data['stock_disponible'],
        ':conditions_delai_stockage' => $data['conditions_delai_stockage'],
        ':theme_id' => $data['theme_id'],
        ':regime_id' => $data['regime_id'],
        ':image_url' => $imageUrl,
        ':is_active' => $data['is_active'],
    ]);
    $menuId = (int)$pdo->lastInsertId();

    // 2. Association plats
    foreach ($data['plats'] as $platId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO menu_plat (menu_id, plat_id) VALUES (:menu_id, :plat_id)");
        $stmt->execute([':menu_id' => $menuId, ':plat_id' => (int)$platId]);
    }

    // 3. Galerie images
    if (isset($_FILES['images_galerie'])) {
        $files = $_FILES['images_galerie'];
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage([
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ]);
                if ($uploadResult['success']) {
                    $stmt = $pdo->prepare("INSERT INTO menu_image (menu_id, image_url, alt_text) VALUES (:menu_id, :image_url, :alt_text)");
                    $stmt->execute([
                        ':menu_id' => $menuId,
                        ':image_url' => $uploadResult['path'],
                        ':alt_text' => $files['name'][$i],
                    ]);
                }
            }
        }
    }

    $pdo->commit();
    setFlashSuccess('Menu créé avec succès.');
    header('Location: ../../admin/gestion_menus.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    setFlashError('Erreur lors de la création : ' . $e->getMessage());
    header('Location: ../../admin/modifier_menu.php');
    exit;
}

/**
 * Upload d'image sécurisé
 */
function uploadImage(array $file): array {
    $allowedTypes = ['image/webp', 'image/jpeg', 'image/png'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Format d\'image non autorisé (WebP, JPEG, PNG uniquement).'];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Image trop volumineuse (max 2MB).'];
    }

    $ext = match($file['type']) {
        'image/webp' => '.webp',
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        default => ''
    };
    $filename = 'menu_' . bin2hex(random_bytes(8)) . $ext;
    $uploadDir = __DIR__ . '/../../public/img/menus/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Erreur lors de l\'upload.'];
    }

    return ['success' => true, 'path' => 'public/img/menus/' . $filename];
}