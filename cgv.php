<?php
/**
 * Conditions Générales de Vente - Vite & Gourmand
 * ECF : Page CGV complète conforme au droit de la consommation
 */

require_once __DIR__ . '/config/require_auth.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Conditions Générales de Vente - Vite & Gourmand, traiteur gastronomique à Bordeaux">
    <title>Conditions Générales de Vente | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'cgv';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-legal">
        <div class="legal-container">
            <!-- Hero -->
            <header class="legal-hero">
                <h1 class="legal-hero__title">Conditions Générales de Vente</h1>
                <p class="legal-hero__meta">Dernière mise à jour : <time datetime="2026-09-01">1er septembre 2026</time></p>
            </header>

            <article class="legal-content">
                <!-- Préambule -->
                <section class="legal-section">
                    <h2>Article 1 — Objet et Champ d'application</h2>
                    <p>Les présentes Conditions Générales de Vente (ci-après « CGV ») régissent l'ensemble des relations contractuelles entre la société <strong>Vite & Gourmand</strong> (ci-après « le Traiteur ») et ses clients (ci-après « le Client ») pour la fourniture de prestations de traiteur événementiel (repas, buffets, cocktails, livraison, service, location de matériel).</p>
                    <p>Toute commande passée implique l'acceptation sans réserve des présentes CGV, qui prévalent sur tout autre document du Client, sauf dérogation écrite signée par les deux parties.</p>
                </section>

                <!-- Commande -->
                <section class="legal-section">
                    <h2>Article 2 — Commande</h2>
                    <h3>2.1 Devis et confirmation</h3>
                    <p>Toute prestation fait l'objet d'un devis détaillé établi par le Traiteur. La commande est réputée ferme et définitive dès :</p>
                    <ul>
                        <li>La signature du devis (ou acceptation par email) par le Client ;</li>
                        <li>Le versement de l'acompte prévu à l'article 3.2 ;</li>
                        <li>La confirmation écrite (email) par le Traiteur.</li>
                    </ul>
                    <h3>2.2 Modification</h3>
                    <p>Toute modification de commande (nombre de convives, menu, date, horaire) doit être communiquée par écrit au minimum <strong>72 heures ouvrées</strong> avant la date de prestation. Au-delà, le Traiteur ne garantit pas la faisabilité des modifications.</p>
                    <h3>2.3 Annulation par le Client</h3>
                    <p>En cas d'annulation :</p>
                    <ul>
                        <li>Plus de 14 jours avant la prestation : remboursement intégral de l'acompte ;</li>
                        <li>Entre 7 et 14 jours : acompte conservé (30 % du montant total) ;</li>
                        <li>Moins de 7 jours : 50 % du montant total dû ;</li>
                        <li>Moins de 48 heures : 100 % du montant total dû.</li>
                    </ul>
                </section>

                <!-- Prix et Paiement -->
                <section class="legal-section">
                    <h2>Article 3 — Prix et Modalités de paiement</h2>
                    <h3>3.1 Prix</h3>
                    <p>Les prix s'entendent en euros TTC (TVA 20 % applicable aux prestations de restauration). Ils comprennent : la préparation culinaire, la livraison (selon zone), le matériel de service si prévu, et la TVA.</p>
                    <p>Les frais de livraison sont calculés selon la règle suivante :</p>
                    <ul>
                        <li><strong>Bordeaux intra-muros (codes postaux 33000, 33100, 33200, 33300, 33800) :</strong> Gratuit ;</li>
                        <li><strong>Hors Bordeaux :</strong> Forfait 5,00 € + 0,59 € par kilomètre (distance estimée depuis Bordeaux centre).</li>
                    </ul>
                    <p>Une remise de <strong>10 %</strong> est appliquée automatiquement sur le prix du menu pour toute commande portant sur un nombre de personnes supérieur ou égal au minimum requis + 5 personnes.</p>
                    <h3>3.2 Acompte</h3>
                    <p>Un acompte de <strong>30 %</strong> du montant total TTC est exigé à la commande. Le solde est payable le jour de la prestation.</p>
                    <h3>3.3 Moyens de paiement acceptés</h3>
                    <p>Espèces (appoint apprécié), carte bancaire (terminal mobile), chèque à l'ordre de « Vite & Gourmand », virement bancaire (RIB sur demande, à effectuer 48h avant la prestation).</p>
                    <h3>3.4 Retard de paiement</h3>
                    <p>Tout retard de paiement entraînera de plein droit l'application d'intérêts de retard au taux légal majoré de 5 points, ainsi qu'une indemnité forfaitaire de 40 € pour frais de recouvrement (Art. L.441-10 Code de commerce).</p>
                </section>

                <!-- Livraison et Prestation -->
                <section class="legal-section">
                    <h2>Article 4 — Livraison et Prestation</h2>
                    <h3>4.1 Délais et horaires</h3>
                    <p>Les horaires de livraison sont convenus à la commande. Le Traiteur s'engage à livrer dans un créneau de ± 30 minutes. Tout retard imputable au Client (absence, adresse erronée, accès difficile non signalé) ne saurait engager la responsabilité du Traiteur.</p>
                    <h3>4.2 Transfert des risques</h3>
                    <p>Les risques sont transférés au Client dès la remise des plats au lieu de livraison convenu. Le Client s'engage à vérifier la conformité (quantité, température, état) à la réception et à signer le bon de livraison.</p>
                    <h3>4.3 Conservation</h3>
                    <p>Le Client est responsable de la bonne conservation des plats après livraison (respect de la chaîne du froid ≤ 4°C, réchauffe selon instructions fournies). Le Traiteur décline toute responsabilité en cas de non-respect des consignes de conservation.</p>
                    <h3>4.4 Service et personnel</h3>
                    <p>Si la prestation inclut du service, le personnel reste sous la responsabilité du Traiteur. Le Client s'engage à fournir les conditions de travail décentes (accès cuisine, point d'eau, électricité).</p>
                </section>

                <!-- Matériel -->
                <section class="legal-section">
                    <h2>Article 5 — Prêt de matériel</h2>
                    <p>Le Traiteur peut mettre à disposition du matériel (chafing dishes, réchauds, nappes, vaisselle, mobilier). Ce prêt fait l'objet d'un inventaire contradictoire à la livraison et à la reprise.</p>
                    <ul>
                        <li>Le matériel reste la propriété exclusive de Vite & Gourmand ;</li>
                        <li>Le Client s'engage à le restituer complet, propre et en bon état dans un délai de <strong>10 jours ouvrés</strong> après la prestation ;</li>
                        <li>Tout matériel manquant, cassé ou dégradé sera facturé au prix de remplacement neuf ;</li>
                        <li>À défaut de restitution dans le délai, une pénalité forfaitaire de <strong>600 €</strong> sera facturée (conformément à l'article L.131-1 du Code de la consommation).</li>
                    </ul>
                </section>

                <!-- Droit de rétractation -->
                <section class="legal-section">
                    <h2>Article 6 — Droit de rétractation</h2>
                    <p>Conformément à l'article L.221-28 du Code de la consommation, le droit de rétractation de 14 jours <strong>ne s'applique pas</strong> aux prestations de services de restauration (fourniture de biens confectionnés selon les spécifications du Client ou nettement personnalisés, périssables).</p>
                    <p>Toutefois, le Client bénéficie des conditions d'annulation prévues à l'article 2.3.</p>
                </section>

                <!-- Responsabilité -->
                <section class="legal-section">
                    <h2>Article 7 — Responsabilité et Assurances</h2>
                    <h3>7.1 Responsabilité du Traiteur</h3>
                    <p>Le Traiteur s'engage à réaliser ses prestations avec soin et professionnalisme, dans le respect des normes d'hygiène (HACCP) et de la réglementation en vigueur. Sa responsabilité est limitée au montant total de la commande, hors dommages indirects (préjudice commercial, perte de chance, atteinte à l'image).</p>
                    <h3>7.2 Force majeure</h3>
                    <p>Sont considérés comme cas de force majeure : intempéries exceptionnelles, grève, épidémie, décision administrative, panne véhicule, rupture de stock fournisseur. En cas de force majeure, le Traiteur en informe le Client dans les meilleurs délais et propose une solution de remplacement ou le remboursement intégral.</p>
                    <h3>7.3 Assurances</h3>
                    <p>Le Traiteur dispose d'une assurance Responsabilité Civile Professionnelle couvrant les dommages corporels, matériels et immatériels causés à tiers dans le cadre de son activité.</p>
                </section>

                <!-- Allergènes -->
                <section class="legal-section">
                    <h2>Article 8 — Allergènes et Régimes alimentaires</h2>
                    <p>Conformément au Règlement (UE) n°1169/2011 (INCO), la liste des 14 allergènes majeurs est communiquée pour chaque plat sur la fiche menu détaillée et sur demande. Le Client s'engage à informer le Traiteur de toute allergie ou intolérance connue de ses convives <strong>au moment de la commande</strong>.</p>
                    <p>Malgré la vigilance du Traiteur, des traces croisées d'allergènes sont possibles dans les cuisines. Pour les allergies sévères (choc anaphylactique), le Client doit en informer le Traiteur qui évaluera la faisabilité d'une prestation sans contamination croisée.</p>
                </section>

                <!-- Données personnelles -->
                <section class="legal-section">
                    <h2>Article 9 — Données personnelles (RGPD)</h2>
                    <p>Les données collectées (nom, prénom, email, téléphone, adresse, historique commandes) sont traitées par Vite & Gourmand, responsable de traitement, pour :</p>
                    <ul>
                        <li>Gestion des commandes, facturation, livraison ;</li>
                        <li>Communication liée à la prestation (confirmation, suivi, avis) ;</li>
                        <li>Obligations légales (comptabilité, factures 10 ans).</li>
                    </ul>
                    <p>Base juridique : exécution du contrat (Art. 6.1.b RGPD) et intérêt légitime (Art. 6.1.f).</p>
                    <p><strong>Droits du Client :</strong> accès, rectification, effacement, limitation, portabilité, opposition, retrait du consentement. Exercice par email : <a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a>.</p>
                    <p>Durée de conservation : 3 ans après dernière commande (données clients) / 10 ans (factures).</p>
                </section>

                <!-- Propriété intellectuelle -->
                <section class="legal-section">
                    <h2>Article 10 — Propriété intellectuelle</h2>
                    <p>Tous les éléments du site (textes, photos, logos, recettes, menus, charte graphique) sont la propriété exclusive de Vite & Gourmand. Toute reproduction, même partielle, est interdite sans autorisation écrite préalable.</p>
                </section>

                <!-- Droit applicable et Litiges -->
                <section class="legal-section">
                    <h2>Article 11 — Droit applicable et Médiation</h2>
                    <p>Les présentes CGV sont soumises au droit français. En cas de litige, les parties s'engagent à rechercher une solution amiable avant toute action judiciaire.</p>
                    <p>Conformément à l'article L.612-1 du Code de la consommation, le Client peut recourir gratuitement à un médiateur de la consommation : <strong>Médiation de la Consommation & Patrimoine</strong> — <a href="https://www.mediateur-conso.fr" target="_blank" rel="noopener">mediateur-conso.fr</a>.</p>
                    <p>À défaut d'accord amiable, compétence exclusive est attribuée aux tribunaux de <strong>Bordeaux</strong>, nonobstant pluralité de défendeurs ou appel en garantie.</p>
                </section>

                <!-- Dispositions générales -->
                <section class="legal-section">
                    <h2>Article 12 — Dispositions générales</h2>
                    <ul>
                        <li>Si une clause est déclarée nulle, les autres restent applicables.</li>
                        <li>Le fait de ne pas se prévaloir d'une clause ne vaut pas renonciation.</li>
                        <li>Les CGV sont modifiables à tout moment ; la version applicable est celle en vigueur à la date de commande.</li>
                        <li>Les présentes CGV constituent l'intégralité de l'accord entre les parties.</li>
                    </ul>
                </section>

                <!-- Signature -->
                <section class="legal-section legal-signature">
                    <p><strong>Vite & Gourmand</strong> — 12 cours de l'Intendance, 33000 Bordeaux</p>
                    <p>SIRET : 123 456 789 00012 — APE : 5621Z — TVA intra : FR12 123456789</p>
                    <p>Tél. : 05 56 00 00 00 — Email : <a href="mailto:contact@viteetgourmand.fr">contact@viteetgourmand.fr</a></p>
                </section>
            </article>
        </div>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>