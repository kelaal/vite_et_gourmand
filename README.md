# Vite & Gourmand 🍽️

Application web de traiteur gastronomique sur-mesure à Bordeaux, développée dans le cadre de l'**Évaluation en Cours de Formation (ECF) - Titre Professionnel Développeur Web et Web Mobile (DWWM)**.

---

## 🛠️ Stack Technique

* **Serveur Web :** Apache (XAMPP sur port 80)
* **Langage Back-End :** PHP 8.2 (Architecture modulaire & Design Pattern *Repository*)
* **Front-End :** HTML5 sémantique (Conforme RGAA), CSS3 Vanilla moderne & modulaire (Playfair Display, Plus Jakarta Sans), JavaScript Vanilla (ES6+)
* **Bases de Données (Hybride) :**
  * **Relationnelle (MySQL) :** Gestion relationnelle complète (Utilisateurs, Menus, Plats, Allergènes, Commandes, Avis, Horaires).
  * **NoSQL (MongoDB) :** Base `vite_et_gourmand_nosql`, collection `statistiques_commandes` alimentée en double-écriture pour l'agrégation des statistiques et du chiffre d'affaires.

---

## 🚀 Guide d'Installation & Déploiement Local

### 1. Prérequis
* XAMPP installé avec **Apache**, **MySQL** et **PHP 8.2+**.
* Extension PHP MongoDB activée (`php_mongodb.dll`).
* Serveur MongoDB en cours d'exécution sur le port `27017`.

### 2. Cloner le projet
Placez le projet dans le dossier racine de votre serveur web local (ex: `C:\xampp\htdocs\vite_et_gourmand`) :
```bash
git clone https://github.com/VOTRE_COMPTE/vite_et_gourmand.git
cd vite_et_gourmand
```

### 3. Configuration de l'Environnement (`.env`)
Copiez le fichier d'exemple et adaptez vos identifiants si nécessaire :
```bash
cp .env.example .env
```
Contenu type du fichier `.env` :
```ini
DB_HOST=127.0.0.1
DB_NAME=vite_et_gourmand
DB_USER=root
DB_PASS=

# MongoDB NoSQL
MONGO_URI=mongodb://127.0.0.1:27017
MONGO_DB=vite_et_gourmand_nosql
```

### 4. Initialisation Automatisée des Bases de Données (MySQL + MongoDB)
Exécutez le script d'initialisation pour créer le schéma SQL, injecter le jeu d'essai complet et synchroniser MongoDB :
```bash
php config/setup_db.php
```

### 5. Accéder à l'application
Lancez Apache et MySQL via le panneau de contrôle XAMPP, puis ouvrez votre navigateur :
👉 **`http://localhost/vite_et_gourmand/index.php`**

---

## 🔑 Identifiants de Test pour l'Évaluation

| Rôle | Nom / Prénom | Email (Identifiant) | Mot de passe |
| :--- | :--- | :--- | :--- |
| **Administrateur** | José Administrateur | `jose@viteetgourmand.fr` | `Admin1234!` |
| **Employée** | Julie Chef | `julie@viteetgourmand.fr` | `Employe1234!` |
| **Employé** | Thomas Dubois | `thomas@viteetgourmand.fr` | `Employe1234!` |
| **Client** | Sophie Martin | `sophie.martin@gmail.com` | `Client1234!` |
| **Client** | Lucas Bernard | `lucas.bernard@yahoo.fr` | `Client1234!` |
| **Cliente** | Claire Dupont | `claire.dupont@orange.fr` | `Client1234!` |

---

## ✅ Fonctionnalités Implémentées (ECF Conformes)

### 🔐 Authentification & Sécurité
- **Connexion** : Email + MDP, redirection selon rôle (admin/employé/client), remember-me (30 jours)
- **Inscription** : Validation RGPD, MDP fort (10 car + maj/min/chiffre/spécial), hash ARGON2ID, auto-login
- **Mot de passe oublié** : Token sécurisé 32 bytes, expiration 1h, envoi email (simulation log)
- **Reset MDP** : Validation token + expiration, nouveau MDP avec règles de force
- **Déconnexion** : Destruction session + cookies HttpOnly
- **Middleware** : `requireAuth()`, `requireRole()`, `requireEmploye()`, `requireAdmin()`, protection CSRF

