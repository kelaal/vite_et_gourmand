# Diagrammes UML — Vite & Gourmand

Ces diagrammes sont écrits en [Mermaid](https://mermaid.js.org/). Ils peuvent être affichés directement dans GitHub, GitLab, VS Code avec une extension Mermaid, ou copiés dans Mermaid Live Editor.

## Diagramme fonctionnel — cas d'utilisation

```mermaid
flowchart LR
    V([Visiteur])
    C([Client])
    E([Employé])
    A([Administrateur])
    DB[(MySQL)]
    M[(MongoDB<br/>statistiques_commandes)]

    subgraph APP[Application Vite & Gourmand]
        direction TB
        UC1[Consulter l'accueil,<br/>menus, horaires et CGV]
        UC2[Filtrer le catalogue<br/>et consulter un menu]
        UC3[Créer un compte /<br/>se connecter]
        UC4[Réinitialiser son<br/>mot de passe]
        UC5[Passer une commande]
        UC6[Calculer le total :<br/>remise et livraison]
        UC7[Consulter / modifier son profil<br/>et suivre ses commandes]
        UC8[Annuler une commande<br/>encore en attente]
        UC9[Déposer un avis]
        UC10[Administrer menus, plats,<br/>allergènes et horaires]
        UC11[Traiter les commandes :<br/>statut ou annulation motivée]
        UC12[Modérer les avis]
        UC13[Gérer les comptes employés]
        UC14[Consulter les statistiques<br/>de ventes et le chiffre d'affaires]
    end

    V --> UC1 & UC2 & UC3 & UC4
    C --> UC1 & UC2 & UC5 & UC7 & UC8 & UC9
    E --> UC10 & UC11 & UC12
    A --> UC10 & UC11 & UC12 & UC13 & UC14
    UC5 --> UC6
    UC5 --> DB
    UC6 --> DB
    UC7 --> DB
    UC8 --> DB
    UC9 --> DB
    UC10 --> DB
    UC11 --> DB
    UC12 --> DB
    UC13 --> DB
    UC5 -. double écriture .-> M
    UC14 --> M
```

Règles métier portées par le parcours de commande : livraison gratuite à Bordeaux ; sinon `5,00 € + 0,59 €/km` ; remise de 10 % à partir du minimum du menu + 5 personnes. Toute commande crée un historique initial `en attente` et une entrée MongoDB destinée aux statistiques.

## Diagramme de classes — modèle métier et persistance

```mermaid
classDiagram
    direction LR

    class Utilisateur {
        +int utilisateur_id
        +string nom
        +string prenom
        +string email
        +string password
        +string gsm
        +string adresse_postale
        +Role role
        +boolean is_active
        +datetime date_creation
    }

    class Menu {
        +int menu_id
        +string titre
        +text description
        +int nb_personne_min
        +decimal prix_base_min
        +int stock_disponible
        +text conditions_delai_stockage
        +string image_url
        +boolean is_active
    }

    class Theme {
        +int theme_id
        +string libelle
    }

    class Regime {
        +int regime_id
        +string libelle
    }

    class Plat {
        +int plat_id
        +string titre
        +text description
        +TypePlat type_plat
    }

    class Allergene {
        +int allergene_id
        +string libelle
    }

    class MenuImage {
        +int image_id
        +string image_url
        +string alt_text
    }

    class Commande {
        +int commande_id
        +string numero_commande
        +datetime date_commande
        +date date_prestation
        +time heure_prestation
        +string adresse_prestation
        +string ville_prestation
        +decimal distance_km
        +int nb_personnes
        +decimal prix_menu_unitaire
        +decimal remise_appliquee
        +decimal frais_livraison
        +decimal prix_total
        +StatutCommande statut
        +boolean pret_materiel
        +boolean restitution_materiel
    }

    class HistoriqueStatut {
        +int historique_id
        +StatutCommande statut
        +datetime date_changement
    }

    class Avis {
        +int avis_id
        +int note
        +text commentaire
        +StatutAvis statut
        +datetime date_creation
    }

    class Horaire {
        +int horaire_id
        +string jour_semaine
        +time heure_ouverture
        +time heure_fermeture
        +boolean est_ouvert
        +int ordre_jour
    }

    class StatistiqueCommandeMongo {
        +string numero_commande
        +int menu_id
        +string menu_titre
        +date date_commande
        +int nb_personnes
        +decimal chiffre_affaires
        +decimal frais_livraison
        +string statut
    }

    class Role {
        <<enumeration>>
        utilisateur
        employe
        administrateur
    }

    class TypePlat {
        <<enumeration>>
        entree
        plat
        dessert
    }

    class StatutCommande {
        <<enumeration>>
        en attente
        accepte
        en preparation
        en cours de livraison
        livre
        en attente retour materiel
        terminee
        annulee
    }

    class StatutAvis {
        <<enumeration>>
        EN_ATTENTE
        VALIDE
        REFUSE
    }

    Theme "1" --> "0..*" Menu : catégorise
    Regime "1" --> "0..*" Menu : définit
    Menu "1" *-- "0..*" MenuImage : galerie
    Menu "0..*" -- "0..*" Plat : compose
    Plat "0..*" -- "0..*" Allergene : contient
    Utilisateur "1" --> "0..*" Commande : passe
    Menu "1" --> "0..*" Commande : concerne
    Commande "1" *-- "1..*" HistoriqueStatut : trace
    Utilisateur "0..1" --> "0..*" HistoriqueStatut : modifie
    Utilisateur "1" --> "0..*" Avis : rédige
    Commande "0..1" --> "0..*" Avis : déclenche
    Commande ..> StatistiqueCommandeMongo : synchronise
    Utilisateur --> Role
    Plat --> TypePlat
    Commande --> StatutCommande
    Avis --> StatutAvis
```

Les relations `Menu–Plat` et `Plat–Allergène` sont des associations plusieurs-à-plusieurs, implémentées en MySQL par les tables de liaison `menu_plat` et `plat_allergene`. `StatistiqueCommandeMongo` est un document dénormalisé, créé lors de l'enregistrement d'une commande, et non une table relationnelle.
