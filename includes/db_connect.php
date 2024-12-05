<?php
// Database connection details
$host = 'localhost';      // Your database host
$dbname = 'taskmgmt'; // Your database name
$username = 'root';       // Your database username (default is 'root' for XAMPP/WAMP)
$password = '';           // Your database password (default is empty for XAMPP/WAMP)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