### 🏠 Page d'Accueil (`index.php`)
- Hero section avec background responsive (AVIF/WebP/JPEG)
- Présentation entreprise (25 ans, Julie & José)
- Savoir-faire : Qualité, Sur-mesure, Réactivité, Artisanal
- Menus vitrine (3 à la une) avec badges thème/régime
- Avis clients validés uniquement (statut `VALIDE`)
- Formulaire dépôt avis (connecté) ou invitation connexion (visiteur)

### 📞 Page Contact (`contact.php`)
- **Formulaire public** : Sujet (select), Email, Message (textarea min 20 car)
- **Validation** : Côté client (HTML5) + serveur (CSRF, longueur, format email)
- **Envoi email** : Notification équipe (`contact@viteetgourmand.fr`) avec Reply-To expéditeur
- **Message flash** : Succès animé ou erreurs inline
- **Infos côte** : Coordonnées, livraison, horaires, carte OpenStreetMap interactive
- **CTA** : Lien vers menus si prêt à commander

### 📜 Pages Légales
| Page | Contenu |
|------|---------|
| `cgv.php` | 12 articles : Objet, Commande, Prix/Paiement, Livraison, Matériel (prêt + 600€ pénalité), Rétractation (exclue Art. L.221-28), Responsabilité, Allergènes (Règlement INCO), RGPD, Propriété intellectuelle, Droit applicable + Médiation, Dispositions générales |
| `mentions-legales.php` | Éditeur (SIRET, RCS, TVA, gérant), Hébergeur (OVHcloud), Propriété intellectuelle, RGPD détaillé (tableau finalités/bases/durées/droits), Cookies (tableau types/durée/consentement), Responsabilité, Droit applicable, Contact DPO/CNIL |

### 👨‍🍳 Espace Employé (`employe/dashboard.php`)
- **Dashboard KPI** : 6 cartes (en attente, préparation, livraison, terminées, avis à modérer, menus actifs)
- **Gestion Commandes** : Tableau filtrable par statut/client, actions (voir détail, changer statut, annuler)
- **Cycle Statuts (6 étapes)** : `en attente` → `acceptée` → `en préparation` → `en livraison` → `livrée` → `attente retour matériel` → `terminée`
- **Annulation Employé** : Motif + mode contact (GSM/Email) obligatoires (règle ECF)
- **Modération Avis** : Liste `EN_ATTENTE` avec boutons Valider/Refuser → statut `VALIDE`/`REFUSE`
- **Gestion Menus** : Tableau CRUD (activer/désactiver, modifier, nouveau)
- **Horaires** : Grille Lundi-Dimanche (ouverture/fermeture, ouvert/fermé), sauvegarde groupée
- **Détail Commande** (`employe/commande-detail.php`) : Infos client, menu, facturation, matériel, historique statuts, actions

### ⚙️ Espace Administrateur (`admin/dashboard.php`)
- **Hérite de tout l'espace employé** + fonctionnalités exclusives :
- **Gestion Équipe** : Tableau employés (nom, email, téléphone, rôle, statut, date création)
  - Création compte employé : formulaire complet, **MDP généré affiché une seule fois** (copier), email notification (sans MDP)
  - Activation/Désactivation (bloquer) employé via AJAX
  - Protection : admin ne peut pas se bloquer lui-même
- **Statistiques NoSQL (MongoDB)** :
  - Graphique 1 : **Nombre de commandes par menu** (Chart.js, agrégation `statistiques_commandes`)
  - Graphique 2 : **Chiffre d'affaires par menu** (Chart.js, sommes `chiffre_affaires`)
  - Filtres : Par menu, date début/fin
  - Tableau détaillé : Menu, thème, régime, nb commandes, total personnes, CA, frais livraison
- **Navigation** : Lien vers vue employé + espace client

### 📋 Catalogue Menus (`menus.php`)
- **Formulaire public** : Sujet (select), Email, Message (textarea min 20 car)
- **Validation** : Côté client (HTML5) + serveur (CSRF, longueur, format email)
- **Envoi email** : Notification équipe (`contact@viteetgourmand.fr`) avec Reply-To expéditeur
- **Message flash** : Succès animé ou erreurs inline
- **Infos côte** : Coordonnées, livraison, horaires, carte OpenStreetMap interactive
- **CTA** : Lien vers menus si prêt à commander

