<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = "Gestion des annonces";
include '../includes/header.php';

// Handle form actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Create or edit listing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $province = $_POST['province'];
    $pickupLocation = trim($_POST['pickup_location']);
    $deliveryDate = $_POST['delivery_date'];
    $weight = $_POST['weight_kg'] ?: null;
    $dimensions = trim($_POST['dimensions']);
    $specialInstructions = trim($_POST['special_instructions']);
    
    $errors = [];
    
    if (empty($title)) $errors[] = "Le titre est requis.";
    if (empty($description)) $errors[] = "La description est requise.";
    if (empty($province)) $errors[] = "La province est requise.";
    if (empty($pickupLocation)) $errors[] = "Le lieu de ramassage est requis.";
    if (empty($deliveryDate)) $errors[] = "La date de livraison est requise.";
    
    if (empty($errors)) {
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile($_FILES['image'], UPLOAD_PATH);
        }
        
        if ($action === 'edit' && $id > 0) {
            // Update existing listing
            if ($image) {
                $stmt = $pdo->prepare("UPDATE listings SET title = ?, description = ?, image = ?, province = ?, pickup_location = ?, delivery_date = ?, weight_kg = ?, dimensions = ?, special_instructions = ? WHERE id = ?");
                $stmt->execute([$title, $description, $image, $province, $pickupLocation, $deliveryDate, $weight, $dimensions, $specialInstructions, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE listings SET title = ?, description = ?, province = ?, pickup_location = ?, delivery_date = ?, weight_kg = ?, dimensions = ?, special_instructions = ? WHERE id = ?");
                $stmt->execute([$title, $description, $province, $pickupLocation, $deliveryDate, $weight, $dimensions, $specialInstructions, $id]);
            }
            
            $_SESSION['flash_message'] = "Annonce mise à jour avec succès.";
            $_SESSION['flash_type'] = "success";
        } else {
            // Create new listing
            $stmt = $pdo->prepare("INSERT INTO listings (title, description, image, province, pickup_location, delivery_date, weight_kg, dimensions, special_instructions, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $image, $province, $pickupLocation, $deliveryDate, $weight, $dimensions, $specialInstructions, $_SESSION['user_id']]);
            
            $_SESSION['flash_message'] = "Annonce créée avec succès.";
            $_SESSION['flash_type'] = "success";
        }
        
        redirect('listings.php');
    }
}

// Delete listing
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("DELETE FROM listings WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['flash_message'] = "Annonce supprimée avec succès.";
    $_SESSION['flash_type'] = "success";
    redirect('listings.php');
}

// Change status
if ($action === 'status' && $id > 0 && isset($_GET['status'])) {
    $status = $_GET['status'];
    $stmt = $pdo->prepare("UPDATE listings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    $_SESSION['flash_message'] = "Statut de l'annonce mis à jour.";
    $_SESSION['flash_type'] = "success";
    redirect('listings.php');
}

// Get listing for editing
$listing = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM listings WHERE id = ?");
    $stmt->execute([$id]);
    $listing = $stmt->fetch();
}

// Get all listings
$stmt = $pdo->query("SELECT l.*, u.first_name, u.last_name FROM listings l JOIN users u ON l.created_by = u.id ORDER BY l.created_at DESC");
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Gestion des annonces</h1>
                <a href="?action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nouvelle annonce
                </a>
            </div>
            
            <?php if ($action === 'create' || $action === 'edit'): ?>
                <!-- Create/Edit Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?php echo $action === 'create' ? 'Créer une annonce' : 'Modifier l\'annonce'; ?>
                        </h6>
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
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Titre *</label>
                                        <input type="text" class="form-control" id="title" name="title" required 
                                               value="<?php echo isset($listing['title']) ? htmlspecialchars($listing['title']) : (isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="province" class="form-label">Province *</label>
                                        <select class="form-select" id="province" name="province" required>
                                            <option value="">Sélectionner une province</option>
                                            <?php foreach (getProvinces() as $provinceOption): ?>
                                                <option value="<?php echo $provinceOption; ?>" 
                                                    <?php echo (isset($listing['province']) && $listing['province'] === $provinceOption) || (isset($_POST['province']) && $_POST['province'] === $provinceOption) ? 'selected' : ''; ?>>
                                                    <?php echo $provinceOption; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($listing['description']) ? htmlspecialchars($listing['description']) : (isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pickup_location" class="form-label">Lieu de ramassage *</label>
                                        <input type="text" class="form-control" id="pickup_location" name="pickup_location" required 
                                               value="<?php echo isset($listing['pickup_location']) ? htmlspecialchars($listing['pickup_location']) : (isset($_POST['pickup_location']) ? htmlspecialchars($_POST['pickup_location']) : ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="delivery_date" class="form-label">Date de livraison *</label>
                                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" required 
                                               value="<?php echo isset($listing['delivery_date']) ? $listing['delivery_date'] : (isset($_POST['delivery_date']) ? $_POST['delivery_date'] : ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="weight_kg" class="form-label">Poids (kg)</label>
                                        <input type="number" step="0.01" class="form-control" id="weight_kg" name="weight_kg" 
                                               value="<?php echo isset($listing['weight_kg']) ? $listing['weight_kg'] : (isset($_POST['weight_kg']) ? $_POST['weight_kg'] : ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="dimensions" class="form-label">Dimensions (L x W x H)</label>
                                        <input type="text" class="form-control" id="dimensions" name="dimensions" 
                                               value="<?php echo isset($listing['dimensions']) ? htmlspecialchars($listing['dimensions']) : (isset($_POST['dimensions']) ? htmlspecialchars($_POST['dimensions']) : ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="special_instructions" class="form-label">Instructions spéciales</label>
                                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3"><?php echo isset($listing['special_instructions']) ? htmlspecialchars($listing['special_instructions']) : (isset($_POST['special_instructions']) ? htmlspecialchars($_POST['special_instructions']) : ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <?php if (isset($listing['image']) && $listing['image']): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo UPLOAD_URL . $listing['image']; ?>" alt="Current image" style="max-height: 150px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listings.php" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $action === 'create' ? 'Créer' : 'Mettre à jour'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Listings Table -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Titre</th>
                                        <th>Province</th>
                                        <th>Lieu de ramassage</th>
                                        <th>Date de livraison</th>
                                        <th>Statut</th>
                                        <th>Créé par</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($listings) > 0): ?>
                                        <?php foreach ($listings as $listing): ?>
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
                                                <td><?php echo htmlspecialchars($listing['first_name'] . ' ' . $listing['last_name']); ?></td>
                                                <td><?php echo formatDate($listing['created_at']); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?action=edit&id=<?php echo $listing['id']; ?>" class="btn btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($listing['status'] === 'active'): ?>
                                                            <a href="?action=status&id=<?php echo $listing['id']; ?>&status=completed" class="btn btn-info" title="Marquer comme complété">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                            <a href="?action=status&id=<?php echo $listing['id']; ?>&status=cancelled" class="btn btn-warning" title="Annuler">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="?action=delete&id=<?php echo $listing['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Aucune annonce trouvée</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>