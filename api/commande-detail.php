<?php
/**
 * API Détail Commande - Vite & Gourmand
 * Retourne le HTML du détail d'une commande pour la modale (espace.php)
 */

session_start();
require_once __DIR__ . '/../config/require_auth.php';
require_once __DIR__ . '/../repositories/CommandeRepository.php';

// Protection : utilisateur connecté seulement
requireAuth();

$userId = getCurrentUserId();
$cmdId = (int)($_GET['id'] ?? 0);

if (!$cmdId) {
    http_response_code(400);
    echo '<div class="alert alert--error">Commande invalide.</div>';
    exit;
}

$commandeRepo = new CommandeRepository();
$commande = $commandeRepo->findById($cmdId);

// Vérification : la commande appartient à l'utilisateur
if (!$commande || $commande['utilisateur_id'] !== $userId) {
    http_response_code(403);
    echo '<div class="alert alert--error">Accès refusé à cette commande.</div>';
    exit;
}

// Récupération historique
$historique = $commandeRepo->getHistoriqueStatut($cmdId);

// Calculs pour affichage
$prixMenuTotal = (float)$commande['prix_menu_unitaire'] * (int)$commande['nb_personnes'];
$statutLabels = [
    'en attente' => 'En attente',
    'accepte' => 'Acceptée',
    'en preparation' => 'En préparation',
    'en cours de livraison' => 'En cours de livraison',
    'livre' => 'Livrée',
    'en attente du retour de materiel' => 'En attente du retour matériel',
    'terminee' => 'Terminée',
    'annulee' => 'Annulée'
];
?>
<div class="commande-detail">
    <!-- Infos principales -->
    <div class="commande-detail__section">
        <h3>Informations de la commande</h3>
        <div class="commande-detail__row">
            <span class="label">Numéro :</span>
            <span class="value"><?= htmlspecialchars($commande['numero_commande']) ?></span>
        </div>
        <div class="commande-detail__row">
            <span class="label">Date de commande :</span>
            <span class="value"><?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></span>
        </div>
        <div class="commande-detail__row">
            <span class="label">Statut actuel :</span>
            <span class="value">
                <span class="badge badge--statut badge--<?= strtolower(str_replace(' ', '-', $commande['statut'])) ?>">
                    <?= $statutLabels[$commande['statut']] ?? htmlspecialchars(ucfirst($commande['statut'])) ?>
                </span>
            </span>
        </div>
        <div class="commande-detail__row">
            <span class="label">Date de prestation :</span>
            <span class="value"><?= date('d/m/Y', strtotime($commande['date_prestation'])) ?></span>
        </div>
        <div class="commande-detail__row">
            <span class="label">Heure :</span>
            <span class="value"><?= substr($commande['heure_prestation'], 0, 5) ?></span>
        </div>
        <div class="commande-detail__row">
            <span class="label">Adresse :</span>
            <span class="value"><?= htmlspecialchars($commande['adresse_prestation']) ?>, <?= htmlspecialchars($commande['ville_prestation']) ?></span>
        </div>
        <?php if ((float)$commande['distance_km'] > 0): ?>
            <div class="commande-detail__row">
                <span class="label">Distance :</span>
                <span class="value"><?= number_format((float)$commande['distance_km'], 1, ',', ' ') ?> km</span>
            </div>
        <?php endif; ?>
        <div class="commande-detail__row">
            <span class="label">Nombre de personnes :</span>
            <span class="value"><?= (int)$commande['nb_personnes'] ?></span>
        </div>
        <?php if ($commande['pret_materiel']): ?>
            <div class="commande-detail__row">
                <span class="label">Matériel prêté :</span>
                <span class="value"><span class="text-warning">⚠️ Oui (retour sous 10 jours ouvrés)</span></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Menu commandé -->
    <div class="commande-detail__section">
        <h3>Menu : <?= htmlspecialchars($commande['menu_titre']) ?></h3>
        <div class="commande-detail__row">
            <span class="label">Prix unitaire :</span>
            <span class="value"><?= number_format((float)$commande['prix_menu_unitaire'], 2, ',', ' ') ?> €</span>
        </div>
        <div class="commande-detail__row">
            <span class="label">Quantité :</span>
            <span class="value"><?= (int)$commande['nb_personnes'] ?> personne(s)</span>
        </div>
    </div>

    <!-- Détail tarification -->
    <div class="commande-detail__section">
        <h3>Détail de la facturation</h3>
        <div class="commande-detail__pricing">
            <div class="commande-detail__price-line">
                <span>Menu (<?= (int)$commande['nb_personnes'] ?> × <?= number_format((float)$commande['prix_menu_unitaire'], 2, ',', ' ') ?> €)</span>
                <span><?= number_format($prixMenuTotal, 2, ',', ' ') ?> €</span>
            </div>
            <?php if ((float)$commande['remise_appliquee'] > 0): ?>
                <div class="commande-detail__price-line remise">
                    <span>Remise 10% (<?= (int)$commande['nb_personnes'] ?> ≥ min + 5)</span>
                    <span>- <?= number_format((float)$commande['remise_appliquee'], 2, ',', ' ') ?> €</span>
                </div>
            <?php endif; ?>
            <?php if ((float)$commande['frais_livraison'] > 0): ?>
                <div class="commande-detail__price-line frais">
                    <span>Frais de livraison (<?= htmlspecialchars($commande['ville_prestation']) ?> - <?= number_format((float)$commande['distance_km'], 1, ',', ' ') ?> km)</span>
                    <span><?= number_format((float)$commande['frais_livraison'], 2, ',', ' ') ?> €</span>
                </div>
            <?php else: ?>
                <div class="commande-detail__price-line frais offert">
                    <span>Frais de livraison (Bordeaux intra-muros)</span>
                    <span class="text-success">Offerts</span>
                </div>
            <?php endif; ?>
            <div class="commande-detail__price-line total">
                <span>Total TTC</span>
                <span><?= number_format((float)$commande['prix_total'], 2, ',', ' ') ?> €</span>
            </div>
        </div>
    </div>

    <!-- Historique des statuts -->
    <div class="commande-detail__section">
        <h3>Suivi de la commande</h3>
        <div class="commande-detail__history">
            <?php foreach ($historique as $index => $h): ?>
                <div class="commande-detail__history-item">
                    <div class="commande-detail__history-dot"></div>
                    <div class="commande-detail__history-content">
                        <div class="commande-detail__history-status">
                            <?= $statutLabels[$h['statut']] ?? htmlspecialchars(ucfirst($h['statut'])) ?>
                        </div>
                        <div class="commande-detail__history-meta">
                            Le <?= date('d/m/Y à H:i', strtotime($h['date_changement'])) ?>
                            <?php if ($h['auteur_prenom']): ?>
                                par <?= htmlspecialchars($h['auteur_prenom']) ?> (<?= htmlspecialchars(ucfirst($h['auteur_role'] ?? 'client')) ?>)
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>