<?php
// user/dashboard.php
require '../config.php';
require '../includes/functions.php';

// Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Fetch user's tasks
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<!-- HTML to display tasks -->
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
    <h3>Your Recent Tasks</h3>
    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <a href="view_task.php?id=<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a>
                - <?= htmlspecialchars($task['status']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="tasks.php">View All Tasks</a>
</div>

<?php include '../includes/footer.php'; ?>
