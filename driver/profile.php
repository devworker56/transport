<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireDriver();

$pageTitle = "Mon profil";
include '../includes/header.php';

$driverId = $_SESSION['user_id'];

// Get driver information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$driverId]);
$driver = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $vehicleType = $_POST['vehicle_type'];
    $vehicleDetails = trim($_POST['vehicle_details']);
    $licensePlate = trim($_POST['license_plate']);
    
    $errors = [];
    
    if (empty($firstName)) $errors[] = "Le prénom est requis.";
    if (empty($lastName)) $errors[] = "Le nom est requis.";
    if (empty($vehicleType)) $errors[] = "Le type de véhicule est requis.";
    
    if (empty($errors)) {
        // Handle profile image upload
        $profileImage = $driver['profile_image'];
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image if exists
            if ($profileImage && file_exists(UPLOAD_PATH . $profileImage)) {
                unlink(UPLOAD_PATH . $profileImage);
            }
            $profileImage = uploadFile($_FILES['profile_image'], UPLOAD_PATH);
        }
        
        // Update driver information
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, vehicle_type = ?, vehicle_details = ?, license_plate = ?, profile_image = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $phone, $vehicleType, $vehicleDetails, $licensePlate, $profileImage, $driverId]);
        
        // Update session variables
        $_SESSION['user_first_name'] = $firstName;
        $_SESSION['user_last_name'] = $lastName;
        
        $_SESSION['flash_message'] = "Profil mis à jour avec succès.";
        $_SESSION['flash_type'] = "success";
        redirect('profile.php');
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations personnelles</h6>
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
                                    <label for="first_name" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required 
                                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : htmlspecialchars($driver['first_name']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required 
                                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : htmlspecialchars($driver['last_name']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($driver['email']); ?>" disabled>
                                    <small class="text-muted">L'email ne peut pas être modifié.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($driver['phone']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehicle_type" class="form-label">Type de véhicule *</label>
                                    <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="pickup" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'pickup') || $driver['vehicle_type'] === 'pickup' ? 'selected' : ''; ?>>Pickup</option>
                                        <option value="van" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'van') || $driver['vehicle_type'] === 'van' ? 'selected' : ''; ?>>Van</option>
                                        <option value="truck" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'truck') || $driver['vehicle_type'] === 'truck' ? 'selected' : ''; ?>>Camion</option>
                                        <option value="other" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'other') || $driver['vehicle_type'] === 'other' ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_plate" class="form-label">Plaque d'immatriculation</label>
                                    <input type="text" class="form-control" id="license_plate" name="license_plate" 
                                           value="<?php echo isset($_POST['license_plate']) ? htmlspecialchars($_POST['license_plate']) : htmlspecialchars($driver['license_plate']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="vehicle_details" class="form-label">Détails du véhicule (marque, modèle, capacité, etc.)</label>
                            <textarea class="form-control" id="vehicle_details" name="vehicle_details" rows="3"><?php echo isset($_POST['vehicle_details']) ? htmlspecialchars($_POST['vehicle_details']) : htmlspecialchars($driver['vehicle_details']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <?php if ($driver['profile_image']): ?>
                                <div class="mt-2">
                                    <img src="<?php echo UPLOAD_URL . $driver['profile_image']; ?>" alt="Photo de profil actuelle" style="max-height: 150px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statut du compte</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge bg-<?php echo $driver['status'] === 'pending' ? 'warning' : ($driver['status'] === 'approved' ? 'success' : 'danger'); ?> p-3 fs-6">
                            <?php echo $driver['status'] === 'pending' ? 'En attente d\'approbation' : ($driver['status'] === 'approved' ? 'Compte approuvé' : 'Compte rejeté'); ?>
                        </span>
                    </div>
                    
                    <?php if ($driver['status'] === 'pending'): ?>
                        <p class="text-muted">Votre compte est en attente d'approbation par l'administrateur. Vous serez notifié une fois approuvé.</p>
                    <?php elseif ($driver['status'] === 'approved'): ?>
                        <p class="text-success">Votre compte est approuvé. Vous pouvez maintenant soumettre des offres pour les annonces.</p>
                    <?php else: ?>
                        <p class="text-danger">Votre compte a été rejeté. Veuillez contacter l'administrateur pour plus d'informations.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Document Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Document d'identité</h6>
                </div>
                <div class="card-body text-center">
                    <?php if ($driver['id_document']): ?>
                        <a href="<?php echo UPLOAD_URL . $driver['id_document']; ?>" target="_blank" class="btn btn-outline-primary mb-3">
                            <i class="fas fa-file-pdf me-1"></i> Voir le document
                        </a>
                        <p class="text-muted">Document d'identité téléchargé lors de l'inscription.</p>
                    <?php else: ?>
                        <p class="text-muted">Aucun document d'identité téléchargé.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>