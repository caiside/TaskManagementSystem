<?php
include 'includes/db_connect.php';
session_start();

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied. Admins only.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_task'])) {
        $task_id = $_POST['task_id'];
        $status = $_POST['status'];

        $sql = "UPDATE tasks SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $task_id);
        $stmt->execute();
        header("Location: admin_dashboard.php");
    }

    if (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];

        $sql = "DELETE FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        header("Location: admin_dashboard.php");
    }
}
?>

