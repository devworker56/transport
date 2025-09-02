<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireDriver();
requireApprovedDriver();

$pageTitle = "Mes offres";
include '../includes/header.php';

$driverId = $_SESSION['user_id'];

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'])) {
    $listingId = $_POST['listing_id'];
    $amount = $_POST['amount'];
    $message = trim($_POST['message']);
    
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
        redirect('bids.php');
    }
}

// Get driver's bids
$stmt = $pdo->prepare("
    SELECT b.*, l.title as listing_title, l.province, l.pickup_location, l.delivery_date
    FROM bids b
    JOIN listings l ON b.listing_id = l.id
    WHERE b.driver_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$driverId]);
$bids = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Mes offres</h1>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Historique des offres</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Annonce</th>
                                    <th>Province</th>
                                    <th>Lieu de ramassage</th>
                                    <th>Date de livraison</th>
                                    <th>Montant (FCFA)</th>
                                    <th>Date de soumission</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($bids) > 0): ?>
                                    <?php foreach ($bids as $bid): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($bid['listing_title']); ?></td>
                                            <td><?php echo htmlspecialchars($bid['province']); ?></td>
                                            <td><?php echo htmlspecialchars($bid['pickup_location']); ?></td>
                                            <td><?php echo formatDate($bid['delivery_date']); ?></td>
                                            <td><?php echo number_format($bid['amount'], 0, ',', ' '); ?></td>
                                            <td><?php echo formatDate($bid['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $bid['status'] === 'pending' ? 'warning' : ($bid['status'] === 'accepted' ? 'success' : 'danger'); ?>">
                                                    <?php echo $bid['status'] === 'pending' ? 'En attente' : ($bid['status'] === 'accepted' ? 'Acceptée' : 'Rejetée'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php if (!empty($bid['message'])): ?>
                                            <tr>
                                                <td colspan="7" class="bg-light">
                                                    <strong>Votre message:</strong> <?php echo htmlspecialchars($bid['message']); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Vous n'avez soumis aucune offre pour le moment.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="../listings.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-clipboard-list me-1"></i> Voir les annonces disponibles
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>