<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireDriver();

$pageTitle = "Tableau de bord Chauffeur";
include '../includes/header.php';

// Get driver statistics
$driverId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bids WHERE driver_id = ?");
$stmt->execute([$driverId]);
$totalBids = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bids WHERE driver_id = ? AND status = 'accepted'");
$stmt->execute([$driverId]);
$acceptedBids = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bids WHERE driver_id = ? AND status = 'pending'");
$stmt->execute([$driverId]);
$pendingBids = $stmt->fetch()['total'];

// Get active listings
$stmt = $pdo->prepare("SELECT * FROM listings WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$activeListings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get driver's recent bids
$stmt = $pdo->prepare("
    SELECT b.*, l.title as listing_title, l.province, l.pickup_location, l.delivery_date
    FROM bids b
    JOIN listings l ON b.listing_id = l.id
    WHERE b.driver_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute([$driverId]);
$recentBids = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Tableau de bord Chauffeur</h1>
            
            <?php if ($_SESSION['user_status'] !== 'approved'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Votre compte est en attente d'approbation par l'administrateur. Vous ne pourrez pas soumettre d'offres tant que votre compte n'est pas approuvé.
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Offres Totales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalBids; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-handshake fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Offres Acceptées</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $acceptedBids; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Offres en Attente</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingBids; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Annonces Actives</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($activeListings); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Active Listings -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Annonces actives</h6>
                            <a href="../listings.php" class="btn btn-sm btn-primary">Voir toutes</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($activeListings) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($activeListings as $listing): ?>
                                        <a href="../listing-detail.php?id=<?php echo $listing['id']; ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($listing['title']); ?></h6>
                                                <small><?php echo formatDate($listing['delivery_date']); ?></small>
                                            </div>
                                            <p class="mb-1"><?php echo htmlspecialchars($listing['province']); ?> → Libreville</p>
                                            <small class="text-muted">Lieu de ramassage: <?php echo htmlspecialchars($listing['pickup_location']); ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Aucune annonce active pour le moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Bids -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Vos offres récentes</h6>
                            <a href="bids.php" class="btn btn-sm btn-primary">Voir toutes</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($recentBids) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($recentBids as $bid): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($bid['listing_title']); ?></h6>
                                                <span class="badge bg-<?php echo $bid['status'] === 'pending' ? 'warning' : ($bid['status'] === 'accepted' ? 'success' : 'danger'); ?>">
                                                    <?php echo $bid['status'] === 'pending' ? 'En attente' : ($bid['status'] === 'accepted' ? 'Acceptée' : 'Rejetée'); ?>
                                                </span>
                                            </div>
                                            <p class="mb-1">Montant: <?php echo number_format($bid['amount'], 0, ',', ' '); ?> FCFA</p>
                                            <small class="text-muted">Soumis le: <?php echo formatDate($bid['created_at']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Vous n'avez soumis aucune offre pour le moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>