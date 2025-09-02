<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = "Accueil";
include 'includes/header.php';

// Get active listings
$stmt = $pdo->prepare("SELECT l.*, u.first_name, u.last_name 
                      FROM listings l 
                      JOIN users u ON l.created_by = u.id 
                      WHERE l.status = 'active' 
                      ORDER BY l.created_at DESC 
                      LIMIT 6");
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Connecter les agriculteurs et les transporteurs au Gabon</h1>
                <p class="lead">Une plateforme collaborative pour faciliter le transport des produits agricoles vers Libreville.</p>
                <div class="mt-4">
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-light btn-lg me-3">Inscription</a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">Connexion</a>
                    <?php elseif (isDriver()): ?>
                        <a href="driver/dashboard.php" class="btn btn-light btn-lg">Tableau de bord</a>
                    <?php elseif (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="btn btn-light btn-lg">Administration</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1601579530195-8a547a5a1ef4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" 
                     alt="Transport de produits agricoles" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Comment ça marche</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                        <h4 class="mt-4">1. Inscription</h4>
                        <p>Créez votre compte en tant que transporteur et soumettez vos documents pour approbation.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                        <h4 class="mt-4">2. Consulter les annonces</h4>
                        <p>Parcourez les demandes de livraison depuis les différentes provinces du Gabon.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                        <h4 class="mt-4">3. Soumissionner et livrer</h4>
                        <p>Soumettez vos offres pour les livraisons qui vous intéressent et effectuez les transports.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Listings Section -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">Annonces récentes</h2>
        <div class="row">
            <?php if (count($listings) > 0): ?>
                <?php foreach ($listings as $listing): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if ($listing['image']): ?>
                                <img src="<?php echo UPLOAD_URL . $listing['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($listing['title']); ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($listing['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo substr(htmlspecialchars($listing['description']), 0, 100); ?>...</p>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Province:</strong> <?php echo htmlspecialchars($listing['province']); ?></li>
                                    <li class="list-group-item"><strong>Lieu de ramassage:</strong> <?php echo htmlspecialchars($listing['pickup_location']); ?></li>
                                    <li class="list-group-item"><strong>Date de livraison:</strong> <?php echo formatDate($listing['delivery_date']); ?></li>
                                </ul>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Posté par: <?php echo htmlspecialchars($listing['first_name'] . ' ' . $listing['last_name']); ?></small>
                                    <a href="listing-detail.php?id=<?php echo $listing['id']; ?>" class="btn btn-sm btn-primary">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Aucune annonce disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="listings.php" class="btn btn-primary">Voir toutes les annonces</a>
        </div>
    </div>
</section>

<!-- Provinces Section -->
<section id="provinces" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Provinces desservies</h2>
        <div class="row">
            <?php $provinces = getProvinces(); ?>
            <?php foreach ($provinces as $province): ?>
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <span><?php echo $province; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="bg-primary text-white py-5">
    <div class="container text-center">
        <h2>Prêt à commencer?</h2>
        <p class="lead">Rejoignez notre plateforme et commencez à transporter des produits dès aujourd'hui.</p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-light btn-lg mt-3">Créer un compte</a>
        <?php else: ?>
            <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'driver/dashboard.php'; ?>" class="btn btn-light btn-lg mt-3">Accéder au tableau de bord</a>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>