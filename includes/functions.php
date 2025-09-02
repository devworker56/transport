<?php
// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Check if user is driver
function isDriver() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'driver';
}

// Get provinces of Gabon
function getProvinces() {
    return array(
        'Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié', 
        'Nyanga', 'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem'
    );
}

// Format date in French
function formatDate($date) {
    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
    return strftime('%d %B %Y', strtotime($date));
}

// Upload file function
function uploadFile($file, $targetDir) {
    $fileName = time() . '_' . basename($file['name']);
    $targetFilePath = $targetDir . $fileName;
    
    if(move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return $fileName;
    }
    return false;
}
?>