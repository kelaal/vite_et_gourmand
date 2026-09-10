<?php
/**
 * Repository d'accès aux données des Commandes (MySQL + Synchronisation MongoDB)
 * Projet ECF Vite & Gourmand
 */

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../config/mongo.php';

class CommandeRepository extends BaseRepository {

    /**
     * Crée une commande avec calculs, statut initial, traçabilité et synchronisation NoSQL
     * 
     * @param array $data Données de la commande
     * @return int ID de la commande créée
     * @throws Exception
     */
    public function create(array $data): int {
        // Génération d'un numéro de commande unique ex: CMD-2026-XXXX
        $numeroCommande = 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO commande (
                    numero_commande, date_prestation, heure_prestation,
                    adresse_prestation, ville_prestation, distance_km,
                    nb_personnes, prix_menu_unitaire, remise_appliquee,
                    frais_livraison, prix_total, statut, pret_materiel,
                    utilisateur_id, menu_id
                ) VALUES (
                    :numero_commande, :date_prestation, :heure_prestation,
                    :adresse_prestation, :ville_prestation, :distance_km,
                    :nb_personnes, :prix_menu_unitaire, :remise_appliquee,
                    :frais_livraison, :prix_total, 'en attente', :pret_materiel,
                    :utilisateur_id, :menu_id
                )
            ");

            $stmt->execute([
                ':numero_commande'    => $numeroCommande,
                ':date_prestation'    => $data['date_prestation'],
                ':heure_prestation'   => $data['heure_prestation'],
                ':adresse_prestation' => $data['adresse_prestation'],
                ':ville_prestation'   => $data['ville_prestation'],
                ':distance_km'        => $data['distance_km'] ?? 0.00,
                ':nb_personnes'       => (int)$data['nb_personnes'],
                ':prix_menu_unitaire' => (float)$data['prix_menu_unitaire'],
                ':remise_appliquee'   => (float)($data['remise_appliquee'] ?? 0.00),
                ':frais_livraison'    => (float)($data['frais_livraison'] ?? 0.00),
                ':prix_total'         => (float)$data['prix_total'],
                ':pret_materiel'      => !empty($data['pret_materiel']) ? 1 : 0,
                ':utilisateur_id'     => (int)$data['utilisateur_id'],
                ':menu_id'            => (int)$data['menu_id'],
            ]);

            $commandeId = (int)$this->pdo->lastInsertId();

            // Enregistrement de l'historique initial
            $stmtHist = $this->pdo->prepare("
                INSERT INTO commande_historique_statut (commande_id, statut, modifie_par_id)
                VALUES (:commande_id, 'en attente', :user_id)
            ");
            $stmtHist->execute([
                ':commande_id' => $commandeId,
                ':user_id'     => $data['utilisateur_id'],
            ]);

            $this->pdo->commit();

            // DOUBLE-ÉCRITURE ECF : Synchronisation NoSQL dans MongoDB
            $data['numero_commande'] = $numeroCommande;
            $data['statut']          = 'en attente';
            syncCommandeStatsToMongo($data);

            return $commandeId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Récupère les commandes d'un utilisateur donné
     * @param int $userId
     * @return array
     */
    public function findByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT c.*, m.titre AS menu_titre, m.image_url AS menu_image
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            WHERE c.utilisateur_id = :user_id
            ORDER BY c.date_commande DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère une commande par son ID avec détails utilisateur et menu
     * @param int $commandeId
     * @return array|null
     */
    public function findById(int $commandeId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT c.*, 
                   m.titre AS menu_titre, m.description AS menu_description, m.image_url AS menu_image,
                   u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email, u.gsm AS client_gsm
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            WHERE c.commande_id = :id
        ");
        $stmt->execute([':id' => $commandeId]);
        $cmd = $stmt->fetch();
        return $cmd ?: null;
    }

    /**
     * Récupère toutes les commandes avec filtrage statut/client (espace Employé/Admin)
     * @param string|null $statut
     * @param int|null $userId
     * @return array
     */
    public function findAllFiltered(?string $statut = null, ?int $userId = null): array {
        $sql = "
            SELECT c.*, 
                   m.titre AS menu_titre,
                   u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email, u.gsm AS client_gsm
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($statut)) {
            $sql .= " AND c.statut = :statut";
            $params[':statut'] = $statut;
        }

        if (!empty($userId)) {
            $sql .= " AND c.utilisateur_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $sql .= " ORDER BY c.date_commande DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Met à jour le statut d'une commande avec ajout à l'historique chronologique
     * 
     * @param int $commandeId
     * @param string $nouveauStatut
     * @param int|null $modifieParId ID de l'utilisateur ayant fait l'action
     * @return bool
     */
    public function updateStatut(int $commandeId, string $nouveauStatut, ?int $modifieParId = null): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE commande 
                SET statut = :statut 
                WHERE commande_id = :id
            ");
            $stmt->execute([
                ':statut' => $nouveauStatut,
                ':id'     => $commandeId,
            ]);

            // Ajout à l'historique
            $stmtHist = $this->pdo->prepare("
                INSERT INTO commande_historique_statut (commande_id, statut, modifie_par_id)
                VALUES (:commande_id, :statut, :modifie_par)
            ");
            $stmtHist->execute([
                ':commande_id' => $commandeId,
                ':statut'      => $nouveauStatut,
                ':modifie_par' => $modifieParId,
            ]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Annulation par un employé avec motif et mode de contact obligatoire (Règle ECF)
     * 
     * @param int $commandeId
     * @param string $motif
     * @param string $contactMode 'gsm' ou 'mail'
     * @param int $employeId
     * @return bool
     */
    public function annulerParEmploye(int $commandeId, string $motif, string $contactMode, int $employeId): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE commande 
                SET statut = 'annulee', motif_annulation = :motif, contact_mode_annulation = :mode
                WHERE commande_id = :id
            ");
            $stmt->execute([
                ':motif' => trim($motif),
                ':mode'  => $contactMode,
                ':id'    => $commandeId,
            ]);

            $stmtHist = $this->pdo->prepare("
                INSERT INTO commande_historique_statut (commande_id, statut, modifie_par_id)
                VALUES (:id, 'annulee', :employe_id)
            ");
            $stmtHist->execute([':id' => $commandeId, ':employe_id' => $employeId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Récupère l'historique chronologique de statut d'une commande
     * @param int $commandeId
     * @return array
     */
    public function getHistoriqueStatut(int $commandeId): array {
        $stmt = $this->pdo->prepare("
            SELECT h.statut, h.date_changement, u.prenom AS auteur_prenom, u.role AS auteur_role
            FROM commande_historique_statut h
            LEFT JOIN utilisateur u ON h.modifie_par_id = u.utilisateur_id
            WHERE h.commande_id = :id
            ORDER BY h.date_changement ASC
        ");
        $stmt->execute([':id' => $commandeId]);
        return $stmt->fetchAll();
    }
}
