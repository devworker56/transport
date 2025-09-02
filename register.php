<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = "Inscription";
include 'includes/header.php';

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $vehicleType = $_POST['vehicle_type'];
    $vehicleDetails = trim($_POST['vehicle_details']);
    $licensePlate = trim($_POST['license_plate']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($firstName)) $errors[] = "Le prénom est requis.";
    if (empty($lastName)) $errors[] = "Le nom est requis.";
    if (empty($email)) $errors[] = "L'email est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide.";
    if (empty($password)) $errors[] = "Le mot de passe est requis.";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    if ($password !== $confirmPassword) $errors[] = "Les mots de passe ne correspondent pas.";
    if (empty($vehicleType)) $errors[] = "Le type de véhicule est requis.";
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Cet email est déjà utilisé.";
    }
    
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Handle file uploads
        $profileImage = null;
        $idDocument = null;
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $profileImage = uploadFile($_FILES['profile_image'], UPLOAD_PATH);
        }
        
        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
            $idDocument = uploadFile($_FILES['id_document'], UPLOAD_PATH);
        }
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, phone, vehicle_type, vehicle_details, license_plate, profile_image, id_document) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $phone, $vehicleType, $vehicleDetails, $licensePlate, $profileImage, $idDocument]);
        
        $_SESSION['flash_message'] = "Votre compte a été créé avec succès. Il sera activé après approbation par l'administrateur.";
        $_SESSION['flash_type'] = "success";
        redirect('login.php');
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Créer un compte transporteur</h3>
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
                                    <label for="first_name" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required 
                                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required 
                                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehicle_type" class="form-label">Type de véhicule</label>
                                    <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="pickup" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'pickup') ? 'selected' : ''; ?>>Pickup</option>
                                        <option value="van" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'van') ? 'selected' : ''; ?>>Van</option>
                                        <option value="truck" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'truck') ? 'selected' : ''; ?>>Camion</option>
                                        <option value="other" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'other') ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_plate" class="form-label">Plaque d'immatriculation</label>
                                    <input type="text" class="form-control" id="license_plate" name="license_plate" 
                                           value="<?php echo isset($_POST['license_plate']) ? htmlspecialchars($_POST['license_plate']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="vehicle_details" class="form-label">Détails du véhicule (marque, modèle, capacité, etc.)</label>
                            <textarea class="form-control" id="vehicle_details" name="vehicle_details" rows="3"><?php echo isset($_POST['vehicle_details']) ? htmlspecialchars($_POST['vehicle_details']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Photo de profil</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_document" class="form-label">Document d'identité (PDF ou image)</label>
                                    <input type="file" class="form-control" id="id_document" name="id_document" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="#">termes et conditions</a>
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Créer mon compte</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Vous avez déjà un compte? <a href="login.php">Connectez-vous ici</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>