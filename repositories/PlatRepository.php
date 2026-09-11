<?php
/**
 * Repository d'accès aux données des Plats (MySQL)
 * Projet ECF Vite & Gourmand
 */

require_once __DIR__ . '/BaseRepository.php';

class PlatRepository extends BaseRepository {

    /**
     * Récupère tous les plats
     * @return array
     */
    public function findAll(): array {
        return $this->pdo->query("
            SELECT * FROM plat
            ORDER BY FIELD(type_plat, 'entree', 'plat', 'dessert'), titre ASC
        ")->fetchAll();
    }

    /**
     * Récupère les plats par type
     * @param string $type 'entree', 'plat', 'dessert'
     * @return array
     */
    public function findByType(string $type): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM plat
            WHERE type_plat = :type
            ORDER BY titre ASC
        ");
        $stmt->execute([':type' => $type]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère un plat par son ID
     * @param int $platId
     * @return array|null
     */
    public function findById(int $platId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM plat WHERE plat_id = :id");
        $stmt->execute([':id' => $platId]);
        $plat = $stmt->fetch();
        return $plat ?: null;
    }

    /**
     * Récupère les plats d'un menu
     * @param int $menuId
     * @return array
     */
    public function findByMenu(int $menuId): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, mp.menu_id
            FROM plat p
            JOIN menu_plat mp ON p.plat_id = mp.plat_id
            WHERE mp.menu_id = :menu_id
            ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.plat_id ASC
        ");
        $stmt->execute([':menu_id' => $menuId]);
        return $stmt->fetchAll();
    }

    /**
     * Crée un nouveau plat
     * @param array $data
     * @return int ID du plat créé
     */
    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO plat (titre, description, type_plat)
            VALUES (:titre, :description, :type_plat)
        ");
        $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'] ?? '',
            ':type_plat' => $data['type_plat'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un plat
     * @param int $platId
     * @param array $data
     * @return bool
     */
    public function update(int $platId, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE plat
            SET titre = :titre, description = :description, type_plat = :type_plat
            WHERE plat_id = :id
        ");
        return $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'] ?? '',
            ':type_plat' => $data['type_plat'],
            ':id' => $platId,
        ]);
    }

    /**
     * Supprime un plat
     * @param int $platId
     * @return bool
     */
    public function delete(int $platId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM plat WHERE plat_id = :id");
        return $stmt->execute([':id' => $platId]);
    }

    /**
     * Associe un plat à un menu
     * @param int $menuId
     * @param int $platId
     * @return bool
     */
    public function associateToMenu(int $menuId, int $platId): bool {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO menu_plat (menu_id, plat_id)
            VALUES (:menu_id, :plat_id)
        ");
        return $stmt->execute([
            ':menu_id' => $menuId,
            ':plat_id' => $platId,
        ]);
    }

    /**
     * Dissocie un plat d'un menu
     * @param int $menuId
     * @param int $platId
     * @return bool
     */
    public function dissociateFromMenu(int $menuId, int $platId): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM menu_plat WHERE menu_id = :menu_id AND plat_id = :plat_id
        ");
        return $stmt->execute([
            ':menu_id' => $menuId,
            ':plat_id' => $platId,
        ]);
    }

    /**
     * Récupère les allergènes d'un plat
     * @param int $platId
     * @return array
     */
    public function getAllergenes(int $platId): array {
        $stmt = $this->pdo->prepare("
            SELECT a.allergene_id, a.libelle
            FROM allergene a
            JOIN plat_allergene pa ON a.allergene_id = pa.allergene_id
            WHERE pa.plat_id = :plat_id
        ");
        $stmt->execute([':plat_id' => $platId]);
        return $stmt->fetchAll();
    }

    /**
     * Associe un allergène à un plat
     * @param int $platId
     * @param int $allergeneId
     * @return bool
     */
    public function addAllergene(int $platId, int $allergeneId): bool {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO plat_allergene (plat_id, allergene_id)
            VALUES (:plat_id, :allergene_id)
        ");
        return $stmt->execute([
            ':plat_id' => $platId,
            ':allergene_id' => $allergeneId,
        ]);
    }

    /**
     * Dissocie un allergène d'un plat
     * @param int $platId
     * @param int $allergeneId
     * @return bool
     */
    public function removeAllergene(int $platId, int $allergeneId): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM plat_allergene WHERE plat_id = :plat_id AND allergene_id = :allergene_id
        ");
        return $stmt->execute([
            ':plat_id' => $platId,
            ':allergene_id' => $allergeneId,
        ]);
    }
}