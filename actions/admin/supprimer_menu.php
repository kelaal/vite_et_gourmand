<?php
/**
 * Action Suppression Menu - Administration
 * ECF : Suppression sécurisée avec cascade (plats, allergènes, images, associations)
 */

session_start();
require_once __DIR__ . '/../../config/require_auth.php';
require_once __DIR__ . '/../../repositories/MenuRepository.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/gestion_menus.php');
    exit;
}

// CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashError('Token de sécurité invalide.');
    header('Location: ../../admin/gestion_menus.php');
    exit;
}

$menuId = (int)($_POST['menu_id'] ?? 0);
if (!$menuId) {
    setFlashError('ID de menu manquant.');
    header('Location: ../../admin/gestion_menus.php');
    exit;
}

$menuRepo = new MenuRepository();
$pdo = $menuRepo->getPdo();
$pdo->beginTransaction();

try {
    // Vérifier que le menu existe
    $stmt = $pdo->prepare("SELECT image_url FROM menu WHERE menu_id = :id");
    $stmt->execute([':id' => $menuId]);
    $menu = $stmt->fetch();

    if (!$menu) {
        $pdo->rollBack();
        setFlashError('Menu introuvable.');
        header('Location: ../../admin/gestion_menus.php');
        exit;
    }

    // La suppression en cascade est gérée par les contraintes FK (ON DELETE CASCADE)
    // Tables concernées : menu_image, menu_plat (via plat_allergene non concerné car plat non supprimé)
    // Attention : on ne supprime PAS les plats, juste les associations menu_plat

    // 1. Supprimer les images de la galerie (fichiers + BDD)
    $stmt = $pdo->prepare("SELECT image_url FROM menu_image WHERE menu_id = :id");
    $stmt->execute([':id' => $menuId]);
    $images = $stmt->fetchAll();
    foreach ($images as $img) {
        $filePath = __DIR__ . '/../../' . $img['image_url'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
    $pdo->prepare("DELETE FROM menu_image WHERE menu_id = :id")->execute([':id' => $menuId]);

    // 2. Supprimer l'image principale (fichier)
    if (!empty($menu['image_url'])) {
        $filePath = __DIR__ . '/../../' . $menu['image_url'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    // 3. Supprimer les associations menu_plat (cascade géré par FK)
    $pdo->prepare("DELETE FROM menu_plat WHERE menu_id = :id")->execute([':id' => $menuId]);

    // 4. Supprimer le menu
    $stmt = $pdo->prepare("DELETE FROM menu WHERE menu_id = :id");
    $stmt->execute([':id' => $menuId]);

    $pdo->commit();
    setFlashSuccess('Menu supprimé avec succès.');
    header('Location: ../../admin/gestion_menus.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    setFlashError('Erreur lors de la suppression : ' . $e->getMessage());
    header('Location: ../../admin/gestion_menus.php');
    exit;
}