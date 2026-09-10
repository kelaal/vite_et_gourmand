-- ==========================================================
-- Fixtures & Jeu d'essai : vite_et_gourmand
-- Projet ECF DWWM : Vite & Gourmand
-- Mots de passe par défaut pour les tests :
--   - Admin (José)     : Admin1234!
--   - Employés (Julie) : Employe1234!
--   - Clients (Sophie) : Client1234!
-- ==========================================================

USE `vite_et_gourmand`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `commande_historique_statut`;
TRUNCATE TABLE `avis`;
TRUNCATE TABLE `commande`;
TRUNCATE TABLE `menu_image`;
TRUNCATE TABLE `menu_plat`;
TRUNCATE TABLE `plat_allergene`;
TRUNCATE TABLE `menu`;
TRUNCATE TABLE `plat`;
TRUNCATE TABLE `allergene`;
TRUNCATE TABLE `regime`;
TRUNCATE TABLE `theme`;
TRUNCATE TABLE `horaire`;
TRUNCATE TABLE `utilisateur`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- 1. Utilisateurs (Admin, Employés, Clients)
-- ----------------------------------------------------------
INSERT INTO `utilisateur` (`utilisateur_id`, `nom`, `prenom`, `email`, `password`, `gsm`, `adresse_postale`, `role`, `is_active`, `date_creation`) VALUES
(1, 'Administrateur', 'José', 'jose@viteetgourmand.fr', '$2y$10$Lj81bguq2m3JsJGRO1eCp.Iu.a/IWw0erjSp.QPIIeUDG7XosGXem', '0655001122', '12 cours de l''Intendance, 33000 Bordeaux', 'administrateur', 1, '2026-01-01 08:00:00'),
(2, 'Chef', 'Julie', 'julie@viteetgourmand.fr', '$2y$10$3ZmVJeEEZllx3ttPRbWdLOPkxoWYdgwu5ybhwYxltQw28TVCWGagW', '0655003344', '45 rue Sainte-Catherine, 33000 Bordeaux', 'employe', 1, '2026-01-02 09:00:00'),
(3, 'Dubois', 'Thomas', 'thomas@viteetgourmand.fr', '$2y$10$3ZmVJeEEZllx3ttPRbWdLOPkxoWYdgwu5ybhwYxltQw28TVCWGagW', '0655005566', '8 quai des Chartrons, 33000 Bordeaux', 'employe', 1, '2026-01-15 10:30:00'),
(4, 'Martin', 'Sophie', 'sophie.martin@gmail.com', '$2y$10$BTARnTDxdC33i4XrW8QYY.HRUuWUM0diSj50RBeFP2YocWcC7jODS', '0612345678', '14 rue Judaïque, 33000 Bordeaux', 'utilisateur', 1, '2026-02-01 14:20:00'),
(5, 'Bernard', 'Lucas', 'lucas.bernard@yahoo.fr', '$2y$10$BTARnTDxdC33i4XrW8QYY.HRUuWUM0diSj50RBeFP2YocWcC7jODS', '0698765432', '27 avenue Thiers, 33100 Bordeaux', 'utilisateur', 1, '2026-02-10 11:15:00'),
(6, 'Dupont', 'Claire', 'claire.dupont@orange.fr', '$2y$10$BTARnTDxdC33i4XrW8QYY.HRUuWUM0diSj50RBeFP2YocWcC7jODS', '0644332211', '5 allée des Pins, 33700 Mérignac', 'utilisateur', 1, '2026-02-18 16:45:00');

-- ----------------------------------------------------------
-- 2. Thèmes
-- ----------------------------------------------------------
INSERT INTO `theme` (`theme_id`, `libelle`) VALUES
(1, 'Classique'),
(2, 'Noël'),
(3, 'Pâques'),
(4, 'Événement d''entreprise'),
(5, 'Mariage & Réception');

-- ----------------------------------------------------------
-- 3. Régimes
-- ----------------------------------------------------------
INSERT INTO `regime` (`regime_id`, `libelle`) VALUES
(1, 'Classique'),
(2, 'Végétarien'),
(3, 'Vegan'),
(4, 'Sans gluten');

