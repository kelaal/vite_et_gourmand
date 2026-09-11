<?php
/**
 * Repository d'accès aux données des Allergènes (MySQL)
 * Projet ECF Vite & Gourmand
 */

require_once __DIR__ . '/BaseRepository.php';

class AllergeneRepository extends BaseRepository {

    /**
     * Récupère tous les allergènes
     * @return array
     */
    public function findAll(): array {
        return $this->pdo->query("
            SELECT * FROM allergene
            ORDER BY libelle ASC
        ")->fetchAll();
    }

    /**
     * Récupère un allergène par son ID
     * @param int $allergeneId
     * @return array|null
     */
    public function findById(int $allergeneId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM allergene WHERE allergene_id = :id");
        $stmt->execute([':id' => $allergeneId]);
        $allergene = $stmt->fetch();
        return $allergene ?: null;
    }
}