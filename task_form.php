<?php
// user/task_form.php
require '../config.php';
require '../includes/functions.php';

// Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$taskId = $_GET['id'] ?? null;
$isEdit = !empty($taskId);
$task = [
    'title' => '',
    'description' => '',
    'status' => 'pending',
];

if ($isEdit) {
    // Fetch existing task
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$taskId, $user_id]);
    $task = $stmt->fetch();

    if (!$task) {
        echo 'Task not found.';
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status      = $_POST['status'];

    // Validate inputs
    $errors = [];
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            // Fetch the current status before updating
            $stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ? AND user_id = ?');
            $stmt->execute([$taskId, $user_id]);
            $currentTask = $stmt->fetch();

            // Update the task
            $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$title, $description, $status, $taskId, $user_id]);

            // Check if the task has been marked as completed
            if ($status == 'completed' && $currentTask['status'] != 'completed') {
                // Task has been marked as completed
                require '../api/mailgun_api.php';
                // Ensure the user's email is stored in the session
                $userEmail = $_SESSION['email'];
                sendCompletionEmail($userEmail, $title);
            }
        } else {
            // Create a new task
            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $title, $description, $status]);
        }
        header('Location: tasks.php');
        exit();
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <h2><?= $isEdit ? 'Edit Task' : 'Create Task' ?></h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="task_form.php<?= $isEdit ? '?id=' . $taskId : '' ?>" method="post">
        <input type="text" name="title" placeholder="Title" value="<?= htmlspecialchars($title ?? $task['title']) ?>">
        <textarea name="description" placeholder="Description"><?= htmlspecialchars($description ?? $task['description']) ?></textarea>
        <select name="status">
            <option value="pending" <?= ($status ?? $task['status']) == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="in_progress" <?= ($status ?? $task['status']) == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="completed" <?= ($status ?? $task['status']) == 'completed' ? 'selected' : '' ?>>Completed</option>
        </select>
        <button type="submit"><?= $isEdit ? 'Update' : 'Create' ?> Task</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
