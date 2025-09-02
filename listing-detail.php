<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if listing ID is provided
if (!isset($_GET['id'])) {
    redirect('listings.php');
}

$listingId = (int)$_GET['id'];

// Get listing details
$stmt = $pdo->prepare("SELECT l.*, u.first_name, u.last_name 
                      FROM listings l 
                      JOIN users u ON l.created_by = u.id 
                      WHERE l.id = ?");
$stmt->execute([$listingId]);
$listing = $stmt->fetch();

if (!$listing) {
    $_SESSION['flash_message'] = "Annonce non trouvée.";
    $_SESSION['flash_type'] = "danger";
    redirect('listings.php');
}

$pageTitle = htmlspecialchars($listing['title']);
include 'includes/header.php';

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && isDriver() && $_SESSION['user_status'] === 'approved') {
    $amount = $_POST['amount'];
    $message = trim($_POST['message']);
    $driverId = $_SESSION['user_id'];
    
    $errors = [];
    
    if (empty($amount) || $amount <= 0) {
        $errors[] = "Le montant doit être supérieur à 0.";
    }
    
    // Check if driver already bid on this listing
    $stmt = $pdo->prepare("SELECT id FROM bids WHERE listing_id = ? AND driver_id = ?");
    $stmt->execute([$listingId, $driverId]);
    if ($stmt->fetch()) {
        $errors[] = "Vous avez déjà soumis une offre pour cette annonce.";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO bids (listing_id, driver_id, amount, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$listingId, $driverId, $amount, $message]);
        
        $_SESSION['flash_message'] = "Votre offre a été soumise avec succès.";
        $_SESSION['flash_type'] = "success";
        redirect('listing-detail.php?id=' . $listingId);
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <?php if ($listing['image']): ?>
                    <img src="<?php echo UPLOAD_URL . $listing['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($listing['title']); ?>" style="max-height: 400px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($listing['title']); ?></h1>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Détails de la livraison</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Province:</strong> <?php echo htmlspecialchars($listing['province']); ?></li>
                                <li class="list-group-item"><strong>Lieu de ramassage:</strong> <?php echo htmlspecialchars($listing['pickup_location']); ?></li>
                                <li class="list-group-item"><strong>Destination:</strong> Libreville</li>
                                <li class="list-group-item"><strong>Date de livraison:</strong> <?php echo formatDate($listing['delivery_date']); ?></li>
                                <?php if ($listing['weight_kg']): ?>
                                    <li class="list-group-item"><strong>Poids:</strong> <?php echo $listing['weight_kg']; ?> kg</li>
                                <?php endif; ?>
                                <?php if ($listing['dimensions']): ?>
                                    <li class="list-group-item"><strong>Dimensions:</strong> <?php echo htmlspecialchars($listing['dimensions']); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Informations supplémentaires</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Statut:</strong> 
                                    <span class="badge bg-<?php echo $listing['status'] === 'active' ? 'success' : ($listing['status'] === 'completed' ? 'info' : 'secondary'); ?>">
                                        <?php echo $listing['status'] === 'active' ? 'Actif' : ($listing['status'] === 'completed' ? 'Complété' : 'Annulé'); ?>
                                    </span>
                                </li>
                                <li class="list-group-item"><strong>Posté par:</strong> <?php echo htmlspecialchars($listing['first_name'] . ' ' . $listing['last_name']); ?></li>
                                <li class="list-group-item"><strong>Date de publication:</strong> <?php echo formatDate($listing['created_at']); ?></li>
                            </ul>
                            
                            <?php if ($listing['special_instructions']): ?>
                                <div class="mt-3">
                                    <h5>Instructions spéciales</h5>
                                    <p><?php echo nl2br(htmlspecialchars($listing['special_instructions'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <?php if (isLoggedIn() && isDriver() && $listing['status'] === 'active'): ?>
                <?php if ($_SESSION['user_status'] === 'approved'): ?>
                    <!-- Bid Form -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Soumettre une offre</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo $error; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Montant (FCFA)</label>
                                    <input type="number" class="form-control" id="amount" name="amount" required min="1" step="any">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message (optionnel)</label>
                                    <textarea class="form-control" id="message" name="message" rows="3" placeholder="Informations supplémentaires sur votre offre..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Soumettre l'offre</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Not Approved Message -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Votre compte n'a pas encore été approuvé. Vous ne pouvez pas soumettre d'offres pour le moment.
                    </div>
                <?php endif; ?>
            <?php elseif (!isLoggedIn()): ?>
                <!-- Login Prompt -->
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5 class="card-title">Intéressé par cette livraison?</h5>
                        <p class="card-text">Connectez-vous pour soumettre une offre.</p>
                        <a href="login.php" class="btn btn-primary me-2">Se connecter</a>
                        <a href="register.php" class="btn btn-outline-primary">S'inscrire</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <a href="listings.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour aux annonces
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>