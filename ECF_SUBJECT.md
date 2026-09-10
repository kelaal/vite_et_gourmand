# Cahier des Charges ECF – Vite & Gourmand

**Titre Professionnel :** Développeur Web et Web Mobile (DWWM)  
**Projet :** Vite & Gourmand  
**Entreprise cliente :** Julie & José (Bordeaux, traiteur événementiel depuis 25 ans)  
**Agence de développement :** FastDev

---

## 1. Contexte & Objectifs du Projet

Julie et José gèrent « Vite & Gourmand », un service traiteur bordelais réputé proposant des menus événementiels (Noël, Pâques, mariages, entreprises, etc.).
L'objectif est de concevoir et développer une application web complète (Front-end & Back-end sécurisés, base de données relationnelle MySQL et NoSQL MongoDB) permettant :
- De présenter l'entreprise, son savoir-faire, ses avis clients et ses horaires.
- De consulter les menus avec filtrage dynamique et vue détaillée.
- De commander en ligne avec calcul des frais kilométriques et gestion des réductions.
- De gérer les espaces Utilisateur, Employé et Administrateur.
- De modérer les avis et analyser les statistiques de ventes (MongoDB).

---

## 2. Spécifications Fonctionnelles Détaillées

### 2.1. Navigation, Header & Pied de Page
- **Menu de navigation :** Accueil, Tous les menus, Contact, Connexion / Espace connecté (selon le rôle).
- **Pied de page :**
  - Horaires d'ouverture visibles du **lundi au dimanche** (dynamiques / configurables par l'équipe).
  - Liens vers **Mentions Légales** et **Conditions Générales de Vente (CGV)**.

### 2.2. Page d'Accueil (`index.php`)
- Présentation de l'entreprise (Julie & José, 25 ans d'expérience).
- Mise en valeur du professionnalisme et du savoir-faire (qualité, réactivité, sur-mesure).
- Affichage des **avis clients validés** uniquement.

### 2.3. Gestion des Menus & Vue Globale
- **Vue globale des menus :**
  - Carte de chaque menu : titre, description courte, nombre de personnes minimum, prix associé, bouton vers la vue détaillée.
  - Accessible aux visiteurs et aux utilisateurs connectés.
- **Filtres dynamiques (sans rechargement de page en JS / AJAX) :**
  - Prix maximum
  - Fourchette de prix (min - max)
  - Thème (Noël, Pâques, classique, événement...)
  - Régime (végétarien, vegan, classique...)
  - Nombre de personnes minimum
