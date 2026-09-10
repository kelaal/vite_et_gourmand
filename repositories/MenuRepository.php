<?php
/**
 * Repository d'accès aux données des Menus et Plats (MySQL)
 * Projet ECF Vite & Gourmand
 */

require_once __DIR__ . '/BaseRepository.php';

class MenuRepository extends BaseRepository {

    /**
     * Récupère les menus mis en avant pour la page d'accueil (vitrine)
     * @param int $limit
     * @return array
     */
    public function findVitrine(int $limit = 3): array {
        $stmt = $this->pdo->prepare("
            SELECT m.menu_id, m.titre, m.description, m.nb_personne_min, m.prix_base_min, 
                   m.stock_disponible, m.image_url, t.libelle AS theme_libelle, r.libelle AS regime_libelle
            FROM menu m
            JOIN theme t ON m.theme_id = t.theme_id
            JOIN regime r ON m.regime_id = r.regime_id
            WHERE m.is_active = 1
            ORDER BY m.menu_id ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère la liste complète des menus avec filtres dynamiques (exigence ECF)
     * 
     * @param array $filters [
     *    'prix_max' => float,
     *    'prix_min' => float,
     *    'theme_id' => int,
     *    'regime_id' => int,
     *    'nb_personnes_min' => int,
     *    'search' => string
     * ]
     * @return array
     */
    public function findAllFiltered(array $filters = []): array {
        $sql = "
            SELECT m.menu_id, m.titre, m.description, m.nb_personne_min, m.prix_base_min, 
                   m.stock_disponible, m.conditions_delai_stockage, m.image_url, m.is_active,
                   t.theme_id, t.libelle AS theme_libelle, 
                   r.regime_id, r.libelle AS regime_libelle
            FROM menu m
            JOIN theme t ON m.theme_id = t.theme_id
            JOIN regime r ON m.regime_id = r.regime_id
            WHERE m.is_active = 1
        ";
        $params = [];

        // Filtre prix maximum
        if (!empty($filters['prix_max'])) {
            $sql .= " AND m.prix_base_min <= :prix_max";
            $params[':prix_max'] = (float)$filters['prix_max'];
        }

        // Filtre prix minimum (fourchette)
        if (!empty($filters['prix_min'])) {
            $sql .= " AND m.prix_base_min >= :prix_min";
            $params[':prix_min'] = (float)$filters['prix_min'];
        }

        // Filtre par Thème
        if (!empty($filters['theme_id'])) {
            $sql .= " AND m.theme_id = :theme_id";
            $params[':theme_id'] = (int)$filters['theme_id'];
        }

        // Filtre par Régime
        if (!empty($filters['regime_id'])) {
            $sql .= " AND m.regime_id = :regime_id";
            $params[':regime_id'] = (int)$filters['regime_id'];
        }

        // Filtre par nombre de personnes minimum
        if (!empty($filters['nb_personnes_min'])) {
            $sql .= " AND m.nb_personne_min <= :nb_personnes_min";
            $params[':nb_personnes_min'] = (int)$filters['nb_personnes_min'];
        }

        // Recherche par mot-clé
        if (!empty($filters['search'])) {
            $sql .= " AND (m.titre LIKE :search OR m.description LIKE :search)";
            $params[':search'] = '%' . trim($filters['search']) . '%';
        }

        $sql .= " ORDER BY m.prix_base_min ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère un menu par son ID
     * @param int $menuId
     * @return array|null
     */
    public function findById(int $menuId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT m.*, t.libelle AS theme_libelle, r.libelle AS regime_libelle
            FROM menu m
            JOIN theme t ON m.theme_id = t.theme_id
            JOIN regime r ON m.regime_id = r.regime_id
            WHERE m.menu_id = :id
        ");
        $stmt->execute([':id' => $menuId]);
        $menu = $stmt->fetch();
        return $menu ?: null;
    }

    /**
     * Récupère la vue détaillée complète d'un menu (Plats, Allergènes, Galerie d'images)
     * @param int $menuId
     * @return array|null
     */
    public function findDetails(int $menuId): ?array {
        $menu = $this->findById($menuId);
        if (!$menu) return null;

        // 1. Récupération des plats du menu
        $stmtPlats = $this->pdo->prepare("
            SELECT p.plat_id, p.titre, p.description, p.type_plat
            FROM plat p
            JOIN menu_plat mp ON p.plat_id = mp.plat_id
            WHERE mp.menu_id = :menu_id
            ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.plat_id ASC
        ");
        $stmtPlats->execute([':menu_id' => $menuId]);
        $plats = $stmtPlats->fetchAll();

        // 2. Récupération des allergènes par plat
        foreach ($plats as &$plat) {
            $stmtAllerg = $this->pdo->prepare("
                SELECT a.allergene_id, a.libelle
                FROM allergene a
                JOIN plat_allergene pa ON a.allergene_id = pa.allergene_id
                WHERE pa.plat_id = :plat_id
            ");
            $stmtAllerg->execute([':plat_id' => $plat['plat_id']]);
            $plat['allergenes'] = $stmtAllerg->fetchAll();
        }
        $menu['plats'] = $plats;

        // 3. Récupération de la galerie d'images
        $stmtImages = $this->pdo->prepare("
            SELECT image_id, image_url, alt_text 
            FROM menu_image 
            WHERE menu_id = :menu_id
        ");
        $stmtImages->execute([':menu_id' => $menuId]);
        $menu['images'] = $stmtImages->fetchAll();

        return $menu;
    }

    /**
     * Récupère l'ensemble des thèmes existants
     * @return array
     */
    public function getAllThemes(): array {
        return $this->pdo->query("SELECT * FROM theme ORDER BY libelle ASC")->fetchAll();
    }

    /**
     * Récupère l'ensemble des régimes existants
     * @return array
     */
    public function getAllRegimes(): array {
        return $this->pdo->query("SELECT * FROM regime ORDER BY libelle ASC")->fetchAll();
    }
}
