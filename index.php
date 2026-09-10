<?php
// Démarrage de la session utilisateur
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Inclusion des Repositories d'accès aux données
require_once __DIR__ . '/repositories/MenuRepository.php';
require_once __DIR__ . '/repositories/AvisRepository.php';

$menuRepo = new MenuRepository();
$avisRepo = new AvisRepository();

// 1. Récupération des avis clients validés via le Repository
$avisClients = $avisRepo->findValides(3);

// 2. Récupération des menus à l'affiche (vitrine) via le Repository
$menusVitrine = $menuRepo->findVitrine(3);

// Vérification de message flash (ex: dépôt d'avis)
$avisSuccess = isset($_GET['avis']) && $_GET['avis'] === 'succes';
$isLoggedIn  = !empty($_SESSION['utilisateur_id']);
$userRole    = $_SESSION['role'] ?? 'utilisateur';
$userPrenom  = $_SESSION['prenom'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Vite & Gourmand - Service traiteur gastronomique sur-mesure à Bordeaux depuis 25 ans. Menus de saison, événements et réceptions privées.">
  <title>Vite & Gourmand | Traiteur Gastronomique à Bordeaux</title>
  
  <!-- Polices Google Fonts (Playfair Display & Plus Jakarta Sans) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
  <!-- BARRE DE NAVIGATION GLOBALE FIXE -->
  <?php 
  $activePage = 'accueil';
  require_once __DIR__ . '/includes/navbar.php'; 
  ?>

  <!-- HEADER & HERO SECTION -->
  <header class="hero">
    <div class="hero_section__img">
      <picture>
        <source srcset="public/img/hero-bg.avif" type="image/avif">
        <source srcset="public/img/hero-bg.webp" type="image/webp">
        <img src="public/img/hero-bg.jpeg" alt="Gastronomie traiteur Vite et Gourmand à Bordeaux" width="1920" height="1080">
      </picture>
    </div>

    <div class="hero_section__content">
      <span class="hero_section__badge">Traiteur d'Excellence à Bordeaux</span>
      <h1>L'excellence gastronomique, <span>le temps d'un instant</span></h1>
      <p>Découvrez une expérience culinaire où la rapidité du service rencontre l'artisanat du goût. Pour vos réceptions privées et événements professionnels.</p>
      <div class="hero_section__actions">
        <a href="#menus" class="btn-hero-primary">
          <span>Découvrir nos créations</span> →
        </a>
        <a href="#presentation" class="btn-hero-secondary">Notre Savoir-faire</a>
      </div>
    </div>
  </header>

  <main>
    <!-- SECTION PRÉSENTATION -->
    <section class="presentation" id="presentation">
      <div class="presentation__container">
        <div class="presentation__img_wrapper">
          <div class="presentation__img">
            <picture>
              <source srcset="public/img/presentation-photo.avif" type="image/avif">
              <source srcset="public/img/presentation-photo.webp" type="image/webp">
              <img src="public/img/presentation-photo.jpeg" alt="Julie et José - Chefs traiteurs Vite & Gourmand" width="1200" height="800">
            </picture>
          </div>
          <div class="presentation__badge_floating">
            <span class="icon">⚜️</span>
            <span class="text">Maison Bordelaise</span>
          </div>
        </div>

        <div class="presentation__texte">
          <span class="presentation__subtitle">Artisans du Goût & Passion</span>
          <h2>25 ans de passion au service de l'excellence</h2>
          <p class="lead">Dirigé par les chefs passionnés <strong>Julie et José</strong>, Vite & Gourmand est une institution bordelaise dédiée à l'art culinaire et à l'organisation de réceptions sur-mesure.</p>
          <p>Notre philosophie repose sur l'authenticité du produit et le respect du goût. Nous sourçons minutieusement tous nos ingrédients auprès d'éleveurs et de maraîchers locaux en circuits courts pour garantir fraîcheur et saveurs à chacune de vos bouchées.</p>
          
          <div class="presentation__highlights">
            <div class="presentation__stat">
              <span class="presentation__stat_number">25+</span>
              <span class="presentation__stat_label">Ans d'Excellence</span>
            </div>
            <div class="presentation__stat">
              <span class="presentation__stat_number">100%</span>
              <span class="presentation__stat_label">Fait Maison & Local</span>
            </div>
            <div class="presentation__stat">
              <span class="presentation__stat_number">Bordeaux</span>
              <span class="presentation__stat_label">& CUB Métropole</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION EXPERTISE / NOTRE SAVOIR-FAIRE -->
    <section class="expertise" id="expertise">
      <div class="expertise__container">
        <div class="expertise__header">
          <span class="expertise__subtitle">Nos Engagements d'Excellence</span>
          <h2 class="expertise__title">Notre Savoir-Faire</h2>
          <p class="expertise__lead">Depuis 25 ans, nous allions passion gastronomique et rigueur artisanale pour faire de chacun de vos événements un moment d'exception.</p>
        </div>

        <div class="expertise__grid">
          <div class="expertise__card">
            <div class="expertise__icon_wrapper">
              <img src="public/img/qualite.svg" alt="Icône Qualité" width="38" height="38">
            </div>
            <h3>Qualité Supérieure</h3>
            <p>Produits frais, nobles et de saison rigoureusement sélectionnés en circuits courts auprès de producteurs régionaux.</p>
          </div>

          <div class="expertise__card">
            <div class="expertise__icon_wrapper">
              <img src="public/img/savoir-faire.svg" alt="Icône Savoir-faire" width="38" height="38">
            </div>
            <h3>Savoir-Faire Artisanal</h3>
            <p>Un quart de siècle d'exigence gastronomique bordelaise pour sublimer chaque réception avec créativité et maîtrise.</p>
          </div>

          <div class="expertise__card">
            <div class="expertise__icon_wrapper">
              <img src="public/img/sur-mesure.svg" alt="Icône Sur mesure" width="38" height="38">
            </div>
            <h3>Prestations Sur-Mesure</h3>
            <p>Des formules adaptées à vos envies, régimes spécifiques (végétarien, vegan, sans gluten) et exigences de réception.</p>
          </div>

          <div class="expertise__card">
            <div class="expertise__icon_wrapper">
              <img src="public/img/reactivite.svg" alt="Icône Réactivité" width="38" height="38">
            </div>
            <h3>Réactivité & Rigueur</h3>
            <p>Une logistique maîtrisée et une équipe dédiée pour une livraison soignée et un respect absolu de vos horaires.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION MENUS EN VEDETTE -->
    <section class="menus_section" id="menus">
      <div class="menus_section__header">
        <span class="menus_section__subtitle">Carte & Menus</span>
        <h2 class="menus_section__title">Nos Menus d'Exception</h2>
        <p class="menus_section__lead">Des compositions gourmandes pensées pour émerveiller vos convives, disponibles à la commande dès aujourd'hui.</p>
      </div>

      <div class="menus_grid">
        <?php if (!empty($menusVitrine)): ?>
          <?php foreach ($menusVitrine as $menu): ?>
            <article class="menu_card">
              <div class="menu_card__img_wrapper">
                <img src="<?= htmlspecialchars($menu['image_url'] ?? 'public/img/presentation-photo.webp') ?>" alt="<?= htmlspecialchars($menu['titre']) ?>" loading="lazy">
                <div class="menu_card__badges">
                  <span class="badge badge--theme"><?= htmlspecialchars($menu['theme_libelle']) ?></span>
                  <span class="badge badge--regime"><?= htmlspecialchars($menu['regime_libelle']) ?></span>
                </div>
              </div>

              <div class="menu_card__content">
                <h3 class="menu_card__title"><?= htmlspecialchars($menu['titre']) ?></h3>
                <p class="menu_card__description"><?= htmlspecialchars($menu['description']) ?></p>
                
                <div class="menu_card__info">
                  <span class="menu_card__personnes">
                    👥 Min. <?= (int)$menu['nb_personne_min'] ?> personnes
                  </span>
                  <span class="menu_card__price">
                    <?= number_format((float)$menu['prix_base_min'], 2, ',', ' ') ?> € <small>/ pers.</small>
                  </span>
                </div>

                <div class="menu_card__cta">
                  <a href="menu-detail.php?id=<?= (int)$menu['menu_id'] ?>" class="btn-menu-outline">Détails</a>
                  <a href="commander.php?menu_id=<?= (int)$menu['menu_id'] ?>" class="btn-menu-primary">Commander</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; grid-column: 1/-1;">Aucun menu disponible pour le moment.</p>
        <?php endif; ?>
      </div>

      <div class="menus_section__viewall">
        <a href="menus.php" class="btn-viewall">Découvrir l'ensemble de notre carte →</a>
      </div>
    </section>

    <!-- SECTION AVIS CLIENTS -->
    <section class="avis" id="avis">
      <div class="avis__header">
        <span class="avis__subtitle">Témoignages</span>
        <h2 class="avis__title">Ils nous font confiance</h2>
      </div>

      <?php if ($avisSuccess): ?>
        <div class="alert alert--success" role="alert">
          <span>✨ <strong>Merci !</strong> Votre avis a bien été enregistré et sera affiché après validation par notre équipe.</span>
          <button type="button" class="alert__close" onclick="this.parentElement.remove();" aria-label="Fermer">✕</button>
        </div>
      <?php endif; ?>

      <!-- Liste des avis validés -->
      <div class="avis__container">
        <?php if (!empty($avisClients)): ?>
          <?php foreach ($avisClients as $avis): ?>
            <div class="avis__carte">
              <span class="avis__quote_icon" aria-hidden="true">“</span>
              <div class="avis__etoiles" aria-label="Note de <?= (int)$avis['note'] ?> sur 5">
                <?= str_repeat('★', (int)$avis['note']) ?><?= str_repeat('☆', 5 - (int)$avis['note']) ?>
              </div>
              <p class="avis__commentaire">"<?= htmlspecialchars($avis['commentaire']) ?>"</p>
              
              <div class="avis__client_info">
                <div class="avis__avatar">
                  <?= htmlspecialchars(mb_substr($avis['prenom'], 0, 1)) ?>
                </div>
                <div>
                  <div class="avis__client_name"><?= htmlspecialchars($avis['prenom']) ?></div>
                  <div class="avis__client_tag">Client vérifié</div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; grid-column: 1/-1;">Aucun avis publié pour le moment.</p>
        <?php endif; ?>
      </div>

      <!-- Formulaire de dépôt d'avis (Accessible uniquement aux utilisateurs connectés) -->
      <?php if ($isLoggedIn): ?>
        <div class="avis__form-wrapper">
          <h3>Partagez votre expérience</h3>
          <p>Votre retour est précieux pour nous aider à parfaire nos prestations.</p>

          <div class="avis__user_posting">
            <span>✍️ Vous publiez en tant que : <strong><?= htmlspecialchars($userPrenom ?: 'Client') ?></strong></span>
          </div>
          
          <form action="actions/ajouter_avis.php" method="POST" class="avis__form">
            <div class="form-group">
              <label for="note">Votre appréciation :</label>
              <select name="note" id="note" required>
                <option value="5">★★★★★ — Excellent (5/5)</option>
                <option value="4">★★★★☆ — Très bon (4/5)</option>
                <option value="3">★★★☆☆ — Bon (3/5)</option>
                <option value="2">★★☆☆☆ — Moyen (2/5)</option>
                <option value="1">★☆☆☆☆ — Décevant (1/5)</option>
              </select>
            </div>

            <div class="form-group">
              <label for="commentaire">Votre commentaire :</label>
              <textarea name="commentaire" id="commentaire" rows="4" placeholder="Racontez-nous votre événement avec Vite & Gourmand..." required></textarea>
            </div>

            <button type="submit" class="btn-avis-submit">Publier mon avis</button>
          </form>
        </div>
      <?php else: ?>
        <div class="avis__auth_box">
          <div class="avis__auth_icon">🔒</div>
          <h3>Vous avez commandé chez Vite & Gourmand ?</h3>
          <p>Connectez-vous à votre espace client pour partager votre expérience et attribuer une note à votre prestation traiteur.</p>
          <div class="avis__auth_actions">
            <a href="connexion.php?redirect=index.php#avis" class="btn-avis-login">Se connecter pour donner mon avis</a>
            <a href="inscription.php" class="btn-avis-signup">Créer un compte</a>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <!-- FOOTER GLOBAL -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>