- **Vue détaillée d'un menu :**
  - Galerie d'images.
  - Description complète, thème, régime.
  - Composition des plats (entrées, plats, desserts).
  - Liste des allergènes par plat.
  - Conditions spécifiques du menu (délai de commande x jours/semaines à l'avance, conservation/stockage) mises bien en évidence.
  - Stock disponible (ex: *5 commandes restantes*).
  - Prix pour le nombre minimum de personnes.
  - Bouton **« Commander »** (redirige vers le formulaire de commande avec le menu pré-sélectionné ; si non connecté, invite à se connecter/créer un compte).

### 2.4. Authentification & Sécurité Utilisateurs
- **Création de compte (Visiteur -> Utilisateur) :**
  - Champs obligatoires : Nom, Prénom, Numéro GSM, Adresse postale et email.
  - **Mot de passe sécurisé :** minimum 10 caractères avec au moins 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.
  - Attribution automatique du rôle `'utilisateur'`.
  - Envoi automatique d'un **email de bienvenue**.
- **Connexion :** Email (username) + mot de passe.
- **Mot de passe oublié :** Formulaire de saisie d'email pour l'envoi d'un lien de réinitialisation sécurisé par mail.
- **Conformité & Sécurité :** RGPD, hachage des mots de passe (`password_hash` BCRYPT / ARGON2ID), protection contre les failles XSS, CSRF, et injections SQL (requêtes préparées PDO).

### 2.5. Prise de Commande & Facturation
- **Formulaire de commande :**
  - Informations client auto-remplies (Nom, Prénom, Email, GSM, Adresse).
  - Choix de la date et de l'heure souhaitées de livraison.
  - Lieu / adresse de livraison.
  - Sélection du menu (pré-sélectionné si clic depuis la fiche menu).
  - Choix du nombre de personnes ($\ge$ minimum requis par le menu).
- **Règles de tarification & réductions :**
  - **Frais de livraison :**
    - Gratuits si la ville de livraison est **Bordeaux**.
    - Hors Bordeaux : forfait fixe de **5,00 €** + **0,59 € par kilomètre**.
  - **Remise de 10% :** appliquée automatiquement sur le prix du menu pour toute commande ayant au moins **5 personnes de plus** que le minimum requis.
  - **Récapitulatif détaillé avant validation :** détail prix menu, remise éventuelle, frais de livraison, total TTC.
- **Confirmation :** Envoi d'un email de confirmation de commande au client.

### 2.6. Espace Utilisateur (Client)
- Visualisation de l'historique et du détail de ses commandes.
- Modification de ses données personnelles.
- **Modification / Annulation de commande :**
  - Possible tant que la commande n'est pas passée au statut `'accepté'` par l'équipe.
  - Tout est modifiable sauf le choix du menu.
- **Suivi de commande :**
  - Historique chronologique des changements d'état avec date et heure.
- **Dépôt d'avis :**
  - Dès qu'une commande passe au statut `'terminée'`, l'utilisateur reçoit une notification par email pour déposer son avis (note de 1 à 5 étoiles + commentaire).

### 2.7. Espace Employé
- **Gestion du catalogue :** Ajout, modification, suppression des menus, plats, allergènes et horaires d'ouverture.
- **Gestion des commandes :**
  - Filtrage des commandes par statut et par client.
  - Mise à jour du cycle de statut :
    1. `'accepté'` (validation initiale)
    2. `'en préparation'` (cuisine)
    3. `'en cours de livraison'` (logistique)
    4. `'livré'` (réception client)
    5. `'en attente du retour de matériel'` (si prêt de matériel -> envoi mail automatique notifiant le délai de 10 jours ouvrés sous peine de facturation forfaitaire de **600 €** selon les CGV)
    6. `'terminée'` (livrée sans matériel ou après restitution)
  - **Annulation / Modification par l'employé :** Nécessite obligatoirement un motif d'annulation et la spécification du mode de contact préalable (GSM ou mail).
- **Modération des avis :** Validation (statut `'VALIDE'`) pour affichage sur l'accueil ou refus (statut `'REFUSE'`).

### 2.8. Espace Administrateur
- Accès à l'ensemble des fonctionnalités de l'espace Employé.
- **Gestion des comptes employés :**
  - Création de compte employé (email, mot de passe).
  - Envoi d'un email de notification à l'employé sans mentionner le mot de passe (qui doit lui être remis en main propre).
  - Désactivation / blocage d'un compte employé en cas de départ.
  - *Règle :* Pas de création de compte Administrateur depuis l'interface (compte admin initial inséré en base pour José).
- **Tableau de bord & Statistiques NoSQL (MongoDB) :**
  - Graphique comparatif du nombre de commandes par menu (données agrégées depuis MongoDB `statistiques_commandes`).
  - Calcul et affichage du chiffre d'affaires par menu avec filtres par menu et par période / durée.

### 2.9. Page Contact
- Formulaire de contact public : Titre, description/message, email.
- Envoi d'un email de notification à l'équipe.

### 2.10. Accessibilité & Qualité
- Conformité au référentiel **RGAA** (contraste, sémantique HTML5, balises ARIA, navigation au clavier, attributs `alt` sur les images).

---

## 3. Schéma de Données (MCD MySQL)

D'après l'Annexe 1 du sujet :
- **`utilisateur`** (`utilisateur_id`, `nom`, `prenom`, `email`, `password`, `gsm`, `adresse_postale`, `role`, `is_active`, `date_creation`)
- **`theme`** (`theme_id`, `libelle`)
- **`regime`** (`regime_id`, `libelle`)
- **`plat`** (`plat_id`, `titre`, `description`, `type_plat` [entree, plat, dessert])
- **`allergene`** (`allergene_id`, `libelle`)
- **`plat_allergene`** (`plat_id`, `allergene_id`)
- **`menu`** (`menu_id`, `titre`, `description`, `nb_personne_min`, `prix_base_min`, `stock_disponible`, `conditions_delai_stockage`, `theme_id`, `regime_id`, `image_url`)
- **`menu_plat`** (`menu_id`, `plat_id`)
- **`commande`** (`commande_id`, `numero_commande`, `date_commande`, `date_prestation`, `heure_prestation`, `adresse_prestation`, `ville_prestation`, `distance_km`, `nb_personnes`, `prix_menu_unitaire`, `remise_appliquee`, `frais_livraison`, `prix_total`, `statut`, `pret_materiel`, `restitution_materiel`, `motif_annulation`, `contact_mode_annulation`, `utilisateur_id`, `menu_id`)
- **`commande_historique_statut`** (`historique_id`, `commande_id`, `statut`, `date_changement`, `modifie_par_id`)
- **`avis`** (`avis_id`, `note`, `commentaire`, `statut` [EN_ATTENTE, VALIDE, REFUSE], `date_creation`, `utilisateur_id`, `commande_id`)
- **`horaire`** (`horaire_id`, `jour_semaine`, `heure_ouverture`, `heure_fermeture`, `est_ouvert`)

---

## 4. Schéma NoSQL MongoDB (`vite_et_gourmand_nosql`)

- **Collection `statistiques_commandes` :**
  - Document par commande enregistrée pour analyse rapide et agrégations :
  ```json
  {
    "numero_commande": "CMD-2026-0001",
    "menu_id": 1,
    "menu_titre": "Menu Terroir Bordelais",
    "date_commande": "2026-09-10T10:00:00Z",
    "nb_personnes": 12,
    "chiffre_affaires": 450.00,
    "frais_livraison": 0.00,
    "statut": "accepte",
    "theme": "Classique",
    "regime": "Classique"
  }
  ```
