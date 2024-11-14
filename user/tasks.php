<?php
// user/tasks.php
require '../config.php';
require '../includes/functions.php';

// Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Fetch user's tasks
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <h2>Your Tasks</h2>
    <a href="task_form.php">Create New Task</a>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?= htmlspecialchars($task['title']) ?></td>
                <td><?= htmlspecialchars($task['status']) ?></td>
                <td><?= htmlspecialchars($task['created_at']) ?></td>
                <td>
                    <a href="view_task.php?id=<?= $task['id'] ?>">View</a>
                    <a href="task_form.php?id=<?= $task['id'] ?>">Edit</a>
                    <a href="delete_task.php?id=<?= $task['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