-- ----------------------------------------------------------
-- 4. Allergènes
-- ----------------------------------------------------------
INSERT INTO `allergene` (`allergene_id`, `libelle`) VALUES
(1, 'Gluten'),
(2, 'Crustacés'),
(3, 'Œufs'),
(4, 'Poissons'),
(5, 'Arachides'),
(6, 'Soja'),
(7, 'Lait / Lactose'),
(8, 'Fruits à coque'),
(9, 'Céleri'),
(10, 'Moutarde'),
(11, 'Graines de sésame'),
(12, 'Sulfites'),
(13, 'Lupin'),
(14, 'Mollusques');

-- ----------------------------------------------------------
-- 5. Plats
-- ----------------------------------------------------------
INSERT INTO `plat` (`plat_id`, `titre`, `description`, `type_plat`) VALUES
(1, 'Terrine de foie gras du Sud-Ouest maison', 'Accompagnée de son chutney de figues et pain brioché toasté', 'entree'),
(2, 'Carpaccio de Saint-Jacques aux agrumes', 'Huile d''olive citronnée, baies roses et jeunes pousses', 'entree'),
(3, 'Velouté de potimarron rôti et éclats de châtaignes', 'Une entrée douce et veloutée relevée d''une pointe de muscade', 'entree'),
(4, 'Tartare de saumon d''Écosse et avocat', 'Assaisonné au yuzu et graines de sésame grillées', 'entree'),
(5, 'Salade croquante de quinoa et légumes anciens', 'Vinaigrette passion et noisettes torréfiées', 'entree'),
(6, 'Magret de canard du Sud-Ouest rôti au miel et épices', 'Servi avec une écrasée de pommes de terre à la truffe', 'plat'),
(7, 'Pavé de cabillaud sauvage en croûte d''herbes', 'Accompagné d''un risotto crémeux au parmesan et asperges vertes', 'plat'),
(8, 'Risotto aux cèpes de Bordeaux et truffe d''été', 'Cuisiné au vin blanc de Pessac et parmesan affiné 24 mois', 'plat'),
(9, 'Suprême de volaille fermière aux morilles', 'Jus corsé et mousseline de carottes au cumin', 'plat'),
(10, 'Curry de légumes de saison et pois chiches au lait de coco', 'Riz basmati parfumé à la cardamome', 'plat'),
(11, 'Canelé bordelais revisité en trompe-l''œil', 'Cœur coulant vanille de Madagascar et croustillant caramel', 'dessert'),
(12, 'Dôme au chocolat grand cru Guanaja et coulis framboise', 'Sablé cacao fleur de sel et mousse onctueuse', 'dessert'),
(13, 'Tartelette fine aux poires caramélisées et amandes', 'Crème d''amande douce et glace artisanale vanille', 'dessert'),
(14, 'Carpaccio d''ananas rôti aux épices douces et sorbet mangue', 'Dessert 100% végétal et rafraîchissant', 'dessert');

-- ----------------------------------------------------------
-- 6. Liaisons Plat - Allergène
-- ----------------------------------------------------------
INSERT INTO `plat_allergene` (`plat_id`, `allergene_id`) VALUES
(1, 1), (1, 3), (1, 7), (1, 12),
(2, 4), (2, 14),
(3, 7),
(4, 4), (4, 11),
(5, 8),
(6, 7), (6, 12),
(7, 1), (7, 4), (7, 7),
(8, 7), (8, 12),
(9, 7), (9, 9),
(11, 1), (11, 3), (11, 7),
(12, 1), (12, 3), (12, 7), (12, 8),
(13, 1), (13, 3), (13, 7), (13, 8);

