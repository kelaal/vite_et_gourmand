<?php
/**
 * Script d'initialisation des bases de données Vite & Gourmand (MySQL + MongoDB)
 * Exécute sql/schema.sql, sql/fixtures.sql et synchronise MongoDB
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/mongo.php';

try {
    echo "=== Initialisation des bases de données Vite & Gourmand ===" . PHP_EOL;

    // 1. Application du schéma MySQL
    $schemaFile = __DIR__ . '/../sql/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Fichier de schéma non trouvé : $schemaFile");
    }

    echo "1. Application du schéma SQL (MySQL)..." . PHP_EOL;
    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    echo "   -> Schéma créé avec succès." . PHP_EOL;

    // 2. Insertion des fixtures MySQL
    $fixturesFile = __DIR__ . '/../sql/fixtures.sql';
    if (!file_exists($fixturesFile)) {
        throw new Exception("Fichier de fixtures non trouvé : $fixturesFile");
    }

    echo "2. Insertion du jeu d'essai SQL..." . PHP_EOL;
    $fixturesSql = file_get_contents($fixturesFile);
    $pdo->exec($fixturesSql);
    echo "   -> Fixtures MySQL insérées avec succès." . PHP_EOL;

    // 3. Synchronisation NoSQL MongoDB (statistiques_commandes)
    echo "3. Initialisation de la collection MongoDB 'statistiques_commandes'..." . PHP_EOL;
    $manager = getMongoManager();
    if ($manager) {
        // Nettoyage de la collection pour test propre
        try {
            $dropCmd = new MongoDB\Driver\Command(['drop' => 'statistiques_commandes']);
            $manager->executeCommand(MONGO_DB_NAME, $dropCmd);
        } catch (Exception $ignored) {}

        // Récupération des commandes avec menus pour alimenter MongoDB
        $stmtCmds = $pdo->query("
            SELECT c.numero_commande, c.menu_id, m.titre AS menu_titre, t.libelle AS theme, 
                   r.libelle AS regime, c.nb_personnes, c.prix_total, c.frais_livraison, 
                   c.ville_prestation, c.statut, c.date_commande, c.date_prestation
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            JOIN theme t ON m.theme_id = t.theme_id
            JOIN regime r ON m.regime_id = r.regime_id
        ");
        $commandes = $stmtCmds->fetchAll();

        $mongoSynced = 0;
        foreach ($commandes as $cmd) {
            if (syncCommandeStatsToMongo($cmd)) {
                $mongoSynced++;
            }
        }
        echo "   -> {$mongoSynced} commande(s) synchronisée(s) dans MongoDB." . PHP_EOL;
    } else {
        echo "   -> ⚠️ MongoDB non joignable, synchronisation ignorée." . PHP_EOL;
    }

    // 4. Vérifications finales
    $menusCount = $pdo->query("SELECT COUNT(*) FROM menu")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
    $avisCount  = $pdo->query("SELECT COUNT(*) FROM avis WHERE statut = 'VALIDE'")->fetchColumn();

    echo PHP_EOL . "=== Bilan Global ===" . PHP_EOL;
    echo "- Menus enregistrés (MySQL)       : $menusCount" . PHP_EOL;
    echo "- Utilisateurs créés (MySQL)      : $usersCount" . PHP_EOL;
    echo "- Avis validés (MySQL)            : $avisCount" . PHP_EOL;
    echo "- Base NoSQL                      : " . MONGO_DB_NAME . " (Collection: statistiques_commandes)" . PHP_EOL;
    echo "Tout est prêt et parfaitement synchronisé !" . PHP_EOL;

} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . PHP_EOL;
    exit(1);
}
