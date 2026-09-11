<?php
/**
 * Composant Footer réutilisable pour toutes les pages de Vite & Gourmand
 */

// Détecter le chemin de base selon l'emplacement du script
$basePath = '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
if (str_contains($scriptName, '/employe/') || str_contains($scriptName, '/admin/')) {
    $basePath = '../';
}

if (!isset($horaires) && isset($pdo)) {
    try {
        $stmtHoraires = $pdo->query("SELECT * FROM horaire ORDER BY ordre_jour ASC");
        $horaires = $stmtHoraires->fetchAll();
    } catch (Exception $e) {
        $horaires = [];
    }
}
?>
<!-- FOOTER GLOBAL -->
<footer class="footer">
    <div class="footer__container">

        <!-- Colonne 1 : Brand & Présentation -->
        <div class="footer__brand">
            <img src="<?= $basePath ?>public/img/logo.svg" alt="Vite & Gourmand" class="footer__logo" width="150" height="48">
            <p>L'excellence gastronomique à Bordeaux. Traiteur d'exception sur-mesure pour vos mariages, événements professionnels et moments précieux.</p>
        </div>

        <!-- Colonne 2 : Navigation -->
        <div class="footer__nav">
            <h3>Navigation</h3>
            <ul>
                <li><a href="<?= $basePath ?>index.php">Accueil</a></li>
                <li><a href="<?= $basePath ?>menus.php">Nos Menus</a></li>
                <li><a href="<?= $basePath ?>contact.php">Contact</a></li>
                <li><a href="<?= $basePath ?>connexion.php">Espace Client / Équipe</a></li>
            </ul>
        </div>

        <!-- Colonne 3 : Coordonnées -->
        <div class="footer__contact">
            <h3>Contact</h3>
            <p>📍 12 cours de l'Intendance, 33000 Bordeaux</p>
            <p>📞 05 56 00 00 00</p>
            <p>✉️ contact@viteetgourmand.fr</p>
            <p style="margin-top: 10px; color: #ffca28; font-size: 0.85rem;">🚚 Livraison gratuite sur Bordeaux intra-muros</p>
        </div>

        <!-- Colonne 4 : Horaires d'ouverture dynamiques -->
        <div class="footer__hours">
            <h3>Horaires d'ouverture</h3>
            <ul>
                <?php if (!empty($horaires)): ?>
                    <?php foreach ($horaires as $h): ?>
                        <li>
                            <span class="day-name"><?= htmlspecialchars($h['jour_semaine']) ?></span>
                            <?php if ($h['est_ouvert'] && !empty($h['heure_ouverture'])): ?>
                                <span class="day-time">
                                    <?= substr($h['heure_ouverture'], 0, 5) ?> - <?= substr($h['heure_fermeture'], 0, 5) ?>
                                </span>
                            <?php else: ?>
                                <span class="day-closed">Sur réservation</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><span>Lundi - Samedi :</span> <span>08:00 - 20:00</span></li>
                    <li><span>Dimanche :</span> <span>Sur réservation</span></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <!-- Mentions & Copyright -->
    <div class="footer__bottom">
        <p>&copy; <?= date('Y') ?> Vite & Gourmand — Tous droits réservés.</p>
        <div class="footer__links">
            <a href="<?= $basePath ?>mentions-legales.php">Mentions Légales</a>
            <span>|</span>
            <a href="<?= $basePath ?>cgv.php">CGV</a>
            <span>|</span>
            <a href="<?= $basePath ?>contact.php">Plan d'accès</a>
        </div>
    </div>
</footer>
