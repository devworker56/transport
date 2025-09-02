<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = "Gestion des offres";
include '../includes/header.php';

// Handle bid actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Accept or reject bid
if (($action === 'accept' || $action === 'reject') && $id > 0) {
    $status = $action === 'accept' ? 'accepted' : 'rejected';
    
    $stmt = $pdo->prepare("UPDATE bids SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    // If accepted, reject all other bids for this listing
    if ($action === 'accept') {
        $stmt = $pdo->prepare("SELECT listing_id FROM bids WHERE id = ?");
        $stmt->execute([$id]);
        $bid = $stmt->fetch();
        
        $stmt = $pdo->prepare("UPDATE bids SET status = 'rejected' WHERE listing_id = ? AND id != ?");
        $stmt->execute([$bid['listing_id'], $id]);
        
        // Mark listing as completed
        $stmt = $pdo->prepare("UPDATE listings SET status = 'completed' WHERE id = ?");
        $stmt->execute([$bid['listing_id']]);
    }
    
    $_SESSION['flash_message'] = "Offre " . ($action === 'accept' ? 'acceptée' : 'rejetée') . " avec succès.";
    $_SESSION['flash_type'] = "success";
    redirect('bids.php');
}

// Get all bids with related information
$stmt = $pdo->query("
    SELECT b.*, 
           l.title as listing_title, l.province, l.pickup_location, l.delivery_date,
           u.first_name as driver_first_name, u.last_name as driver_last_name, u.phone as driver_phone
    FROM bids b
    JOIN listings l ON b.listing_id = l.id
    JOIN users u ON b.driver_id = u.id
    ORDER BY b.created_at DESC
");
$bids = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Gestion des offres</h1>
            
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Annonce</th>
                                    <th>Chauffeur</th>
                                    <th>Montant (FCFA)</th>
                                    <th>Province</th>
                                    <th>Lieu de ramassage</th>
                                    <th>Date de livraison</th>
                                    <th>Date de soumission</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($bids) > 0): ?>
                                    <?php foreach ($bids as $bid): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($bid['listing_title']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($bid['driver_first_name'] . ' ' . $bid['driver_last_name']); ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($bid['driver_phone']); ?></small>
                                            </td>
                                            <td><?php echo number_format($bid['amount'], 0, ',', ' '); ?></td>
                                            <td><?php echo htmlspecialchars($bid['province']); ?></td>
                                            <td><?php echo htmlspecialchars($bid['pickup_location']); ?></td>
                                            <td><?php echo formatDate($bid['delivery_date']); ?></td>
                                            <td><?php echo formatDate($bid['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $bid['status'] === 'pending' ? 'warning' : ($bid['status'] === 'accepted' ? 'success' : 'danger'); ?>">
                                                    <?php echo $bid['status'] === 'pending' ? 'En attente' : ($bid['status'] === 'accepted' ? 'Acceptée' : 'Rejetée'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($bid['status'] === 'pending'): ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?action=accept&id=<?php echo $bid['id']; ?>" class="btn btn-success" title="Accepter cette offre">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="?action=reject&id=<?php echo $bid['id']; ?>" class="btn btn-danger" title="Rejeter cette offre">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Aucune action</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if (!empty($bid['message'])): ?>
                                            <tr>
                                                <td colspan="9" class="bg-light">
                                                    <strong>Message du chauffeur:</strong> <?php echo htmlspecialchars($bid['message']); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Aucune offre trouvée</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>