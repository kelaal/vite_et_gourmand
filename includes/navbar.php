<?php
/**
 * Composant Navbar réutilisable pour toutes les pages de Vite & Gourmand
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = !empty($_SESSION['utilisateur_id']);
$userRole   = $_SESSION['role'] ?? 'utilisateur';
$userPrenom = $_SESSION['prenom'] ?? '';
$currentPage = $activePage ?? basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- BARRE DE NAVIGATION FIXE GLOBALE -->
<nav class="site_navbar" id="siteNavbar" aria-label="Navigation principale">
    <div class="site_navbar__container">
        
        <!-- Logo de la marque -->
        <div class="site_navbar__logo">
            <a href="index.php" aria-label="Retour à l'accueil">
                <img src="public/img/logo.svg" alt="Vite & Gourmand" width="145" height="40">
            </a>
        </div>

        <!-- Bouton hamburger pour mobile -->
        <button class="site_navbar__toggle" id="menuToggle" aria-label="Ouvrir le menu de navigation" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Liens centraux de navigation -->
        <div class="site_navbar__menu" id="navMenu">
            <ul>
                <li>
                    <a href="index.php" class="<?= ($currentPage === 'index' || $currentPage === 'accueil') ? 'is-active' : '' ?>">Accueil</a>
                </li>
                <li>
                    <a href="menus.php" class="<?= ($currentPage === 'menus' || $currentPage === 'menu-detail') ? 'is-active' : '' ?>">Nos Menus</a>
                </li>
                <li>
                    <a href="contact.php" class="<?= ($currentPage === 'contact') ? 'is-active' : '' ?>">Contact</a>
                </li>
            </ul>
        </div>

        <!-- Espace Actions & Authentification à droite -->
        <div class="site_navbar__actions">
            <?php if ($isLoggedIn): ?>
                <div class="site_navbar__user">
                    <a href="espace.php" class="user_badge">
                        <span class="user_icon">👤</span>
                        <span class="user_label">Mon Espace (<?= htmlspecialchars(ucfirst($userRole)) ?>)</span>
                    </a>
                    <a href="actions/deconnexion.php" class="btn-nav-logout" title="Se déconnecter">Déconnexion</a>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="btn-nav-login <?= ($currentPage === 'connexion') ? 'is-active' : '' ?>">Connexion</a>
                <a href="menus.php" class="btn-nav-cta">Commander</a>
            <?php endif; ?>
        </div>

    </div>
</nav>

<!-- Script d'interaction et d'effet de scroll sticky -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar    = document.getElementById('siteNavbar');
        const toggleBtn = document.getElementById('menuToggle');
        const navMenu   = document.getElementById('navMenu');

        // Gestion du menu hamburger mobile
        if (toggleBtn && navMenu) {
            toggleBtn.addEventListener('click', function() {
                const isOpen = navMenu.classList.toggle('is-open');
                toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            // Fermer le menu lors d'un clic en dehors
            document.addEventListener('click', function(e) {
                if (!navbar.contains(e.target) && navMenu.classList.contains('is-open')) {
                    navMenu.classList.remove('is-open');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Effet de scroll compact et ombré sur la navbar
        window.addEventListener('scroll', function() {
            if (window.scrollY > 30) {
                navbar.classList.add('is-scrolled');
            } else {
                navbar.classList.remove('is-scrolled');
            }
        });
    });
</script>
