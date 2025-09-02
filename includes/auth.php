<?php
// Authentication functions

// Check if user is logged in and redirect if not
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_message'] = "Veuillez vous connecter pour accéder à cette page.";
        $_SESSION['flash_type'] = "warning";
        redirect('login.php');
    }
}

// Check if user is admin and redirect if not
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['flash_message'] = "Accès refusé. Droits administrateur requis.";
        $_SESSION['flash_type'] = "danger";
        redirect('index.php');
    }
}

// Check if user is driver and redirect if not
function requireDriver() {
    requireLogin();
    if (!isDriver()) {
        $_SESSION['flash_message'] = "Accès refusé. Droits chauffeur requis.";
        $_SESSION['flash_type'] = "danger";
        redirect('index.php');
    }
}

// Check if driver is approved
function requireApprovedDriver() {
    requireDriver();
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['status'] !== 'approved') {
        $_SESSION['flash_message'] = "Votre compte n'a pas encore été approuvé par l'administrateur.";
        $_SESSION['flash_type'] = "warning";
        redirect('index.php');
    }
}
?>