<?php
/**
 * Repository d'accès aux données des Utilisateurs (MySQL)
 * Projet ECF Vite & Gourmand
 */

require_once __DIR__ . '/BaseRepository.php';

class UtilisateurRepository extends BaseRepository {

    /**
     * Recherche un utilisateur par son adresse email (identifiant)
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM utilisateur 
            WHERE email = :email
        ");
        $stmt->execute([':email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Recherche un utilisateur par son ID
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT utilisateur_id, nom, prenom, email, gsm, adresse_postale, role, is_active, date_creation
            FROM utilisateur 
            WHERE utilisateur_id = :id
        ");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Crée un nouvel utilisateur (client ou employé)
     * @param array $data
     * @return int ID de l'utilisateur créé
     */
    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO utilisateur (nom, prenom, email, password, gsm, adresse_postale, role, is_active)
            VALUES (:nom, :prenom, :email, :password, :gsm, :adresse_postale, :role, :is_active)
        ");

        $stmt->execute([
            ':nom'             => $data['nom'] ?? '',
            ':prenom'          => $data['prenom'] ?? '',
            ':email'           => strtolower(trim($data['email'] ?? '')),
            ':password'        => $data['password'],
            ':gsm'             => $data['gsm'] ?? '',
            ':adresse_postale' => $data['adresse_postale'] ?? '',
            ':role'            => $data['role'] ?? 'utilisateur',
            ':is_active'       => $data['is_active'] ?? 1,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour les informations personnelles d'un utilisateur
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProfil(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur 
            SET nom = :nom, prenom = :prenom, gsm = :gsm, adresse_postale = :adresse_postale
            WHERE utilisateur_id = :id
        ");

        return $stmt->execute([
            ':nom'             => $data['nom'],
            ':prenom'          => $data['prenom'],
            ':gsm'             => $data['gsm'],
            ':adresse_postale' => $data['adresse_postale'],
            ':id'              => $id,
        ]);
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     * @param int $id
     * @param string $hashedPassword
     * @return bool
     */
    public function updatePassword(int $id, string $hashedPassword): bool {
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur 
            SET password = :password, reset_token = NULL, reset_token_expires_at = NULL
            WHERE utilisateur_id = :id
        ");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $id,
        ]);
    }

    /**
     * Active ou désactive un compte employé (fonctionnalité Admin ECF)
     * @param int $id
     * @param bool $isActive
     * @return bool
     */
    public function toggleActive(int $id, bool $isActive): bool {
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur 
            SET is_active = :active 
            WHERE utilisateur_id = :id
        ");
        return $stmt->execute([
            ':active' => $isActive ? 1 : 0,
            ':id'     => $id,
        ]);
    }

    /**
     * Récupère la liste des comptes employés (pour l'espace Admin)
     * @return array
     */
    public function findAllEmployes(): array {
        $stmt = $this->pdo->query("
            SELECT utilisateur_id, nom, prenom, email, gsm, role, is_active, date_creation
            FROM utilisateur 
            WHERE role = 'employe'
            ORDER BY date_creation DESC
        ");
        return $stmt->fetchAll();
    }
}
