<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = "Tableau de bord Admin";
include '../includes/header.php';

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'driver'");
$totalDrivers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'driver' AND status = 'pending'");
$pendingDrivers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
$activeListings = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bids WHERE status = 'pending'");
$pendingBids = $stmt->fetch()['total'];

// Get recent listings
$stmt = $pdo->prepare("SELECT l.*, u.first_name, u.last_name 
                      FROM listings l 
                      JOIN users u ON l.created_by = u.id 
                      ORDER BY l.created_at DESC 
                      LIMIT 5");
$stmt->execute();
$recentListings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Tableau de bord Administrateur</h1>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Chauffeurs Totals</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalDrivers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chauffeurs en Attente</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingDrivers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-clock fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Annonces Actives</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeListings; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Offres en Attente</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingBids; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-handshake fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Listings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Annonces récentes</h6>
                    <a href="listings.php" class="btn btn-sm btn-primary">Voir toutes</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Province</th>
                                    <th>Lieu de ramassage</th>
                                    <th>Date de livraison</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentListings) > 0): ?>
                                    <?php foreach ($recentListings as $listing): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                            <td><?php echo htmlspecialchars($listing['province']); ?></td>
                                            <td><?php echo htmlspecialchars($listing['pickup_location']); ?></td>
                                            <td><?php echo formatDate($listing['delivery_date']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $listing['status'] === 'active' ? 'success' : ($listing['status'] === 'completed' ? 'info' : 'secondary'); ?>">
                                                    <?php echo $listing['status'] === 'active' ? 'Actif' : ($listing['status'] === 'completed' ? 'Complété' : 'Annulé'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucune annonce trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
                        </div>
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="mb-3">
                                        <a href="listings.php?action=create" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus me-1"></i> Créer une annonce
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <a href="users.php" class="btn btn-info btn-block">
                                            <i class="fas fa-users me-1"></i> Gérer les chauffeurs
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <a href="bids.php" class="btn btn-warning btn-block">
                                            <i class="fas fa-handshake me-1"></i> Gérer les offres
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>