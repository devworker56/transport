<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = "Gestion des utilisateurs";
include '../includes/header.php';

// Handle user actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Approve or reject user
if (($action === 'approve' || $action === 'reject') && $id > 0) {
    $status = $action === 'approve' ? 'approved' : 'rejected';
    
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    $_SESSION['flash_message'] = "Utilisateur " . ($action === 'approve' ? 'approuvé' : 'rejeté') . " avec succès.";
    $_SESSION['flash_type'] = "success";
    redirect('users.php');
}

// Delete user
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'driver'");
    $stmt->execute([$id]);
    
    $_SESSION['flash_message'] = "Utilisateur supprimé avec succès.";
    $_SESSION['flash_type'] = "success";
    redirect('users.php');
}

// Get all drivers
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'driver' ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Gestion des chauffeurs</h1>
            
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Nom complet</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Type de véhicule</th>
                                    <th>Plaque d'immatriculation</th>
                                    <th>Date d'inscription</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td>
                                                <?php 
                                                $vehicleTypes = [
                                                    'pickup' => 'Pickup',
                                                    'van' => 'Van',
                                                    'truck' => 'Camion',
                                                    'other' => 'Autre'
                                                ];
                                                echo $vehicleTypes[$user['vehicle_type']] ?? 'Inconnu';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['license_plate']); ?></td>
                                            <td><?php echo formatDate($user['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['status'] === 'pending' ? 'warning' : ($user['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                                    <?php echo $user['status'] === 'pending' ? 'En attente' : ($user['status'] === 'approved' ? 'Approuvé' : 'Rejeté'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($user['status'] === 'pending'): ?>
                                                        <a href="?action=approve&id=<?php echo $user['id']; ?>" class="btn btn-success" title="Approuver ce chauffeur">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="?action=reject&id=<?php echo $user['id']; ?>" class="btn btn-danger" title="Rejeter ce chauffeur">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php if (!empty($user['vehicle_details'])): ?>
                                            <tr>
                                                <td colspan="8" class="bg-light">
                                                    <strong>Détails du véhicule:</strong> <?php echo htmlspecialchars($user['vehicle_details']); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Aucun chauffeur trouvé</td>
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