### 📋 Catalogue Menus (`menus.php`)
- **Filtres dynamiques AJAX** (sans rechargement) :
  - Recherche texte (nom/description)
  - Prix maximum (slider) + Fourchette min/max (inputs)
  - Thème (Noël, Pâques, Classique, Événement, Mariage)
  - Régime (Classique, Végétarien, Vegan, Sans gluten)
  - Nombre de personnes minimum (slider)
- Tri : Prix croissant/décroissant, Personnes, Nom A-Z
- Pagination (12 par page)
- Sidebar responsive : Fixe desktop, Drawer mobile avec toggle
- Grille responsive `auto-fit minmax(320px)`

### 🍽️ Détail Menu (`menu-detail.php`)
- Galerie : Image principale + miniatures cliquables (navigation clavier)
- Composition plats groupés : Entrées / Plats / Desserts
- Allergènes par plat (tags) + Liste globale unique
- Conditions délai/stockage mises en évidence (boîte alert)
- Stock disponible temps réel + alerte rupture
- **Calcul prix dynamique** : Sync quantité header ↔ sidebar
- Remise 10% auto si quantité ≥ minimum + 5 personnes
- CTA Commander : Connecté = direct, Visiteur = redirection login

### 🛒 Processus Commande Complet
| Page | Fonctionnalité |
|------|----------------|
| `commander.php` | Formulaire 3 sections (Client pré-rempli, Livraison, Quantité) + Récapitulatif sticky |
| `commander-confirm.php` | Validation finale avec steps, détail facturation complet |
| `commander-success.php` | Confirmation animée, numéro commande, étapes suivantes, info paiement |

**Calculs Métier (ECF) :**
- ✅ **Frais livraison** : Gratuit Bordeaux (33000,33100,33200,33300,33800), sinon 5,00 € + 0,59 €/km
- ✅ **Remise 10%** : Auto si nb_personnes ≥ min_menu + 5
- ✅ **Double insertion** : MySQL (relationnel) + MongoDB (`statistiques_commandes`)
- ✅ **Historique statut** : Initial `en attente` tracé dans `commande_historique_statut`

### 👤 Espace Client (`espace.php`)
- **Mes Commandes** : Cartes avec badge statut, détail prix, actions (Voir détail, Annuler si `en attente`, Donner avis si `terminée`)
- **Mon Profil** : Modification nom/prénom/GSM/adresse (email protégé)
- **Sécurité** : Changement MDP avec validation force actuelle/nouveau/confirmation
- **Modales AJAX** : Détail commande (`api/commande-detail.php`), Confirmation annulation, Dépôt avis

### 🎨 Interface & Accessibilité (RGAA)
- **Navbar fixe** : Effet scroll (ombre + compact), Hamburger mobile accessible (ARIA)
- **Footer dynamique** : Horaires Lundi-Dimanche depuis BDD, mentions légales, CGV
- **CSS Modulaire** : 11 fichiers sections + variables CSS, responsive mobile-first
- **Formulaires** : Labels associés, messages d'erreur ARIA, validation HTML5 + JS
- **Contraste** : Palette Terracotta/Crème/Anthracite/Ocre conforme

### 📊 API & Endpoints
- `api/menus-filtres.php` : POST JSON (filtres, pagination, tri) → menus filtrés
- `api/commande-detail.php` : GET HTML fragment pour modale détail commande

---

## 📂 Architecture du Projet

