<?php
/**
 * Repository d'accès aux données des Avis Clients (MySQL)
 * Projet ECF Vite & Gourmand
 */

require_once __DIR__ . '/BaseRepository.php';

class AvisRepository extends BaseRepository {

    /**
     * Récupère les derniers avis validés pour affichage public
     * @param int $limit
     * @return array
     */
    public function findValides(int $limit = 3): array {
        $stmt = $this->pdo->prepare("
            SELECT a.avis_id, a.note, a.commentaire, a.date_creation, 
                   u.prenom, u.nom
            FROM avis a
            JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
            WHERE a.statut = 'VALIDE'
            ORDER BY a.date_creation DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère tous les avis avec filtrage optionnel par statut (pour modération employé/admin)
     * @param string|null $statut 'EN_ATTENTE', 'VALIDE', 'REFUSE' ou null pour tous
     * @return array
     */
    public function findAll(?string $statut = null): array {
        $sql = "
            SELECT a.avis_id, a.note, a.commentaire, a.statut, a.date_creation, 
                   u.prenom, u.nom, u.email
            FROM avis a
            JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
        ";
        $params = [];

        if ($statut !== null) {
            $sql .= " WHERE a.statut = :statut";
            $params[':statut'] = $statut;
        }

        $sql .= " ORDER BY a.date_creation DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Enregistre un nouvel avis avec le statut initial 'EN_ATTENTE' (règle ECF)
     * 
     * @param int $utilisateurId
     * @param int $note
     * @param string $commentaire
     * @param int|null $commandeId
     * @return bool
     */
    public function create(int $utilisateurId, int $note, string $commentaire, ?int $commandeId = null): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO avis (note, commentaire, statut, utilisateur_id, commande_id)
            VALUES (:note, :commentaire, 'EN_ATTENTE', :utilisateur_id, :commande_id)
        ");

        return $stmt->execute([
            ':note'           => $note,
            ':commentaire'    => trim($commentaire),
            ':utilisateur_id' => $utilisateurId,
            ':commande_id'    => $commandeId,
        ]);
    }

    /**
     * Met à jour le statut d'un avis (modération par employé/admin)
     * 
     * @param int $avisId
     * @param string $statut 'VALIDE' ou 'REFUSE'
     * @return bool
     */
    public function updateStatut(int $avisId, string $statut): bool {
        if (!in_array($statut, ['EN_ATTENTE', 'VALIDE', 'REFUSE'], true)) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            UPDATE avis 
            SET statut = :statut 
            WHERE avis_id = :id
        ");

        return $stmt->execute([
            ':statut' => $statut,
            ':id'     => $avisId,
        ]);
    }
}
