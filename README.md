# Vite & Gourmand 🍽️

Application web de traiteur gastronomique sur-mesure à Bordeaux, développée dans le cadre de l'**Évaluation en Cours de Formation (ECF) - Titre Professionnel Développeur Web et Web Mobile (DWWM)**.

---

## 🛠️ Stack Technique

* **Serveur Web :** Apache (XAMPP sur port 80)
* **Langage Back-End :** PHP 8.2 (Architecture modulaire & Design Pattern *Repository*)
* **Front-End :** HTML5 sémantique (Conforme RGAA), CSS3 Vanilla moderne & modulaire (Playfair Display, Plus Jakarta Sans), JavaScript Vanilla
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

## 📂 Architecture du Projet

```text
├── actions/             # Traitements des formulaires (POST)
│   └── ajouter_avis.php
├── config/              # Configuration & connexions
│   ├── database.php     # Connexion PDO MySQL
│   ├── mongo.php        # Connexion & helpers NoSQL MongoDB
│   └── setup_db.php     # Script d'initialisation MySQL & MongoDB
├── docs/                # Documents de cadrage & sujet ECF
├── includes/            # Composants réutilisables (Navbar fixe, Footer dynamique)
│   ├── navbar.php
│   └── footer.php
├── public/              # Ressources statiques
│   ├── css/             # Feuilles de styles modulaires
│   ├── img/             # Images & icônes SVG
│   └── js/              # Scripts frontend
├── repositories/        # Couche d'accès aux données (Repository Pattern)
│   ├── BaseRepository.php
│   ├── MenuRepository.php
│   ├── AvisRepository.php
│   ├── UtilisateurRepository.php
│   ├── CommandeRepository.php
│   ├── HoraireRepository.php
│   └── StatistiqueRepository.php
├── sql/                 # Scripts SQL DDL et DML
│   ├── schema.sql       # Schéma de base de données relationnelle
│   └── fixtures.sql     # Jeu d'essai de démonstration
├── .env.example         # Modèle des variables d'environnement
├── .gitignore           # Fichiers ignorés par Git
├── ECF_SUBJECT.md       # Cahier des charges & critères ECF
├── PROJET_CONTEXT.md    # Source de vérité du projet
├── README.md            # Documentation de déploiement
└── index.php            # Page d'accueil dynamique
```

---

## 🌿 Gestion des Branches Git (ECF)

* `main` : Branche principale stable (code prêt pour la livraison / jury).
* `dev` : Branche d'intégration et de développement.
* `feature/*` : Branches pour chaque fonctionnalité spécifique.

---
© 2026 Vite & Gourmand - Réalisé pour le Titre Professionnel DWWM.
