<?php
// user/view_task.php
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

// Fetch the task
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$taskId, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    echo 'Task not found.';
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <h2><?= htmlspecialchars($task['title']) ?></h2>
    <p><strong>Status:</strong> <?= htmlspecialchars($task['status']) ?></p>
    <p><strong>Description:</strong></p>
    <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
    <p><strong>Created At:</strong> <?= htmlspecialchars($task['created_at']) ?></p>
    <p><strong>Last Updated:</strong> <?= htmlspecialchars($task['updated_at']) ?></p>
    <a href="task_form.php?id=<?= $task['id'] ?>">Edit</a>
    <a href="tasks.php">Back to Tasks</a>
</div>

<?php include '../includes/footer.php'; ?>
