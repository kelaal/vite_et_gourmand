<?php
/**
 * Repository d'accès aux données des Horaires d'ouverture (MySQL)
 * Projet ECF Vite & Gourmand
 */

require_once __DIR__ . '/BaseRepository.php';

class HoraireRepository extends BaseRepository {

    /**
     * Récupère tous les horaires d'ouverture du Lundi au Dimanche ordonnés
     * @return array
     */
    public function findAll(): array {
        return $this->pdo->query("
            SELECT * FROM horaire 
            ORDER BY ordre_jour ASC
        ")->fetchAll();
    }

    /**
     * Met à jour les horaires d'un jour spécifique
     * 
     * @param int $horaireId
     * @param string|null $ouverture Format 'HH:MM:SS' ou null
     * @param string|null $fermeture Format 'HH:MM:SS' ou null
     * @param bool $estOuvert
     * @return bool
     */
    public function update(int $horaireId, ?string $ouverture, ?string $fermeture, bool $estOuvert): bool {
        $stmt = $this->pdo->prepare("
            UPDATE horaire 
            SET heure_ouverture = :ouverture, heure_fermeture = :fermeture, est_ouvert = :ouvert
            WHERE horaire_id = :id
        ");

        return $stmt->execute([
            ':ouverture' => $estOuvert ? $ouverture : null,
            ':fermeture' => $estOuvert ? $fermeture : null,
            ':ouvert'    => $estOuvert ? 1 : 0,
            ':id'        => $horaireId,
        ]);
    }
}