-- ----------------------------------------------------------
-- 7. Menus
-- ----------------------------------------------------------
INSERT INTO `menu` (`menu_id`, `titre`, `description`, `nb_personne_min`, `prix_base_min`, `stock_disponible`, `conditions_delai_stockage`, `theme_id`, `regime_id`, `image_url`, `is_active`, `date_creation`) VALUES
(1, 'Menu Terroir Bordelais', 'Une immersion gourmande au cœur des saveurs aquitaines, alliant canard fermier et cèpes de Bordeaux.', 4, 38.00, 15, 'Commande minimum 48h à l''avance. Conserver entre 2°C et 4°C jusqu''au service.', 1, 1, 'public/img/presentation-photo.webp', 1, '2026-01-10 10:00:00'),
(2, 'Menu Festin de Noël Gastronomique', 'L''excellence d''un repas de fête avec foie gras d''exception et Saint-Jacques poêlées.', 6, 65.00, 8, 'Commande obligatoire 5 jours avant l''événement. Chaîne du froid impérative (2°C-4°C). Matériel de maintien au chaud fourni.', 2, 1, 'public/img/hero-bg.webp', 1, '2026-01-12 11:30:00'),
(3, 'Menu Douceur Végétale', 'Création 100% végétarienne raffinée, mettant à l''honneur les cèpes, les légumes anciens et les agrumes.', 2, 34.00, 20, 'Commande 24h à l''avance. Prêt à déguster ou à réchauffer 10 min à 160°C.', 4, 2, 'public/img/expertise.webp', 1, '2026-01-15 09:00:00'),
(4, 'Menu Délices Pascales', 'Un hommage printanier à la tradition de Pâques avec une volaille noble et des saveurs printanières.', 5, 48.00, 10, 'Commande 72h à l''avance. Conservation au réfrigérateur. Instructions de réchauffe fournies.', 3, 1, 'public/img/presentation-photo.webp', 1, '2026-01-18 14:00:00'),
(5, 'Menu Éclat Éco-Vegan', 'Une expérience gastronomique 100% végétale et sans gluten, pleine de textures et d''épices voyageuses.', 4, 32.00, 12, 'Commande 48h à l''avance. Conserver au frais (max 4°C).', 1, 3, 'public/img/expertise.webp', 1, '2026-01-20 16:00:00');

-- ----------------------------------------------------------
-- 8. Liaisons Menu - Plat
-- ----------------------------------------------------------
INSERT INTO `menu_plat` (`menu_id`, `plat_id`) VALUES
(1, 1), (1, 6), (1, 11),
(2, 1), (2, 2), (2, 6), (2, 12),
(3, 3), (3, 8), (3, 13),
(4, 2), (4, 9), (4, 11),
(5, 5), (5, 10), (5, 14);

-- ----------------------------------------------------------
-- 9. Galerie d'images des Menus
-- ----------------------------------------------------------
INSERT INTO `menu_image` (`image_id`, `menu_id`, `image_url`, `alt_text`) VALUES
(1, 1, 'public/img/presentation-photo.webp', 'Présentation du Menu Terroir'),
(2, 1, 'public/img/hero-bg.webp', 'Plat principal canard et cèpes'),
(3, 2, 'public/img/hero-bg.webp', 'Table festive Menu de Noël'),
(4, 2, 'public/img/expertise.webp', 'Entrée Foie Gras et Saint Jacques'),
(5, 3, 'public/img/expertise.webp', 'Risotto crémeux végétarien'),
(6, 4, 'public/img/presentation-photo.webp', 'Menu de Pâques printanier'),
(7, 5, 'public/img/expertise.webp', 'Plat vegan coloré et savoureux');

-- ----------------------------------------------------------
-- 10. Commandes exemples
-- ----------------------------------------------------------
INSERT INTO `commande` (`commande_id`, `numero_commande`, `date_commande`, `date_prestation`, `heure_prestation`, `adresse_prestation`, `ville_prestation`, `distance_km`, `nb_personnes`, `prix_menu_unitaire`, `remise_appliquee`, `frais_livraison`, `prix_total`, `statut`, `pret_materiel`, `restitution_materiel`, `utilisateur_id`, `menu_id`) VALUES
(1, 'CMD-2026-0001', '2026-02-15 10:30:00', '2026-02-25', '12:30:00', '14 rue Judaïque', 'Bordeaux', 0.00, 4, 38.00, 0.00, 0.00, 152.00, 'terminee', 0, 0, 4, 1),
(2, 'CMD-2026-02-0002', '2026-02-20 14:00:00', '2026-03-05', '19:30:00', '5 allée des Pins', 'Mérignac', 8.50, 10, 38.00, 38.00, 10.02, 352.02, 'en attente du retour de materiel', 1, 0, 6, 1),
(3, 'CMD-2026-03-0003', '2026-03-01 09:15:00', '2026-03-15', '20:00:00', '27 avenue Thiers', 'Bordeaux', 0.00, 6, 65.00, 0.00, 0.00, 390.00, 'accepte', 0, 0, 5, 2);

