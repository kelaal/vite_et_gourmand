# Directives Projet - Vite & Gourmand (ECF)

## Stack & Configuration
- PHP 8.2 native avec XAMPP (htdocs/vite_et_gourmand)
- Base relationnelle : MySQL via PDO (`config/database.php`)
- Base NoSQL : MongoDB via l'extension native PHP (`config/mongo.php`)

## Document de Référence
- Le sujet complet de l'ECF est disponible dans `docs/sujet_ecf.pdf` (ou `ECF_SUBJECT.md`).

## Commandes Importantes
- Démarrage BDD : Vérifier les services Apache et MySQL sous XAMPP.

## Règles Métier ECF
- Frais de livraison : 0€ à Bordeaux, sinon 5€ + 0.59€/km.
- Double insertion : Toute commande validée doit être insérée dans MySQL ET MongoDB.
- Avis : Statut par défaut `'EN_ATTENTE'`, affichage uniquement des avis `'VALIDE'`.