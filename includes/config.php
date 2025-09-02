<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u834808878_dbtransport');
define('DB_USER', 'u834808878_admintransport');
define('DB_PASS', 'Ossouka@1968');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Website configuration
define('SITE_NAME', 'TransportGabon');
define('SITE_URL', 'http://localhost/transportgabon');

// File upload paths
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
?>