-- ----------------------------------------------------------
-- 11. Historique de statut des commandes
-- ----------------------------------------------------------
INSERT INTO `commande_historique_statut` (`historique_id`, `commande_id`, `statut`, `date_changement`, `modifie_par_id`) VALUES
(1, 1, 'en attente', '2026-02-15 10:30:00', 4),
(2, 1, 'accepte', '2026-02-16 09:00:00', 2),
(3, 1, 'en preparation', '2026-02-24 16:00:00', 2),
(4, 1, 'en cours de livraison', '2026-02-25 11:30:00', 3),
(5, 1, 'livre', '2026-02-25 12:25:00', 3),
(6, 1, 'terminee', '2026-02-25 14:00:00', 2),
(7, 2, 'en attente', '2026-02-20 14:00:00', 6),
(8, 2, 'accepte', '2026-02-21 10:00:00', 2),
(9, 2, 'en preparation', '2026-03-04 15:00:00', 2),
(10, 2, 'en cours de livraison', '2026-03-05 18:30:00', 3),
(11, 2, 'livre', '2026-03-05 19:25:00', 3),
(12, 2, 'en attente du retour de materiel', '2026-03-05 20:00:00', 2),
(13, 3, 'en attente', '2026-03-01 09:15:00', 5),
(14, 3, 'accepte', '2026-03-01 14:00:00', 2);

-- ----------------------------------------------------------
-- 12. Avis Clients
-- ----------------------------------------------------------
INSERT INTO `avis` (`avis_id`, `note`, `commentaire`, `statut`, `date_creation`, `utilisateur_id`, `commande_id`) VALUES
(1, 5, 'Un service exceptionnel pour notre anniversaire de mariage ! Le canard était cuit à la perfection et le canelé revisité a bluffé tous nos convives.', 'VALIDE', '2026-02-26 10:00:00', 4, 1),
(2, 5, 'Prestation traiteur d''une grande qualité. Julie et José sont à l''écoute, ponctuels et passionnés. Nous recommandons les yeux fermés !', 'VALIDE', '2026-02-28 15:30:00', 5, NULL),
(3, 4, 'Très bonne expérience, produits frais et belle présentation. Livraison dans les temps à Bordeaux.', 'VALIDE', '2026-03-02 18:45:00', 6, NULL),
(4, 5, 'Repas végétarien savoureux et original, nos collègues ont adoré.', 'EN_ATTENTE', '2026-03-06 11:20:00', 4, NULL),
(5, 1, 'Commentaire test inapproprié pour modération.', 'REFUSE', '2026-03-07 09:00:00', 5, NULL);

-- ----------------------------------------------------------
-- 13. Horaires d'ouverture (Lundi au Dimanche)
-- ----------------------------------------------------------
INSERT INTO `horaire` (`horaire_id`, `jour_semaine`, `heure_ouverture`, `heure_fermeture`, `est_ouvert`, `ordre_jour`) VALUES
(1, 'Lundi', '08:00:00', '20:00:00', 1, 1),
(2, 'Mardi', '08:00:00', '20:00:00', 1, 2),
(3, 'Mercredi', '08:00:00', '20:00:00', 1, 3),
(4, 'Jeudi', '08:00:00', '20:00:00', 1, 4),
(5, 'Vendredi', '08:00:00', '20:00:00', 1, 5),
(6, 'Samedi', '08:00:00', '20:00:00', 1, 6),
(7, 'Dimanche', NULL, NULL, 0, 7);
