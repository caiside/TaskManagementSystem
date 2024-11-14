<?php
// admin/dashboard.php
require '../config.php';
require '../includes/functions.php';

// Ensure the admin is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch data for dashboard (e.g., total users, total tasks)
$totalUsersStmt = $pdo->query('SELECT COUNT(*) FROM users');
$totalUsers = $totalUsersStmt->fetchColumn();

$totalTasksStmt = $pdo->query('SELECT COUNT(*) FROM tasks');
$totalTasks = $totalTasksStmt->fetchColumn();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <h2>Admin Dashboard</h2>
    <p>Total Users: <?= $totalUsers ?></p>
    <p>Total Tasks: <?= $totalTasks ?></p>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_tasks.php">Manage Tasks</a>
</div>

<?php include '../includes/footer.php'; ?>
