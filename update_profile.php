<?php
include 'includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;

    if ($password) {
        $sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Hash new password
        $stmt->bind_param("ssi", $username, $hashed_password, $_SESSION['user_id']);
    } else {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $_SESSION['user_id']);
    }

    if ($stmt->execute()) {
        echo "Profile updated successfully.";
        header('Location: index.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
