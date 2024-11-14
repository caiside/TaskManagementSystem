<?php
// user/delete_task.php
require '../config.php';
require '../includes/functions.php';

// Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$taskId = $_GET['id'] ?? null;

if (!$taskId) {
    echo 'Task ID is missing.';
    exit();
}

// Delete the task
$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$taskId, $_SESSION['user_id']]);

header('Location: tasks.php');
exit();
?>
