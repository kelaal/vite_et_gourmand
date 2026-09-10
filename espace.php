<?php
/**
 * Espace Client - Vite & Gourmand
 * ECF : Historique commandes, détail, modification profil, annulation, dépôt avis
 */

require_once __DIR__ . '/config/require_auth.php';
require_once __DIR__ . '/repositories/CommandeRepository.php';
require_once __DIR__ . '/repositories/UtilisateurRepository.php';
require_once __DIR__ . '/repositories/AvisRepository.php';

// Protection : uniquement utilisateurs connectés
requireAuth();

// Récupération données utilisateur
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();
$userPrenom = getCurrentUserPrenom();

$commandeRepo = new CommandeRepository();
$userRepo = new UtilisateurRepository();
$avisRepo = new AvisRepository();

// Récupération commandes de l'utilisateur
$commandes = $commandeRepo->findByUser($userId);

// Récupération infos utilisateur pour profil
$user = $userRepo->findById($userId);

// Traitement actions POST (annulation, modification profil)
$action = $_POST['action'] ?? '';
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    switch ($action) {
        case 'annuler_commande':
            $cmdId = (int)($_POST['commande_id'] ?? 0);
            $commande = $commandeRepo->findById($cmdId);
            if ($commande && $commande['utilisateur_id'] === $userId) {
                // Vérification : annulation possible si statut != 'accepte'
                $statutsNonAnnulables = ['accepte', 'en preparation', 'en cours de livraison', 'livre', 'terminee'];
                if (!in_array($commande['statut'], $statutsNonAnnulables, true)) {
                    // Pour l'instant, simple mise à jour statut (version complète nécessiterait motif + contact)
                    $stmt = $commandeRepo->getPdo()->prepare("
                        UPDATE commande SET statut = 'annulee' WHERE commande_id = :id
                    ");
                    $stmt->execute([':id' => $cmdId]);
                    // Historique
                    $stmtHist = $commandeRepo->getPdo()->prepare("
                        INSERT INTO commande_historique_statut (commande_id, statut, modifie_par_id) VALUES (:id, 'annulee', :uid)
                    ");
                    $stmtHist->execute([':id' => $cmdId, ':uid' => $userId]);
                    $messages['success'] = 'Commande annulée avec succès.';
                    // Rafraîchir la liste
                    $commandes = $commandeRepo->findByUser($userId);
                } else {
                    $messages['error'] = 'Cette commande ne peut plus être annulée (statut : ' . $commande['statut'] . ').';
                }
            }
            break;

        case 'update_profil':
            $data = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'gsm' => trim($_POST['gsm'] ?? ''),
                'adresse_postale' => trim($_POST['adresse_postale'] ?? '')
            ];
            $profilErrors = [];
            if (empty($data['nom'])) $profilErrors['nom'] = 'Nom obligatoire.';
            if (empty($data['prenom'])) $profilErrors['prenom'] = 'Prénom obligatoire.';
            if (empty($data['gsm']) || !preg_match('/^0[1-9](\s?\d{2}){4}$/', $data['gsm'])) $profilErrors['gsm'] = 'Format téléphone invalide.';
            if (empty($data['adresse_postale'])) $profilErrors['adresse'] = 'Adresse obligatoire.';

            if (empty($profilErrors)) {
                if ($userRepo->updateProfil($userId, $data)) {
                    // Mise à jour session
                    $_SESSION['nom'] = $data['nom'];
                    $_SESSION['prenom'] = $data['prenom'];
                    $_SESSION['gsm'] = $data['gsm'];
                    $_SESSION['adresse_postale'] = $data['adresse_postale'];
                    $messages['success'] = 'Profil mis à jour avec succès.';
                    $user = $userRepo->findById($userId); // Rafraîchir
                } else {
                    $messages['error'] = 'Erreur lors de la mise à jour.';
                }
            } else {
                $messages['profil_errors'] = $profilErrors;
                $messages['profil_data'] = $data;
            }
            break;

        case 'update_password':
            $currentPwd = $_POST['current_password'] ?? '';
            $newPwd = $_POST['new_password'] ?? '';
            $confirmPwd = $_POST['confirm_password'] ?? '';

            $pwdErrors = [];
            if (empty($currentPwd)) $pwdErrors['current'] = 'Mot de passe actuel obligatoire.';
            if (empty($newPwd)) $pwdErrors['new'] = 'Nouveau mot de passe obligatoire.';
            else {
                $val = validatePasswordStrength($newPwd);
                if (!$val['valid']) $pwdErrors['new'] = implode(' ', $val['errors']);
            }
            if ($newPwd !== $confirmPwd) $pwdErrors['confirm'] = 'Les mots de passe ne correspondent pas.';

            if (empty($pwdErrors)) {
                // Vérifier mot de passe actuel
                if (verifyPassword($currentPwd, $user['password'])) {
                    $hashed = hashPassword($newPwd);
                    if ($userRepo->updatePassword($userId, $hashed)) {
                        $messages['success'] = 'Mot de passe modifié avec succès.';
                    } else {
                        $messages['error'] = 'Erreur lors du changement.';
                    }
                } else {
                    $messages['error'] = 'Mot de passe actuel incorrect.';
                }
            } else {
                $messages['pwd_errors'] = $pwdErrors;
            }
            break;
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mon espace client Vite & Gourmand - Historique commandes, profil, avis">
    <title>Mon Espace | Vite & Gourmand</title>

    <!-- Polices Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>

<body>
    <!-- BARRE DE NAVIGATION -->
    <?php
    $activePage = 'espace';
    require_once __DIR__ . '/includes/navbar.php';
    ?>

    <main class="main-espace">
        <div class="espace-container">
            <!-- Sidebar Navigation -->
            <aside class="espace-sidebar">
                <div class="espace-user">
                    <div class="espace-user__avatar"><?= htmlspecialchars(mb_substr($userPrenom, 0, 1)) ?></div>
                    <h3><?= htmlspecialchars($userPrenom) ?></h3>
                    <span class="espace-user__role badge badge--<?= $userRole === 'administrateur' ? 'admin' : ($userRole === 'employe' ? 'employe' : 'client') ?>">
                        <?= htmlspecialchars(ucfirst($userRole)) ?>
                    </span>
                </div>
                <nav class="espace-nav" aria-label="Navigation espace client">
                    <ul>
                        <li>
                            <a href="#mes-commandes" class="espace-nav__link is-active" data-tab="mes-commandes">
                                <span class="icon">📦</span> Mes Commandes
                            </a>
                        </li>
                        <li>
                            <a href="#mon-profil" class="espace-nav__link" data-tab="mon-profil">
                                <span class="icon">👤</span> Mon Profil
                            </a>
                        </li>
                        <li>
                            <a href="#securite" class="espace-nav__link" data-tab="securite">
                                <span class="icon">🔒</span> Sécurité
                            </a>
                        </li>
                        <?php if ($userRole !== 'utilisateur'): ?>
                            <li class="espace-nav__divider"></li>
                            <li>
                                <a href="<?= $userRole === 'administrateur' ? 'admin/dashboard.php' : 'employe/dashboard.php' ?>" class="espace-nav__link espace-nav__link--external">
                                    <span class="icon">⚙️</span> Espace Équipe
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </aside>

            <!-- Contenu Principal -->
            <div class="espace-content">
                <!-- Messages flash -->
                <?php displayFlashMessages(); ?>
                <?php if (!empty($messages['success'])): ?>
                    <div class="alert alert--success" role="alert"><?= htmlspecialchars($messages['success']) ?></div>
                <?php endif; ?>
                <?php if (!empty($messages['error'])): ?>
                    <div class="alert alert--error" role="alert"><?= htmlspecialchars($messages['error']) ?></div>
                <?php endif; ?>

                <!-- Onglet : Mes Commandes -->
                <section class="espace-tabpanel is-active" id="mes-commandes" role="tabpanel" aria-labelledby="tab-commandes">
                    <header class="espace-section-header">
                        <h2>Mes Commandes</h2>
                        <p class="espace-section-desc">Historique complet de vos commandes passées</p>
                    </header>

                    <?php if (empty($commandes)): ?>
                        <div class="espace-empty">
                            <div class="espace-empty__icon">📦</div>
                            <h3>Aucune commande pour le moment</h3>
                            <p>Découvrez nos menus et passez votre première commande !</p>
                            <a href="menus.php" class="btn btn--primary">Voir nos menus</a>
                        </div>
                    <?php else: ?>
                        <div class="commandes-list">
                            <?php foreach ($commandes as $cmd): ?>
                                <article class="commande-card" data-commande-id="<?= (int)$cmd['commande_id'] ?>">
                                    <header class="commande-card__header">
                                        <div class="commande-card__ref">
                                            <span class="commande-card__numero"><?= htmlspecialchars($cmd['numero_commande']) ?></span>
                                            <span class="commande-card__date">Le <?= date('d/m/Y à H:i', strtotime($cmd['date_commande'])) ?></span>
                                        </div>
                                        <span class="badge badge--statut badge--<?= strtolower(str_replace(' ', '-', $cmd['statut'])) ?>">
                                            <?= htmlspecialchars(ucfirst($cmd['statut'])) ?>
                                        </span>
                                    </header>

                                    <div class="commande-card__body">
                                        <div class="commande-card__menu">
                                            <img src="<?= htmlspecialchars($cmd['menu_image'] ?? 'public/img/presentation-photo.webp') ?>" alt="" class="commande-card__img" loading="lazy">
                                            <div class="commande-card__menu-info">
                                                <h3><?= htmlspecialchars($cmd['menu_titre']) ?></h3>
                                                <p class="commande-card__details">
                                                    <span>👥 <?= (int)$cmd['nb_personnes'] ?> personne(s)</span>
                                                    <span>📍 <?= htmlspecialchars($cmd['ville_prestation']) ?></span>
                                                    <span>📅 <?= date('d/m/Y', strtotime($cmd['date_prestation'])) ?> à <?= substr($cmd['heure_prestation'], 0, 5) ?></span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="commande-card__pricing">
                                            <div class="commande-card__price-row">
                                                <span>Menu (<?= (int)$cmd['nb_personnes'] ?> × <?= number_format((float)$cmd['prix_menu_unitaire'], 2, ',', ' ') ?> €)</span>
                                                <span><?= number_format((float)$cmd['prix_menu_unitaire'] * (int)$cmd['nb_personnes'], 2, ',', ' ') ?> €</span>
                                            </div>
                                            <?php if ((float)$cmd['remise_appliquee'] > 0): ?>
                                                <div class="commande-card__price-row remise">
                                                    <span>Remise (10%)</span>
                                                    <span>- <?= number_format((float)$cmd['remise_appliquee'], 2, ',', ' ') ?> €</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ((float)$cmd['frais_livraison'] > 0): ?>
                                                <div class="commande-card__price-row frais">
                                                    <span>Frais de livraison</span>
                                                    <span><?= number_format((float)$cmd['frais_livraison'], 2, ',', ' ') ?> €</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="commande-card__price-row frais offert">
                                                    <span>Frais de livraison</span>
                                                    <span class="text-success">Offerts (Bordeaux)</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="commande-card__total">
                                                <span>Total TTC</span>
                                                <strong><?= number_format((float)$cmd['prix_total'], 2, ',', ' ') ?> €</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <footer class="commande-card__actions">
                                        <button type="button" class="btn btn--outline btn--sm commande-detail-btn" data-commande-id="<?= (int)$cmd['commande_id'] ?>">
                                            👁 Détail & Suivi
                                        </button>

                                        <!-- Bouton Annulation (si statut le permet) -->
                                        <?php
                                        $statutsAnnulables = ['en attente'];
                                        $canCancel = in_array($cmd['statut'], $statutsAnnulables, true);
                                        ?>
                                        <?php if ($canCancel): ?>
                                            <button type="button" class="btn btn--danger btn--sm btn-annuler" data-commande-id="<?= (int)$cmd['commande_id'] ?>" data-numero="<?= htmlspecialchars($cmd['numero_commande']) ?>">
                                                ✕ Annuler
                                            </button>
                                        <?php endif; ?>

                                        <!-- Bouton Avis (si commande terminée et pas d'avis déjà déposé) -->
                                        <?php
                                        $canReview = ($cmd['statut'] === 'terminee');
                                        $existingAvis = false;
                                        if ($canReview) {
                                            // Vérifier si avis déjà existant pour cette commande
                                            $stmt = $avisRepo->getPdo()->prepare("SELECT avis_id FROM avis WHERE commande_id = :cmd_id AND utilisateur_id = :user_id");
                                            $stmt->execute([':cmd_id' => $cmd['commande_id'], ':user_id' => $userId]);
                                            $existingAvis = (bool)$stmt->fetch();
                                            $canReview = !$existingAvis;
                                        }
                                        ?>
                                        <?php if ($canReview): ?>
                                            <button type="button" class="btn btn--secondary btn--sm btn-aviser" data-commande-id="<?= (int)$cmd['commande_id'] ?>" data-menu="<?= htmlspecialchars($cmd['menu_titre']) ?>">
                                                ⭐ Donner mon avis
                                            </button>
                                        <?php elseif ($existingAvis): ?>
                                            <span class="btn btn--disabled btn--sm">✅ Avis déposé</span>
                                        <?php endif; ?>
                                    </footer>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Onglet : Mon Profil -->
                <section class="espace-tabpanel" id="mon-profil" role="tabpanel" aria-labelledby="tab-profil" hidden>
                    <header class="espace-section-header">
                        <h2>Mon Profil</h2>
                        <p class="espace-section-desc">Gérez vos informations personnelles</p>
                    </header>

                    <form action="" method="POST" class="espace-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="update_profil">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="profil_nom">Nom <span class="required">*</span></label>
                                <input type="text" id="profil_nom" name="nom" class="form-input" value="<?= htmlspecialchars($messages['profil_data']['nom'] ?? $user['nom'] ?? '') ?>" required>
                                <?php if (!empty($messages['profil_errors']['nom'])): ?>
                                    <span class="form-error"><?= htmlspecialchars($messages['profil_errors']['nom']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="profil_prenom">Prénom <span class="required">*</span></label>
                                <input type="text" id="profil_prenom" name="prenom" class="form-input" value="<?= htmlspecialchars($messages['profil_data']['prenom'] ?? $user['prenom'] ?? '') ?>" required>
                                <?php if (!empty($messages['profil_errors']['prenom'])): ?>
                                    <span class="form-error"><?= htmlspecialchars($messages['profil_errors']['prenom']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="profil_email">Email</label>
                            <input type="email" id="profil_email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                            <span class="form-hint">L'email ne peut pas être modifié (contactez-nous si nécessaire)</span>
                        </div>

                        <div class="form-group">
                            <label for="profil_gsm">Téléphone <span class="required">*</span></label>
                            <input type="tel" id="profil_gsm" name="gsm" class="form-input" value="<?= htmlspecialchars($messages['profil_data']['gsm'] ?? $user['gsm'] ?? '') ?>" pattern="0[1-9](\s?\d{2}){4}" required>
                            <?php if (!empty($messages['profil_errors']['gsm'])): ?>
                                <span class="form-error"><?= htmlspecialchars($messages['profil_errors']['gsm']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="profil_adresse">Adresse postale <span class="required">*</span></label>
                            <textarea id="profil_adresse" name="adresse_postale" class="form-input" rows="3" required><?= htmlspecialchars($messages['profil_data']['adresse_postale'] ?? $user['adresse_postale'] ?? '') ?></textarea>
                            <?php if (!empty($messages['profil_errors']['adresse'])): ?>
                                <span class="form-error"><?= htmlspecialchars($messages['profil_errors']['adresse']) ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
                    </form>
                </section>

                <!-- Onglet : Sécurité -->
                <section class="espace-tabpanel" id="securite" role="tabpanel" aria-labelledby="tab-securite" hidden>
                    <header class="espace-section-header">
                        <h2>Sécurité</h2>
                        <p class="espace-section-desc">Modifiez votre mot de passe</p>
                    </header>

                    <form action="" method="POST" class="espace-form espace-form--narrow" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="update_password">

                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel <span class="required">*</span></label>
                            <input type="password" id="current_password" name="current_password" class="form-input" required autocomplete="current-password">
                            <?php if (!empty($messages['pwd_errors']['current'])): ?>
                                <span class="form-error"><?= htmlspecialchars($messages['pwd_errors']['current']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe <span class="required">*</span></label>
                            <input type="password" id="new_password" name="new_password" class="form-input" required autocomplete="new-password">
                            <div class="password-requirements" aria-live="polite">
                                <span class="req" data-req="length">10 caractères minimum</span>
                                <span class="req" data-req="upper">1 majuscule</span>
                                <span class="req" data-req="lower">1 minuscule</span>
                                <span class="req" data-req="digit">1 chiffre</span>
                                <span class="req" data-req="special">1 caractère spécial</span>
                            </div>
                            <?php if (!empty($messages['pwd_errors']['new'])): ?>
                                <span class="form-error"><?= htmlspecialchars($messages['pwd_errors']['new']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmer le nouveau mot de passe <span class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required autocomplete="new-password">
                            <?php if (!empty($messages['pwd_errors']['confirm'])): ?>
                                <span class="form-error"><?= htmlspecialchars($messages['pwd_errors']['confirm']) ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn--primary">Changer le mot de passe</button>
                    </form>
                </section>
            </div>
        </div>

        <!-- Modales -->
        <!-- Modale Détail Commande -->
        <dialog class="modal" id="modal-commande-detail" role="dialog" aria-modal="true" aria-labelledby="modal-detail-title">
            <form method="dialog">
                <header class="modal__header">
                    <h2 id="modal-detail-title">Détail de la commande</h2>
                    <button type="button" class="modal__close" aria-label="Fermer">✕</button>
                </header>
                <div class="modal__body" id="modal-detail-body">
                    <!-- Contenu chargé dynamiquement -->
                </div>
                <footer class="modal__footer">
                    <button type="button" class="btn btn--outline modal__close-btn">Fermer</button>
                </footer>
            </form>
        </dialog>

        <!-- Modale Confirmation Annulation -->
        <dialog class="modal" id="modal-annuler" role="dialog" aria-modal="true" aria-labelledby="modal-annuler-title">
            <form method="POST" action="" id="form-annuler">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="annuler_commande">
                <input type="hidden" name="commande_id" id="annuler_commande_id">
                <header class="modal__header">
                    <h2 id="modal-annuler-title">Annuler la commande</h2>
                    <button type="button" class="modal__close" aria-label="Fermer">✕</button>
                </header>
                <div class="modal__body">
                    <p>Êtes-vous sûr de vouloir annuler la commande <strong id="annuler_numero"></strong> ?</p>
                    <p class="text-warning">Cette action est irréversible.</p>
                </div>
                <footer class="modal__footer">
                    <button type="button" class="btn btn--outline modal__close-btn">Non, garder</button>
                    <button type="submit" class="btn btn--danger">Oui, annuler</button>
                </footer>
            </form>
        </dialog>

        <!-- Modale Dépôt Avis -->
        <dialog class="modal" id="modal-aviser" role="dialog" aria-modal="true" aria-labelledby="modal-aviser-title">
            <form method="POST" action="actions/ajouter_avis.php" id="form-aviser">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="commande_id" id="aviser_commande_id">
                <header class="modal__header">
                    <h2 id="modal-aviser-title">Donner mon avis</h2>
                    <button type="button" class="modal__close" aria-label="Fermer">✕</button>
                </header>
                <div class="modal__body">
                    <p>Votre avis sur <strong id="aviser_menu"></strong></p>

                    <div class="form-group">
                        <label for="aviser_note">Votre note <span class="required">*</span></label>
                        <select name="note" id="aviser_note" required>
                            <option value="5">★★★★★ — Excellent (5/5)</option>
                            <option value="4">★★★★☆ — Très bon (4/5)</option>
                            <option value="3">★★★☆☆ — Bon (3/5)</option>
                            <option value="2">★★☆☆☆ — Moyen (2/5)</option>
                            <option value="1">★☆☆☆☆ — Décevant (1/5)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="aviser_commentaire">Votre commentaire <span class="required">*</span></label>
                        <textarea name="commentaire" id="aviser_commentaire" rows="4" placeholder="Racontez-nous votre expérience..." required></textarea>
                    </div>
                </div>
                <footer class="modal__footer">
                    <button type="button" class="btn btn--outline modal__close-btn">Annuler</button>
                    <button type="submit" class="btn btn--primary">Publier mon avis</button>
                </footer>
            </form>
        </dialog>
    </main>

    <!-- FOOTER -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Scripts espace client -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Navigation par onglets ---
            const navLinks = document.querySelectorAll('.espace-nav__link[data-tab]');
            const tabPanels = document.querySelectorAll('.espace-tabpanel');

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tab = this.dataset.tab;

                    // Mise à jour nav
                    navLinks.forEach(l => l.classList.remove('is-active'));
                    this.classList.add('is-active');

                    // Affichage panel
                    tabPanels.forEach(panel => {
                        if (panel.id === tab) {
                            panel.hidden = false;
                            panel.classList.add('is-active');
                        } else {
                            panel.hidden = true;
                            panel.classList.remove('is-active');
                        }
                    });
                });
            });

            // --- Modale Détail Commande ---
            const modalDetail = document.getElementById('modal-commande-detail');
            const modalDetailBody = document.getElementById('modal-detail-body');
            const detailBtns = document.querySelectorAll('.commande-detail-btn');

            detailBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const cmdId = this.dataset.commandeId;
                    loadCommandeDetail(cmdId);
                    modalDetail.showModal();
                });
            });

            async function loadCommandeDetail(cmdId) {
                modalDetailBody.innerHTML = '<div class="loading">Chargement...</div>';
                try {
                    const response = await fetch(`api/commande-detail.php?id=${cmdId}`);
                    const html = await response.text();
                    modalDetailBody.innerHTML = html;
                } catch (err) {
                    modalDetailBody.innerHTML = '<div class="alert alert--error">Erreur de chargement</div>';
                }
            }

            // Fermeture modales
            document.querySelectorAll('.modal__close, .modal__close-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = this.closest('dialog');
                    if (modal) modal.close();
                });
            });

            // Fermeture clic backdrop
            document.querySelectorAll('dialog').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) this.close();
                });
            });

            // --- Modale Annulation ---
            const modalAnnuler = document.getElementById('modal-annuler');
            const annulerBtns = document.querySelectorAll('.btn-annuler');

            annulerBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('annuler_commande_id').value = this.dataset.commandeId;
                    document.getElementById('annuler_numero').textContent = this.dataset.numero;
                    modalAnnuler.showModal();
                });
            });

            // --- Modale Avis ---
            const modalAviser = document.getElementById('modal-aviser');
            const aviserBtns = document.querySelectorAll('.btn-aviser');

            aviserBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('aviser_commande_id').value = this.dataset.commandeId;
                    document.getElementById('aviser_menu').textContent = this.dataset.menu;
                    modalAviser.showModal();
                });
            });

            // Validation temps réel MDP (onglet sécurité)
            const pwdInput = document.getElementById('new_password');
            const reqs = document.querySelectorAll('#securite .password-requirements .req');
            if (pwdInput && reqs.length) {
                pwdInput.addEventListener('input', function() {
                    const v = this.value;
                    const checks = {
                        length: v.length >= 10,
                        upper: /[A-Z]/.test(v),
                        lower: /[a-z]/.test(v),
                        digit: /\d/.test(v),
                        special: /[\W_]/.test(v)
                    };
                    reqs.forEach(r => {
                        const k = r.dataset.req;
                        r.classList.toggle('valid', checks[k]);
                    });
                });
            }
        });
    </script>
</body>

</html>