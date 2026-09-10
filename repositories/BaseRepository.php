<?php
/**
 * Classe de base abstraite pour les Repositories (Accès aux données)
 * Projet ECF Vite & Gourmand
 */

abstract class BaseRepository {
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            if ($pdo instanceof PDO) {
                $this->pdo = $pdo;
            } else {
                require_once __DIR__ . '/../config/database.php';
                $this->pdo = $pdo;
            }
        }
    }

    /**
     * Retourne l'instance PDO courante
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }
}
