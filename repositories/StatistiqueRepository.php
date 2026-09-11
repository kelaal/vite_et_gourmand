<?php
/**
 * Repository d'accès aux Statistiques NoSQL (MongoDB)
 * Exigence ECF Espace Administrateur :
 *   - Nombre de commandes par menu comparables via graphique
 *   - Calcul de chiffre d'affaires par menu avec filtres de durée
 */

require_once __DIR__ . '/../config/mongo.php';

class StatistiqueRepository {

    /**
     * Calcule le nombre de commandes par menu pour affichage graphique
     * @return array
     */
    public function getNombreCommandesParMenu(): array {
        $pipeline = [
            [
                '$group' => [
                    '_id'              => '$menu_titre',
                    'menu_id'          => ['$first' => '$menu_id'],
                    'total_commandes'  => ['$sum' => 1],
                    'chiffre_affaires' => ['$sum' => '$chiffre_affaires'],
                ]
            ],
            [
                '$sort' => ['total_commandes' => -1]
            ]
        ];

        return mongoAggregate('statistiques_commandes', $pipeline);
    }

    /**
     * Calcule le Chiffre d'Affaires par menu avec filtres optionnels de date
     * 
     * @param int|null $menuId
     * @param string|null $dateDebut Format 'Y-m-d'
     * @param string|null $dateFin Format 'Y-m-d'
     * @return array
     */
    public function getChiffreAffairesDetails(?int $menuId = null, ?string $dateDebut = null, ?string $dateFin = null): array {
        $match = [];

        if (!empty($menuId)) {
            $match['menu_id'] = (int)$menuId;
        }

        if (!empty($dateDebut) || !empty($dateFin)) {
            $dateFilter = [];
            if (!empty($dateDebut)) {
                $dateFilter['$gte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateDebut . ' 00:00:00') * 1000);
            }
            if (!empty($dateFin)) {
                $dateFilter['$lte'] = new MongoDB\BSON\UTCDateTime(strtotime($dateFin . ' 23:59:59') * 1000);
            }
            $match['date_commande'] = $dateFilter;
        }

        $pipeline = [];
        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = [
            '$group' => [
                '_id'                    => '$menu_titre',
                'menu_id'                => ['$first' => '$menu_id'],
                'theme'                  => ['$first' => '$theme'],
                'regime'                 => ['$first' => '$regime'],
                'nombre_commandes'       => ['$sum' => 1],
                'chiffre_affaires'       => ['$sum' => '$chiffre_affaires'],
                'total_personnes'        => ['$sum' => '$nb_personnes'],
                'total_frais_livraison'  => ['$sum' => '$frais_livraison'],
            ]
        ];

        $pipeline[] = ['$sort' => ['chiffre_affaires' => -1]];

        return mongoAggregate('statistiques_commandes', $pipeline);
    }
}
