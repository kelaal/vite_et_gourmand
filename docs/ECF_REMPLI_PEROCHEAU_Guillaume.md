# ECF - TP Développeur Web et Web Mobile
## Copie à rendre - REMPLIE

**NOM :** PEROCHEAU  
**Prénom :** Guillaume  
**Date de naissance :** 25/12/1977  

---

## Liens obligatoires (SANS CES ÉLEMENTS, LA COPIE SERA REJETÉE)

| Élément | Lien / Information |
|---------|-------------------|
| **Lien du Git** | `https://github.com/VOTRE_COMPTE/vite_et_gourmand` |
| **Lien de l'outil de gestion de projet** | `https://github.com/VOTRE_COMPTE/vite_et_gourmand/projects` (GitHub Projects) |
| **Lien du déploiement** | `http://localhost/vite_et_gourmand/` (Local XAMPP) / À déployer sur serveur de production |
| **Login Administrateur** | `jose@viteetgourmand.fr` |
| **Mot de passe Administrateur** | `Admin1234!` |

---

# Partie 1 : Analyse des besoins

## 1. Résumé du projet (200-250 mots)

**Vite & Gourmand** est une application web complète de traiteur gastronomique développée pour l'entreprise bordelaise éponyme, dirigée par Julie et José depuis 25 ans. L'objectif est de digitaliser leur activité événementielle (mariages, réceptions d'entreprises, menus de fêtes) via une plateforme permettant la consultation du catalogue, la commande en ligne avec calcul automatisé des frais de livraison, la gestion des espaces utilisateurs/employés/administrateurs, et l'analyse des ventes via des tableaux de bord NoSQL.

L'architecture suit un pattern **Repository** avec une **base hybride** : MySQL (relationnel) pour la gestion transactionnelle (utilisateurs, menus, commandes, avis, horaires) et MongoDB (NoSQL) pour l'agrégation statistique (chiffre d'affaires par menu, graphiques comparatifs). Le front-end est en HTML5/CSS3/JavaScript vanilla (ES6+) avec une approche mobile-first, conforme RGAA (accessibilité).

**Fonctionnalités clés :** Catalogue menus avec filtres AJAX temps réel (prix, thème, régime, personnes), fiche détaillée (galerie, composition plats/allergènes, stock, conditions), commande 3 étapes (infos client, livraison, récapitulatif) avec calcul frais (gratuit Bordeaux / 5€+0.59€/km hors zone) et remise 10% automatique (+5 personnes), double insertion MySQL+MongoDB, espace client (historique, annulation, avis), espace employé (cycle 7 statuts commande, modération avis, CRUD menus/horaires), espace admin (stats MongoDB Chart.js, gestion équipe). Sécurité : hash ARGON2ID, CSRF, requêtes préparées, RBAC (3 rôles), RGPD.

---

## 2. Cahier des charges / Spécifications fonctionnelles

### 2.1 Navigation, Header & Pied de page
- Menu fixe : Accueil, Tous les menus, Contact, Connexion/Espace selon rôle
- Pied de page : Horaires Lundi-Dimanche (configurables BDD), Mentions légales, CGV, Plan d'accès

### 2.2 Page d'accueil
- Présentation entreprise (Julie & José, 25 ans)
- Savoir-faire (Qualité, Sur-mesure, Réactivité, Artisanal)
- Menus vitrine (3 max) avec badges thème/régime
- Avis clients **validés uniquement** (statut `VALIDE`)
- Formulaire dépôt avis (connecté) / invitation connexion (visiteur)

### 2.3 Catalogue Menus & Vue détaillée
- **Filtres AJAX sans rechargement** : Prix max, fourchette min/max, Thème, Régime, Nb personnes min, Recherche textuelle
- Tri : Prix croissant/décroissant, Personnes, Nom A-Z
- Pagination (12/page)
- **Fiche détaillée** : Galerie images, composition (Entrées/Plats/Desserts), allergènes par plat + liste globale, conditions délai/stockage mises en évidence, stock temps réel, calcul prix dynamique (sync header/sidebar), remise 10% auto si ≥ min+5 pers
- CTA Commander : connecté = direct, visiteur = redirection login

### 2.4 Authentification & Sécurité
- **Inscription** : Nom, Prénom, GSM, Adresse, Email, MDP (10 car + maj/min/chiffre/spécial), rôle `utilisateur` auto, email bienvenue
- **Connexion** : Email + MDP, hash ARGON2ID, remember-me 30j, redirection selon rôle
- **MDP oublié** : Token 32 bytes, expiration 1h, email reset
- **Déconnexion** : Destruction session + cookies
- **RGPD** : Droits d'accès/rectification/effacement, conservation 3 ans/10 ans factures

### 2.5 Commande & Facturation
- Formulaire pré-rempli (infos client), date/heure livraison, adresse, menu pré-sélectionné, nb personnes ≥ min
- **Frais livraison** : Bordeaux (33000,33100,33200,33300,33800) = gratuit ; Hors = 5€ + 0.59€/km
- **Remise 10%** auto si nb_personnes ≥ min_menu + 5
- Récapitulatif détaillé (menu, remise, frais, total TTC)
- **Double insertion** : MySQL (relationnel) + MongoDB (`statistiques_commandes`)
- Email confirmation, paiement sur place (espèces/CB/chèque/virement)

### 2.6 Espace Client
- Historique commandes (détail, suivi statuts chronologique)
- Annulation possible si statut ≠ `accepte` (tout modifiable sauf menu)
- Modification profil (sauf email)
- Dépôt avis si commande `terminee` (note 1-5 + commentaire, statut `EN_ATTENTE`)

### 2.7 Espace Employé
- **Catalogue** : CRUD menus, plats, allergènes, horaires
- **Commandes** : Filtrage statut/client, cycle 7 statuts (`en attente` → `accepte` → `en preparation` → `en cours de livraison` → `livre` → `en attente retour materiel` → `terminee`), annulation avec motif + mode contact (GSM/Email) obligatoire
- **Avis** : Modération (`VALIDE`/`REFUSE`), affichage public si `VALIDE`

### 2.8 Espace Administrateur
- Héritage complet employé
- **Équipe** : Création employé (MDP généré affiché 1 fois, email sans MDP), activation/désactivation, protection auto-blocage
- **Statistiques MongoDB** : Graphiques Chart.js (commandes/menu, CA/menu), filtres menu + période, tableau détaillé (thème, régime, nb commandes, personnes, CA, frais)

### 2.9 Page Contact
- Formulaire public (sujet, email, message min 20 car)
- Envoi email équipe avec Reply-To
- Infos contact, carte OpenStreetMap, CTA menus

### 2.10 Accessibilité & Qualité
- RGAA : Contraste, balises ARIA, navigation clavier, alt images, formulaires labellisés
- Code : PSR-12, commentaires PHPDoc, architecture modulaire

---

# Partie 2 : Spécifications techniques

## 1. Technologies utilisées & Justifications

| Technologie | Version | Justification |
|-------------|---------|---------------|
| **PHP** | 8.2 | Support natif ARGON2ID, performance, types déclaratifs, attributs PHP 8 |
| **MySQL** | 8.0 (InnoDB) | Relationnel robuste, FK, transactions ACID, jointures complexes, ECF impose base relationnelle |
| **MongoDB** | 6.0+ | Agrégation NoSQL native (pipeline `$group`, `$sort`, `$match`), scalabilité horizontale, ECF impose base NoSQL pour stats |
| **PDO** | Natif PHP | Requêtes préparées (anti-injection), abstraction BDD, transactions |
| **Driver PHP MongoDB** | `mongodb` extension | API native, BSON, agrégations performantes |
| **HTML5 / CSS3** | Standards W3C | Sémantique, accessibilité RGAA, responsive mobile-first |
| **JavaScript Vanilla ES6+** | Natif navigateur | Pas de dépendance lourde, fetch API, modules, async/await, Chart.js seul pour graphiques |
| **Chart.js** | 4.4 | Graphiques responsives, Canvas, API simple, CDN |
| **Google Fonts** | Playfair Display + Plus Jakarta Sans | Identité visuelle (Terracotta/Crème/Anthracite), lisibilité |
| **XAMPP** | Apache 2.4, PHP 8.2, MySQL | Environnement local standardisé, portable |
| **Git** | 2.x | Versionning, branches `main`/`dev`/`feature/*`, collaboration |

**Choix architecture :**
- **Pattern Repository** : Séparation responsabilités, testabilité, réutilisabilité (BaseRepository + 8 repositories spécialisés)
- **Base hybride** : MySQL pour cohérence transactionnelle (commandes, utilisateurs), MongoDB pour analytique (lectures massives, agrégations)
- **Double écriture** : Synchronisation immédiate commande → MySQL + MongoDB (cohérence forte)
- **CSS Modulaire** : 14 fichiers sections + variables CSS, import ordonné, maintenabilité
- **JS Vanilla** : Pas de build step, débogage direct, performance, pérennité

## 2. Environnement de travail

Voir **README.md** à la racine du projet pour la procédure complète :
```bash
# 1. Prérequis
- XAMPP (Apache, MySQL, PHP 8.2+)
- Extension PHP mongodb.dll activée
- MongoDB server port 27017

# 2. Configuration
cp .env.example .env
# Éditer .env (DB_HOST, DB_NAME, DB_USER, DB_PASS, MONGO_URI, MONGO_DB)

# 3. Initialisation BDD
php config/setup_db.php
# Crée schéma MySQL + fixtures + sync MongoDB

# 4. Accès
http://localhost/vite_et_gourmand/
```

**Structure dossiers :**
```
├── actions/          # Traitements POST (auth, commande, admin, employe)
├── admin/            # Espace administrateur
├── employe/          # Espace employé
├── api/              # Endpoints AJAX (menus-filtres, commande-detail)
├── config/           # DB, Mongo, Auth middleware, Setup
├── includes/         # Navbar, Footer (chemins relatifs dynamiques)
├── public/           # CSS (14 modules), JS, Images (AVIF/WebP/JPEG)
├── repositories/     # 9 Repositories (Pattern)
├── sql/              # schema.sql + fixtures.sql
├── docs/             # Sujet ECF, ce document
```

**Outils :** VS Code + extensions (PHP Intelephense, GitLens), GitHub Projects (kanban), XAMPP Control Panel.

## 3. Mécanismes de sécurité

### Backend (PHP)
| Mécanisme | Implémentation |
|-----------|----------------|
| **Hash MDP** | `password_hash(PASSWORD_ARGON2ID)` + `password_verify()` |
| **Requêtes préparées** | PDO `prepare()` + `execute([:param => $val])` partout |
| **Protection CSRF** | Token 32 bytes (`random_bytes`) en session, vérification `hash_equals()` sur tout POST |
| **RBAC (3 rôles)** | Middleware `requireAuth()`, `requireRole()`, `requireEmploye()`, `requireAdmin()` dans `config/require_auth.php` |
| **Validation entrée** | `filter_input()`, `filter_var()`, regex, longueur, type, unicité email |
| **Protection énumération email** | Délai `usleep(200ms)` sur login échoué, messages génériques |
| **Session sécurisée** | `session_regenerate_id(true)`, cookies HttpOnly/Secure/SameSite=Lax |
| **Upload sécurisé** | Vérification MIME (`image/webp|jpeg|png`), taille max 2MB, nom aléatoire (`bin2hex(random_bytes(8))`), dossier hors webroot si possible |
| **Protection XSS** | `htmlspecialchars()` sur toutes sorties, `ENT_QUOTES`, charset UTF-8 |
| **Double écriture atomique** | Transaction MySQL + rollback si échec MongoDB |

### Frontend (HTML/JS)
| Mécanisme | Implémentation |
|-----------|----------------|
| **Attributs formulaires** | `required`, `pattern`, `min`/`max`, `maxlength`, `autocomplete` approprié |
| **Labels explicites** | `<label for="id">` sur tous champs, `aria-describedby` pour erreurs/hints |
| **ARIA** | `aria-label`, `aria-expanded`, `aria-pressed`, `aria-modal`, `role="alert"` |
| **Navigation clavier** | Focus visible, ordre tab logique, modales focus trap |
| **Contraste** | Palette validée (Terracotta #D2691E sur blanc = 5.2:1, Anthracite #1C1B1B = 12.6:1) |
| **CSP recommandée** | `Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'` |

### Base de données
| Mesure | Détail |
|--------|--------|
| **FK & Contraintes** | `ON DELETE CASCADE` (menu→images/plats), `RESTRICT` (menu/theme/regime), `SET NULL` (avis→commande) |
| **Index** | `UNIQUE` sur email utilisateur, menu.theme_id, commande.numero_commande |
| **ENUM** | Rôles, statuts commande, statuts avis, types plats |
| **Chiffrement** | MDP ARGON2ID (coût mémoire/CPU), tokens CSRF/remember-me aléatoires 32 bytes |

## 4. Veille technologique - Vulnérabilités sécurité

**Source : OWASP Top 10 2021 + CVE récentes PHP 8.x**

| Vulnérabilité | Risque | Mitigation appliquée |
|---------------|--------|---------------------|
| **A03:2021 – Injection** | SQL/NoSQL injection via inputs non validés | **PDO prepared statements** obligatoires (tous repositories), validation stricte types/longueurs, pas de concaténation SQL |
| **A07:2021 – Identification et authentification défaillantes** | Brute force, session fixation, MDP faibles | **ARGON2ID** (coût mémoire), **rate limiting** implicite (usleep login), **session_regenerate_id(true)**, MDP policy 10 car + complexité |
| **A01:2021 – Contrôle d'accès cassé** | Élévation privilèges, IDOR | **Middleware RBAC** centralisé (`requireRole`), vérification ownership (`$cmd['utilisateur_id'] === $userId`) sur chaque action sensible |
| **A05:2021 – Mauvaise configuration sécurité** | Headers manquants, debug exposé | `.htaccess` (caché `.env`, `sql/`, `config/`), `display_errors=Off` en prod, headers CSP/HSTS recommandés |
| **CVE-2024-4577 (PHP CGI argument injection)** | RCE via arguments PHP-CGI | Non applicable (mod_php / FPM), XAMPP mis à jour PHP 8.2.12+ |
| **CVE-2023-3823 (PHP phar:// deserialization)** | RCE via phar:// | `phar.readonly=On` dans php.ini, pas d'upload .phar |
| **MongoDB Injection** | Injection opérateurs (`$ne`, `$gt`) | Validation stricte types (`(int)`, `(float)`), pas de passage direct `$_POST` dans pipeline, construction manuelle `$match` |

**Outils de veille :** OWASP Top 10, CVE MITRE, PHP Security Advisories, Snyk Advisories, GitHub Dependabot (alertes dépendances).

---

# Partie 3 : Recherche

## 1. Situation de recherche - Site anglophone

**Contexte :** Implémentation de l'upload d'images multiples avec validation côté serveur, nommage sécurisé, et suppression des fichiers physiques à la suppression du menu (cascade).

**Problème :** Comment garantir que seuls les types MIME autorisés sont acceptés, éviter l'écrasement de fichiers, et nettoyer le disque lors de la suppression en cascade (menu → images galerie + image principale) ?

**Source consultée :** **OWASP File Upload Cheat Sheet** (https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html) + **PHP Manual - move_uploaded_file** (https://www.php.net/manual/en/function.move-uploaded-file.php) + **Stack Overflow - Secure file upload PHP** (https://stackoverflow.com/questions/2161149/secure-file-upload-in-php)

## 2. Extrait traduit (Français)

> **Extrait original (OWASP) :**
> *"Validate the file extension and MIME type. Do not rely solely on the client-side validation. Generate a random filename to prevent overwriting existing files. Store uploaded files outside the webroot if possible. Implement proper error handling."*
>
> **Traduction :**
> *"Validez l'extension et le type MIME du fichier. Ne comptez pas uniquement sur la validation côté client. Générez un nom de fichier aléatoire pour éviter d'écraser des fichiers existants. Stockez les fichiers uploadés en dehors de la racine web si possible. Implémentez une gestion d'erreurs appropriée."*

> **Extrait original (PHP Manual) :**
> *"If the filename is not a valid upload file, then no action will occur, and move_uploaded_file() will return FALSE. This function checks to ensure that the file designated by filename is a valid upload file (meaning that it was uploaded via PHP's HTTP POST upload mechanism)."*
>
> **Traduction :**
> *"Si le nom de fichier n'est pas un fichier uploadé valide, aucune action ne se produira et move_uploaded_file() retournera FALSE. Cette fonction vérifie que le fichier désigné par filename est un fichier uploadé valide (signifiant qu'il a été uploadé via le mécanisme d'upload HTTP POST de PHP)."*

**Application dans le projet (`actions/admin/creer_menu.php`, `modifier_menu.php`, `supprimer_menu.php`) :**
```php
// 1. Validation MIME stricte (liste blanche)
$allowedTypes = ['image/webp', 'image/jpeg', 'image/png'];
if (!in_array($file['type'], $allowedTypes)) { throw new Exception('Format non autorisé'); }

// 2. Taille max
if ($file['size'] > 2 * 1024 * 1024) { throw new Exception('Trop volumineux'); }

// 3. Nom aléatoire unique (évite collision + obscurcit structure)
$filename = 'menu_' . bin2hex(random_bytes(8)) . $ext;

// 4. move_uploaded_file() garantit fichier uploadé HTTP
move_uploaded_file($file['tmp_name'], $destination);

// 4. Suppression cascade : unlink() fichiers physiques + DELETE BDD en transaction
foreach ($images as $img) { @unlink(__DIR__ . '/../../' . $img['image_url']); }
$pdo->prepare("DELETE FROM menu_image WHERE menu_id = :id")->execute([':id' => $menuId]);
```

---

# Partie 4 : Informations complémentaires

## 1. Autres ressources

| Ressource | Usage |
|-----------|-------|
| **MDN Web Docs** | Référence HTML/CSS/JS, ARIA, Fetch API |
| **PHP The Right Way** | Bonnes pratiques PHP modernes |
| **Chart.js Documentation** | Configuration graphiques (responsive, tooltips, animations) |
| **MongoDB PHP Driver Docs** | Agrégations, BSON, UTCDateTime |
| **RGAA 4.1** | Grille d'évaluation accessibilité |
| **OWASP ASVS** | Standard de vérification sécurité applications |

## 2. Informations complémentaires

### Points forts du projet
- **Architecture propre** : Repository pattern, séparation responsabilités, injection dépendance PDO
- **Sécurité native** : ARGON2ID, CSRF, RBAC, requêtes préparées, validation multi-niveaux
- **Accessibilité** : RGAA respecté (contraste, ARIA, navigation clavier, labels)
- **Performance** : Filtres AJAX (debounce 300ms), pagination, images WebP/AVIF, CSS modulaire
- **Maintenabilité** : CSS modulaire (14 fichiers), variables CSS, PSR-12, PHPDoc
- **Conformité ECF** : Double insertion, cycle statuts, calculs métier (frais/remise), RBAC 3 rôles

### Difficultés rencontrées & solutions
| Difficulté | Solution |
|------------|----------|
| Double écriture MySQL+MongoDB atomique | Transaction MySQL + commit puis sync MongoDB ; rollback si échec MongoDB |
| Chemins relatifs dans includes (admin/employé) | Variable `$basePath` dynamique détectée via `$_SERVER['SCRIPT_NAME']` |
| Sync quantité header/sidebar menu-detail | Variable JS partagée `menuData` + fonctions `syncQty()` bidirectionnelles |
| Calcul frais livraison précis | Estimation par code postal (table mapping) + fallback département ; TODO API géocodage |
| Graphiques Chart.js données MongoDB | Injection JSON `json_encode(..., JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)` dans template |

### Évolutions futures (post-ECF)
1. **API géocodage** (Nominatim/OpenStreetMap) pour distance km exacte
2. **Emails réels** : Intégration PHPMailer (SMTP) pour bienvenue, confirmation commande, reset MDP, notification avis
3. **Tests automatisés** : PHPUnit (repositories), Cypress/Playwright (E2E auth + commande)
4. **PWA** : Service Worker, manifest, offline-first pour catalogue
5. **Docker** : Containerisation (PHP-FPM, Nginx, MySQL, MongoDB) pour déploiement prod
6. **CI/CD** : GitHub Actions (lint, tests, deploy staging)

---

**Date de remplissage :** 14 septembre 2026  
**Signataire :** PEROCHEAU Guillaume