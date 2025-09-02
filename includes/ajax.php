<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

// Check if it's an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(['error' => 'Accès direct non autorisé']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'dashboard_stats':
        if (!isLoggedIn()) {
            echo json_encode(['error' => 'Non authentifié']);
            exit;
        }
        
        $stats = [];
        
        if (isAdmin()) {
            // Admin stats
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'driver'");
            $stats['total_drivers'] = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'driver' AND status = 'pending'");
            $stats['pending_drivers'] = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
            $stats['active_listings'] = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM bids WHERE status = 'pending'");
            $stats['pending_bids'] = $stmt->fetch()['total'];
        } else {
            // Driver stats
            $driverId = $_SESSION['user_id'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bids WHERE driver_id = ?");
            $stmt->execute([$driverId]);
            $stats['total_bids'] = $stmt->fetch()['total'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bids WHERE driver_id = ? AND status = 'accepted'");
            $stmt->execute([$driverId]);
            $stats['accepted_bids'] = $stmt->fetch()['total'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bids WHERE driver_id = ? AND status = 'pending'");
            $stmt->execute([$driverId]);
            $stats['pending_bids'] = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
            $stats['active_listings'] = $stmt->fetch()['total'];
        }
        
        echo json_encode(array_merge(['success' => true], $stats));
        break;
        
    case 'check_email':
        $email = $_GET['email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['valid' => false, 'message' => 'Format email invalide']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $exists = $stmt->fetch();
        
        echo json_encode(['valid' => !$exists, 'message' => $exists ? 'Email déjà utilisé' : 'Email disponible']);
        break;
        
    case 'get_provinces':
        echo json_encode(getProvinces());
        break;
        
    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}