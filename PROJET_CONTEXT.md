# Contexte du projet - Vite & Gourmand (ECF)

## 1. Stack Technique & Environnement
- **Serveur Web :** XAMPP (Apache sur port 80, PHP 8.2.12 x64 TS)
- **Bases de données :** 
  - MySQL (Relationnel, PDO, base `vite_et_gourmand`)
  - MongoDB (NoSQL native driver `php_mongodb.dll`, base `vite_et_gourmand_nosql`, collection `statistiques_commandes`)
- **Structure :** Application Web PHP native située dans `C:\xampp\htdocs\vite_et_gourmand`

## 2. Règles Métier Validées (ECF)
- **Calcul des frais de livraison :** Gratuit si la ville est "Bordeaux". Hors Bordeaux : forfait de 5.00 € + 0.59 € / km.
- **Double-insertion Commande :** Enregistrement synchronisé dans MySQL (données relationnelles) et MongoDB (agrégation des statistiques et chiffre d'affaires).
- **Gestion des Avis :** Formulaire soumis avec le statut `'EN_ATTENTE'`. Seuls les avis avec le statut `'VALIDE'` s'affichent sur la page d'accueil.

## 3. Arborescence du Projet
- `config/database.php` : Connexion PDO MySQL (gestion sécurisée via `.env`)
- `public/` : Ressources statiques (`css/`, `js/`, `img/`)
- `actions/` : Scripts de traitement des formulaires (ex: `ajouter_avis.php`, `creer_commande.php`)
- `index.php` : Page d'accueil avec intégration dynamique de MySQL (avis et menus)

## 4. Consigne pour l'Agent IA
Considère ce fichier comme la source de vérité pour l'architecture et les règles métier de l'application Vite & Gourmand.

## 5. Référentiel Évaluation (ECF)
- Le sujet complet de l'ECF est disponible dans `ECF_SUBJECT.md` (ou `docs/sujet_ecf.pdf`).
- Avant chaque proposition de code, vérifie la conformité strict avec le cahier des charges et la grille d'évaluation ECF.