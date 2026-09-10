-- ==========================================================
-- Base de données : vite_et_gourmand
-- Projet ECF DWWM : Vite & Gourmand
-- SGBD : MySQL (InnoDB, utf8mb4)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `vite_et_gourmand` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `vite_et_gourmand`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `commande_historique_statut`;
DROP TABLE IF EXISTS `avis`;
DROP TABLE IF EXISTS `commande`;
DROP TABLE IF EXISTS `menu_image`;
DROP TABLE IF EXISTS `menu_plat`;
DROP TABLE IF EXISTS `plat_allergene`;
DROP TABLE IF EXISTS `menu`;
DROP TABLE IF EXISTS `plat`;
DROP TABLE IF EXISTS `allergene`;
DROP TABLE IF EXISTS `regime`;
DROP TABLE IF EXISTS `theme`;
DROP TABLE IF EXISTS `horaire`;
DROP TABLE IF EXISTS `utilisateur`;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- 1. Table `utilisateur`
-- Rôles possibles : 'utilisateur', 'employe', 'administrateur'
-- ----------------------------------------------------------
CREATE TABLE `utilisateur` (
    `utilisateur_id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `gsm` VARCHAR(20) NOT NULL,
    `adresse_postale` VARCHAR(255) NOT NULL,
    `role` ENUM('utilisateur', 'employe', 'administrateur') NOT NULL DEFAULT 'utilisateur',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `reset_token` VARCHAR(255) NULL,
    `reset_token_expires_at` DATETIME NULL,
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Table `theme`
-- Thèmes des menus (ex: Noël, Pâques, Classique, Événement)
-- ----------------------------------------------------------
CREATE TABLE `theme` (
    `theme_id` INT AUTO_INCREMENT PRIMARY KEY,
    `libelle` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Table `regime`
-- Régimes alimentaires (ex: Végétarien, Vegan, Classique, Sans gluten)
-- ----------------------------------------------------------
CREATE TABLE `regime` (
    `regime_id` INT AUTO_INCREMENT PRIMARY KEY,
    `libelle` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Table `allergene`
-- Liste des allergènes réglementaires
-- ----------------------------------------------------------
CREATE TABLE `allergene` (
    `allergene_id` INT AUTO_INCREMENT PRIMARY KEY,
    `libelle` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Table `plat`
-- Plats composant les menus (Entrée, Plat, Dessert)
-- ----------------------------------------------------------
CREATE TABLE `plat` (
    `plat_id` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `type_plat` ENUM('entree', 'plat', 'dessert') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Table de liaison `plat_allergene`
-- ----------------------------------------------------------
CREATE TABLE `plat_allergene` (
    `plat_id` INT NOT NULL,
    `allergene_id` INT NOT NULL,
    PRIMARY KEY (`plat_id`, `allergene_id`),
    CONSTRAINT `fk_plat_allergene_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_plat_allergene_allergene` FOREIGN KEY (`allergene_id`) REFERENCES `allergene` (`allergene_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Table `menu`
-- Menus proposés par le traiteur
-- ----------------------------------------------------------
CREATE TABLE `menu` (
    `menu_id` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `nb_personne_min` INT NOT NULL DEFAULT 1,
    `prix_base_min` DECIMAL(10, 2) NOT NULL,
    `stock_disponible` INT NOT NULL DEFAULT 0,
    `conditions_delai_stockage` TEXT NOT NULL,
    `theme_id` INT NOT NULL,
    `regime_id` INT NOT NULL,
    `image_url` VARCHAR(255) NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_menu_theme` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`theme_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_menu_regime` FOREIGN KEY (`regime_id`) REFERENCES `regime` (`regime_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 8. Table de liaison `menu_plat`
-- Association N-N (un plat peut appartenir à plusieurs menus)
-- ----------------------------------------------------------
CREATE TABLE `menu_plat` (
    `menu_id` INT NOT NULL,
    `plat_id` INT NOT NULL,
    PRIMARY KEY (`menu_id`, `plat_id`),
    CONSTRAINT `fk_menu_plat_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_menu_plat_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 9. Table `menu_image`
-- Galerie d'images associées au menu
-- ----------------------------------------------------------
CREATE TABLE `menu_image` (
    `image_id` INT AUTO_INCREMENT PRIMARY KEY,
    `menu_id` INT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(255) NULL,
    CONSTRAINT `fk_menu_image_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 10. Table `commande`
-- Commandes clients avec calculs et statuts métier
-- ----------------------------------------------------------
CREATE TABLE `commande` (
    `commande_id` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_commande` VARCHAR(50) NOT NULL UNIQUE,
    `date_commande` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_prestation` DATE NOT NULL,
    `heure_prestation` TIME NOT NULL,
    `adresse_prestation` VARCHAR(255) NOT NULL,
    `ville_prestation` VARCHAR(100) NOT NULL,
    `distance_km` DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
    `nb_personnes` INT NOT NULL,
    `prix_menu_unitaire` DECIMAL(10, 2) NOT NULL,
    `remise_appliquee` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `frais_livraison` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `prix_total` DECIMAL(10, 2) NOT NULL,
    `statut` ENUM(
        'en attente',
        'accepte',
        'en preparation',
        'en cours de livraison',
        'livre',
        'en attente du retour de materiel',
        'terminee',
        'annulee'
    ) NOT NULL DEFAULT 'en attente',
    `pret_materiel` BOOLEAN NOT NULL DEFAULT FALSE,
    `restitution_materiel` BOOLEAN NOT NULL DEFAULT FALSE,
    `motif_annulation` TEXT NULL,
    `contact_mode_annulation` ENUM('gsm', 'mail') NULL,
    `utilisateur_id` INT NOT NULL,
    `menu_id` INT NOT NULL,
    CONSTRAINT `fk_commande_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_commande_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 11. Table `commande_historique_statut`
-- Traçabilité & suivi du cycle de vie de la commande
-- ----------------------------------------------------------
CREATE TABLE `commande_historique_statut` (
    `historique_id` INT AUTO_INCREMENT PRIMARY KEY,
    `commande_id` INT NOT NULL,
    `statut` VARCHAR(50) NOT NULL,
    `date_changement` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modifie_par_id` INT NULL,
    CONSTRAINT `fk_hist_commande` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`commande_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_hist_utilisateur` FOREIGN KEY (`modifie_par_id`) REFERENCES `utilisateur` (`utilisateur_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 12. Table `avis`
-- Gestion des avis avec modération
-- ----------------------------------------------------------
CREATE TABLE `avis` (
    `avis_id` INT AUTO_INCREMENT PRIMARY KEY,
    `note` TINYINT UNSIGNED NOT NULL CHECK (`note` BETWEEN 1 AND 5),
    `commentaire` TEXT NOT NULL,
    `statut` ENUM('EN_ATTENTE', 'VALIDE', 'REFUSE') NOT NULL DEFAULT 'EN_ATTENTE',
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `utilisateur_id` INT NOT NULL,
    `commande_id` INT NULL,
    CONSTRAINT `fk_avis_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_avis_commande` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`commande_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 13. Table `horaire`
-- Horaires d'ouverture configurables du Lundi au Dimanche
-- ----------------------------------------------------------
CREATE TABLE `horaire` (
    `horaire_id` INT AUTO_INCREMENT PRIMARY KEY,
    `jour_semaine` VARCHAR(20) NOT NULL,
    `heure_ouverture` TIME NULL,
    `heure_fermeture` TIME NULL,
    `est_ouvert` BOOLEAN NOT NULL DEFAULT TRUE,
    `ordre_jour` TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
