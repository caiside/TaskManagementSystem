<?php
// config.php

require 'vendor/autoload.php'; // Include Composer's autoloader

// Database configuration
$db_host = 'localhost';
$db_name = 'task_management';
$db_user = 'your_db_username';
$db_pass = 'your_db_password';

// Database Connection
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $pdo_options);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Start the session
session_start();
?>
