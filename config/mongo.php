<?php
/**
 * Configuration et Connexion MongoDB (Driver PHP Natif php_mongodb.dll)
 * Base NoSQL : vite_et_gourmand_nosql
 * Collection principale : statistiques_commandes (exigée ECF)
 */

// Chargement des variables d'environnement si disponible
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    $mongoUri = $env['MONGO_URI'] ?? 'mongodb://127.0.0.1:27017';
    $mongoDb  = $env['MONGO_DB']  ?? 'vite_et_gourmand_nosql';
} else {
    $mongoUri = 'mongodb://127.0.0.1:27017';
    $mongoDb  = 'vite_et_gourmand_nosql';
}

// Constante globale du nom de la base NoSQL
define('MONGO_DB_NAME', $mongoDb);

$mongoManager = null;

try {
    if (!extension_loaded('mongodb')) {
        throw new Exception("L'extension PHP 'mongodb' n'est pas activée.");
    }

    // Instanciation du Manager MongoDB
    $mongoManager = new MongoDB\Driver\Manager($mongoUri, [
        'connectTimeoutMS' => 3000,
        'serverSelectionTimeoutMS' => 3000,
    ]);

} catch (Exception $e) {
    // Log d'erreur sans bloquer toute l'application
    error_log("Erreur de connexion MongoDB : " . $e->getMessage());
    $mongoManager = null;
}

/**
 * Retourne l'instance du Manager MongoDB
 * @return MongoDB\Driver\Manager|null
 */
function getMongoManager(): ?MongoDB\Driver\Manager {
    global $mongoManager;
    return $mongoManager;
}

/**
 * Insère un document dans une collection MongoDB
 * 
 * @param string $collection Nom de la collection
 * @param array $document Données du document à insérer
 * @return bool Succès ou échec
 */
function mongoInsert(string $collection, array $document): bool {
    $manager = getMongoManager();
    if (!$manager) return false;

    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        // Ajout automatique d'un timestamp ISO si non présent
        if (!isset($document['created_at'])) {
            $document['created_at'] = new MongoDB\BSON\UTCDateTime();
        }
        $bulk->insert($document);
        $result = $manager->executeBulkWrite(MONGO_DB_NAME . '.' . $collection, $bulk);
        return $result->getInsertedCount() > 0;
    } catch (Exception $e) {
        error_log("Erreur mongoInsert ({$collection}) : " . $e->getMessage());
        return false;
    }
}

/**
 * Exécute une requête de recherche dans une collection MongoDB
 * 
 * @param string $collection Nom de la collection
 * @param array $filter Filtres de recherche
 * @param array $options Options (tri, limite, projection...)
 * @return array Liste des documents trouvés
 */
function mongoQuery(string $collection, array $filter = [], array $options = []): array {
    $manager = getMongoManager();
    if (!$manager) return [];

    try {
        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $manager->executeQuery(MONGO_DB_NAME . '.' . $collection, $query);
        return iterator_to_array($cursor);
    } catch (Exception $e) {
        error_log("Erreur mongoQuery ({$collection}) : " . $e->getMessage());
        return [];
    }
}

/**
 * Exécute un pipeline d'agrégation dans MongoDB (pour graphiques & CA)
 * 
 * @param string $collection Nom de la collection
 * @param array $pipeline Pipeline d'agrégation MongoDB ($match, $group, $sort...)
 * @return array Résultats de l'agrégation
 */
function mongoAggregate(string $collection, array $pipeline): array {
    $manager = getMongoManager();
    if (!$manager) return [];

    try {
        $command = new MongoDB\Driver\Command([
            'aggregate' => $collection,
            'pipeline'  => $pipeline,
            'cursor'    => new stdClass(),
        ]);
        $cursor = $manager->executeCommand(MONGO_DB_NAME, $command);
        return iterator_to_array($cursor);
    } catch (Exception $e) {
        error_log("Erreur mongoAggregate ({$collection}) : " . $e->getMessage());
        return [];
    }
}

/**
 * Enregistre ou synchronise une commande dans la collection statistiques_commandes
 * Règle ECF : Double-écriture MySQL + MongoDB
 * 
 * @param array $commande Données de la commande
 * @return bool
 */
function syncCommandeStatsToMongo(array $commande): bool {
    $document = [
        'numero_commande'    => $commande['numero_commande'] ?? '',
        'menu_id'            => (int)($commande['menu_id'] ?? 0),
        'menu_titre'         => $commande['menu_titre'] ?? '',
        'theme'              => $commande['theme'] ?? 'Classique',
        'regime'             => $commande['regime'] ?? 'Classique',
        'nb_personnes'       => (int)($commande['nb_personnes'] ?? 0),
        'prix_total'         => (float)($commande['prix_total'] ?? 0.0),
        'chiffre_affaires'   => (float)($commande['prix_total'] ?? 0.0),
        'frais_livraison'    => (float)($commande['frais_livraison'] ?? 0.0),
        'ville_prestation'   => $commande['ville_prestation'] ?? '',
        'statut'             => $commande['statut'] ?? 'en attente',
        'date_commande'      => new MongoDB\BSON\UTCDateTime(
            isset($commande['date_commande']) ? strtotime($commande['date_commande']) * 1000 : time() * 1000
        ),
        'date_prestation'    => $commande['date_prestation'] ?? '',
    ];

    return mongoInsert('statistiques_commandes', $document);
}
