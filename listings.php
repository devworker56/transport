<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = "Annonces";
include 'includes/header.php';

// Get all active listings with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
$totalListings = $stmt->fetch()['total'];
$totalPages = ceil($totalListings / $limit);

// Get listings for current page
$stmt = $pdo->prepare("SELECT l.*, u.first_name, u.last_name 
                      FROM listings l 
                      JOIN users u ON l.created_by = u.id 
                      WHERE l.status = 'active'
                      ORDER BY l.created_at DESC 
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Annonces de livraison</h1>
            
            <?php if (count($listings) > 0): ?>
                <div class="row">
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
                                        <?php if ($listing['weight_kg']): ?>
                                            <li class="list-group-item"><strong>Poids:</strong> <?php echo $listing['weight_kg']; ?> kg</li>
                                        <?php endif; ?>
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
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucune annonce active pour le moment. Veuillez vérifier ultérieurement.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>