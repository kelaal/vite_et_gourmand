<?php
/**
 * Mentions Légales - Vite & Gourmand
 * ECF : Page mentions légales conforme à la LCEN (Loi pour la Confiance dans l'Économie Numérique)
 */

require_once __DIR__ . '/config/require_auth.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mentions Légales - Vite & Gourmand, traiteur gastronomique à Bordeaux">
    <title>Mentions Légales | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'mentions-legales';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-legal">
        <div class="legal-container">
            <!-- Hero -->
            <header class="legal-hero">
                <h1 class="legal-hero__title">Mentions Légales</h1>
                <p class="legal-hero__meta">Dernière mise à jour : <time datetime="2026-09-01">1er septembre 2026</time></p>
            </header>

            <article class="legal-content">
                <!-- Éditeur -->
                <section class="legal-section">
                    <h2>1. Éditeur du site</h2>
                    <dl class="legal-dl">
                        <dt>Dénomination sociale :</dt>
                        <dd>Vite & Gourmand</dd>

                        <dt>Forme juridique :</dt>
                        <dd>Société à Responsabilité Limitée (SARL) au capital de 10 000 €</dd>

                        <dt>Siège social :</dt>
                        <dd>12 cours de l'Intendance, 33000 Bordeaux, France</dd>

                        <dt>SIREN :</dt>
                        <dd>123 456 789</dd>

                        <dt>SIRET (siège) :</dt>
                        <dd>123 456 789 00012</dd>

                        <dt>Code APE / NAF :</dt>
                        <dd>5621Z — Services des traiteurs</dd>

                        <dt>N° TVA intracommunautaire :</dt>
                        <dd>FR12 123 456 789</dd>

                        <dt>RCS :</dt>
                        <dd>Bordeaux</dd>

                        <dt>Téléphone :</dt>
                        <dd><a href="tel:+33556000000">05 56 00 00 00</a></dd>

                        <dt>Email :</dt>
                        <dd><a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a></dd>

                        <dt>Directeur de la publication :</dt>
                        <dd>José Administrateur (Gérant)</dd>
                    </dl>
                </section>

                <!-- Hébergeur -->
                <section class="legal-section">
                    <h2>2. Hébergeur du site</h2>
                    <dl class="legal-dl">
                        <dt>Société :</dt>
                        <dd>OVHcloud</dd>

                        <dt>Adresse :</dt>
                        <dd>2 rue Kellermann, 59100 Roubaix, France</dd>

                        <dt>Téléphone :</dt>
                        <dd>1007 (service gratuit + prix appel)</dd>

                        <dt>Site web :</dt>
                        <dd><a href="https://www.ovhcloud.com" target="_blank" rel="noopener">ovhcloud.com</a></dd>
                    </dl>
                </section>

                <!-- Propriété intellectuelle -->
                <section class="legal-section">
                    <h2>3. Propriété intellectuelle</h2>
                    <p>L'ensemble de ce site (structure, design, textes, images, photographies, logos, icônes, recettes, fiches menus, charte graphique, code source) est la propriété exclusive de <strong>Vite & Gourmand</strong> ou fait l'objet d'une autorisation d'utilisation.</p>
                    <p>Toute représentation, reproduction, adaptation, traduction, exploitation partielle ou totale, par quelque procédé que ce soit, sans l'autorisation écrite préalable de Vite & Gourmand, est interdite et constitue une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.</p>
                    <p>Les marques « Vite & Gourmand », le logo, les noms de menus, les visuels culinaires sont des marques déposées ou en cours de dépôt. Leur utilisation sans accord écrit est prohibée.</p>
                    <p>Les photographies des plats et de l'équipe sont réalisées par Vite & Gourmand ou sous licence. Crédits photos : Vite & Gourmand / Photographes partenaires.</p>
                </section>

                <!-- Données personnelles -->
                <section class="legal-section">
                    <h2>4. Protection des données personnelles (RGPD)</h2>
                    <h3>4.1 Responsable de traitement</h3>
                    <p>Vite & Gourmand, 12 cours de l'Intendance, 33000 Bordeaux — <a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a></p>

                    <h3>4.2 Données collectées</h3>
                    <ul>
                        <li>Identité : nom, prénom, email, téléphone ;</li>
                        <li>Adresse : postale, livraison ;</li>
                        <li>Commandes : historique, menus, montants, statuts ;</li>
                        <li>Avis : note, commentaire, statut modération ;</li>
                        <li>Connexion : IP, logs, tokens (session, reset MDP) ;</li>
                        <li>Navigation : cookies techniques, analytiques (voir §5).</li>
                    </ul>

                    <h3>4.3 Finalités et bases légales</h3>
                    <table class="legal-table">
                        <thead>
                            <tr><th>Finalité</th><th>Base légale (RGPD Art. 6)</th><th>Durée conservation</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Gestion commandes, facturation, livraison</td><td>Exécution contrat (6.1.b)</td><td>3 ans après dernière commande</td></tr>
                            <tr><td>Compte client, authentification</td><td>Exécution contrat (6.1.b)</td><td>Durée vie compte + 3 ans inactivité</td></tr>
                            <tr><td>Avis clients (modération, affichage)</td><td>Intérêt légitime (6.1.f)</td><td>3 ans après dépôt</td></tr>
                            <tr><td>Newsletter, prospection commerciale</td><td>Consentement (6.1.a)</td><td>Jusqu'au retrait consentement</td></tr>
                            <tr><td>Obligations comptables/fiscales</td><td>Obligation légale (6.1.c)</td><td>10 ans (factures)</td></tr>
                            <tr><td>Sécurité, prévention fraude</td><td>Intérêt légitime (6.1.f)</td><td>13 mois (logs)</td></tr>
                        </tbody>
                    </table>

                    <h3>4.4 Destinataires</h3>
                    <p>Données accessibles en interne (équipe Vite & Gourmand) et transmises aux sous-traitants strictement nécessaires : hébergeur (OVHcloud), prestataire email, autorité judiciaire sur réquisition.</p>
                    <p>Aucun transfert hors UE n'est effectué.</p>

                    <h3>4.5 Vos droits</h3>
                    <p>Conformément aux articles 15 à 22 du RGPD, vous disposez des droits :</p>
                    <ul>
                        <li><strong>Accès</strong> (Art. 15) : obtenir copie de vos données ;</li>
                        <li><strong>Rectification</strong> (Art. 16) : corriger données inexactes ;</li>
                        <li><strong>Effacement</strong> (Art. 17) : « droit à l'oubli » sous conditions ;</li>
                        <li><strong>Limitation</strong> (Art. 18) : geler l'usage ;</li>
                        <li><strong>Portabilité</strong> (Art. 20) : récupérer vos données format structuré ;</li>
                        <li><strong>Opposition</strong> (Art. 21) : au traitement pour intérêt légitime ;</li>
                        <li><strong>Retrait consentement</strong> (Art. 7.3) : à tout moment pour prospection.</li>
                    </ul>
                    <p>Exercice : email <a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a> ou courrier postal au siège. Réponse sous 1 mois (prorogeable 2 mois).</p>

                    <h3>4.6 DPO / Réclamation</h3>
                    <p>Pas de DPO désigné (non obligatoire). En cas de litige, vous pouvez saisir la <strong>CNIL</strong> : <a href="https://www.cnil.fr" target="_blank" rel="noopener">cnil.fr</a> — 3 Place de Fontenoy, 75007 Paris.</p>
                </section>

                <!-- Cookies -->
                <section class="legal-section">
                    <h2>5. Cookies et traceurs</h2>
                    <h3>5.1 Définition</h3>
                    <p>Un cookie est un fichier texte déposé sur votre terminal (ordinateur, tablette, smartphone) lors de la consultation du site.</p>

                    <h3>5.2 Cookies utilisés</h3>
                    <table class="legal-table">
                        <thead>
                            <tr><th>Nom / Type</th><th>Finalité</th><th>Durée</th><th>Consentement</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Session PHP (PHPSESSID)</td><td>Authentification, panier, formulaire</td><td>Session</td><td>Non (strictement nécessaire)</td></tr>
                            <tr><td>CSRF Token</td><td>Protection formulaires</td><td>Session</td><td>Non (strictement nécessaire)</td></tr>
                            <tr><td>Remember me</td><td>Connexion automatique 30 jours</td><td>30 jours</td><td>Oui (case à cocher)</td></tr>
                            <tr><td>Google Fonts (cache navigateur)</td><td>Affichage polices Playfair Display, Plus Jakarta Sans</td><td>1 an</td><td>Non (intérêt légitime)</td></tr>
                        </tbody>
                    </table>

                    <h3>5.3 Gestion des cookies</h3>
                    <p>Vous pouvez configurer votre navigateur pour refuser/supprimer les cookies :</p>
                    <ul>
                        <li>Chrome : <code>chrome://settings/cookies</code></li>
                        <li>Firefox : <code>about:preferences#privacy</code></li>
                        <li>Safari : Préférences > Confidentialité</li>
                        <li>Edge : <code>edge://settings/content/cookies</code></li>
                    </ul>
                    <p>Le refus des cookies techniques peut empêcher l'accès à l'espace client et le passage de commande.</p>
                </section>

                <!-- Responsabilité -->
                <section class="legal-section">
                    <h2>6. Responsabilité</h2>
                    <p>Vite & Gourmand s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur le site. Toutefois, des erreurs ou omissions peuvent survenir. Vite & Gourmand ne saurait être tenu responsable :</p>
                    <ul>
                        <li>De l'indisponibilité temporaire du site (maintenance, incident technique) ;</li>
                        <li>De dommages directs ou indirects résultant de l'utilisation du site ;</li>
                        <li>Des contenus des sites tiers accessibles via liens hypertextes.</li>
                    </ul>
                    <p>Le site est accessible 24h/24, 7j/7, sous réserve d'interruptions pour maintenance.</p>
                </section>

                <!-- Droit applicable -->
                <section class="legal-section">
                    <h2>7. Droit applicable et Juridiction</h2>
                    <p>Les présentes mentions légales sont régies par le droit français. Tout litige relatif à l'interprétation ou l'exécution sera soumis à la compétence exclusive des tribunaux de <strong>Bordeaux</strong>.</p>
                </section>

                <!-- Contact -->
                <section class="legal-section">
                    <h2>8. Contact</h2>
                    <p>Pour toute question relative aux mentions légales, à la protection des données ou à l'exercice de vos droits :</p>
                    <dl class="legal-dl">
                        <dt>Email :</dt>
                        <dd><a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a></dd>

                        <dt>Courrier :</dt>
                        <dd>Vite & Gourmand — À l'attention du Délégué à la Protection des Données<br>12 cours de l'Intendance, 33000 Bordeaux</dd>
                    </dl>
                </section>
            </article>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>