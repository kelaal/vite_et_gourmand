<?php
/**
 * Test de connexion et vérification MongoDB
 */
require_once __DIR__ . '/mongo.php';

echo "=== Test de connexion MongoDB ===" . PHP_EOL;

$manager = getMongoManager();
if (!$manager) {
    echo "❌ ÉCHEC : Impossible d'obtenir le gestionnaire MongoDB." . PHP_EOL;
    exit(1);
}

try {
    // 1. Ping
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $manager->executeCommand(MONGO_DB_NAME, $command);
    echo "✅ Ping réussi sur la base '" . MONGO_DB_NAME . "'." . PHP_EOL;

    // 2. Test d'insertion d'une statistique de commande (ECF)
    $testCommande = [
        'numero_commande'  => 'CMD-TEST-001',
        'menu_id'          => 1,
        'menu_titre'       => 'Menu Terroir Bordelais',
        'theme'            => 'Classique',
        'regime'           => 'Classique',
        'nb_personnes'     => 8,
        'prix_total'       => 304.00,
        'frais_livraison'  => 0.00,
        'ville_prestation' => 'Bordeaux',
        'statut'           => 'terminee',
        'date_commande'    => '2026-03-01 12:00:00',
    ];

    $inserted = syncCommandeStatsToMongo($testCommande);
    if ($inserted) {
        echo "✅ Insertion de test réussie dans 'statistiques_commandes'." . PHP_EOL;
    } else {
        echo "⚠️ Erreur lors de l'insertion." . PHP_EOL;
    }

    // 3. Lecture des documents
    $docs = mongoQuery('statistiques_commandes', ['numero_commande' => 'CMD-TEST-001']);
    echo "✅ Nombre de documents trouvés : " . count($docs) . PHP_EOL;
    if (!empty($docs)) {
        echo "   -> Menu : " . $docs[0]->menu_titre . " (CA : " . $docs[0]->chiffre_affaires . " €)" . PHP_EOL;
    }

    // 4. Test d'agrégation (chiffre d'affaires par menu)
    $pipeline = [
        [
            '$group' => [
                '_id'              => '$menu_titre',
                'total_commandes'  => ['$sum' => 1],
                'chiffre_affaires' => ['$sum' => '$chiffre_affaires'],
            ]
        ]
    ];
    $stats = mongoAggregate('statistiques_commandes', $pipeline);
    echo "✅ Agrégation NoSQL exécutée avec succès (" . count($stats) . " groupe(s) calculé(s))." . PHP_EOL;

    echo PHP_EOL . "🎉 MongoDB est parfaitement connecté et configuré pour l'application !" . PHP_EOL;

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . PHP_EOL;
    exit(1);
}