```text
├── actions/                    # Traitements formulaires (POST)
│   ├── ajouter_avis.php
│   ├── connexion.php
│   ├── inscription.php
│   ├── deconnexion.php
│   ├── mot-de-passe-oublie.php
│   ├── reset-password.php
│   ├── contact.php
│   ├── employe/                # Actions employé
│   │   ├── change-statut.php
│   │   ├── annuler-commande.php
│   │   ├── moderer-avis.php
│   │   ├── update-horaires.php
│   │   └── toggle-menu.php
│   └── admin/                  # Actions admin
│       ├── add-employe.php
│       └── toggle-employe.php
├── api/                        # Endpoints AJAX
│   ├── commande-detail.php
│   └── menus-filtres.php
├── config/                     # Configuration & connexions
│   ├── database.php            # PDO MySQL
│   ├── mongo.php               # MongoDB Manager + helpers (insert, query, aggregate)
│   ├── require_auth.php        # Middleware auth, rôles, CSRF, validation MDP
│   └── setup_db.php            # Initialisation MySQL + MongoDB
├── docs/                       # Documents de cadrage & sujet ECF
├── includes/                   # Composants réutilisables
│   ├── navbar.php              # Navbar fixe + hamburger mobile
│   └── footer.php              # Footer + horaires dynamiques
├── public/                     # Ressources statiques
│   ├── css/
│   │   ├── style.css           # Point d'entrée (imports)
│   │   └── sections/           # 14 fichiers CSS modulaires (+ contact.css, + legal.css, + admin.css)
│   ├── img/                    # Images (AVIF/WebP/JPEG) + SVG
│   └── js/                     # Scripts frontend
├── repositories/               # Repository Pattern (Accès données)
│   ├── BaseRepository.php
│   ├── MenuRepository.php      # CRUD + filtres dynamiques + détails complets
│   ├── AvisRepository.php      # CRUD + modération (EN_ATTENTE/VALIDE/REFUSE)
│   ├── UtilisateurRepository.php # CRUD + rôles + toggle actif
│   ├── CommandeRepository.php  # CRUD + double-insertion MySQL/MongoDB + historique
│   ├── HoraireRepository.php   # CRUD horaires Lundi-Dimanche
│   └── StatistiqueRepository.php # Agrégations MongoDB (graphiques, CA)
├── sql/                        # Scripts SQL
│   ├── schema.sql              # Schéma complet (13 tables, FK, index, ENUM)
│   └── fixtures.sql            # Jeu d'essai (6 users, 5 menus, 14 plats, 14 allergènes, 3 commandes, 5 avis, 7 horaires)
├── .env.example
├── .gitignore
├── ECF_SUBJECT.md              # Cahier des charges complet
├── PROJET_CONTEXT.md           # Contexte projet pour IA
├── README.md                   # Ce fichier
├── index.php                   # Page d'accueil
├── menus.php                   # Catalogue avec filtres AJAX
├── menu-detail.php             # Détail menu complet
├── commander.php               # Étape 1 commande
├── commander-confirm.php       # Étape 2 confirmation
├── commander-success.php       # Succès commande
├── espace.php                  # Espace client (3 onglets + modales)
├── connexion.php               # Login
├── inscription.php             # Inscription
├── mot-de-passe-oublie.php     # Demande reset
├── reset-password.php          # Nouveau MDP
├── contact.php                 # Formulaire contact + carte
├── cgv.php                     # Conditions Générales de Vente
├── mentions-legales.php        # Mentions légales (LCEN, RGPD, cookies)
├── employe/
│   ├── dashboard.php           # Dashboard employé (commandes, avis, menus, horaires)
│   └── commande-detail.php     # Détail commande pour équipe
├── admin/
│   └── dashboard.php           # Dashboard admin (tout employé + équipe + stats MongoDB)
```

---

## 🌿 Gestion des Branches Git (ECF)

* `main` : Branche principale stable (code prêt pour la livraison / jury).
* `dev` : Branche d'intégration et de développement (branche courante).
* `feature/*` : Branches pour chaque fonctionnalité spécifique.

---

## 📋 Prochaines Étapes (Roadmap)

- [ ] **Page Contact** : Formulaire public + envoi email notification équipe
- [ ] **Espace Employé** (`employe/`) : Dashboard, gestion commandes (cycle 6 statuts), modération avis, CRUD menus/plats/allergènes/horaires
- [ ] **Espace Administrateur** (`admin/`) : Dashboard stats MongoDB (graphiques commandes/menu, CA par période/filtres), gestion comptes employés (création, désactivation)
- [ ] **Mentions Légales & CGV** : Pages statiques
- [ ] **Emails réels** : Intégration PHPMailer (bienvenue, confirmation commande, reset MDP, notification avis, contact)
- [ ] **Géocodage précis** : API Nominatim/OpenStreetMap pour calcul distance km exact
- [ ] **Tests** : PHPUnit (repositories), Cypress/Playwright (E2E auth + commande)

---

## 📄 Licence & Contexte

Projet réalisé dans le cadre de l'**ECF DWWM** — Titre Professionnel Développeur Web et Web Mobile.
Sujet complet disponible dans `docs/Enonce ECF.pdf` et `ECF_SUBJECT.md`.

© 2026 Vite & Gourmand — Bordeaux