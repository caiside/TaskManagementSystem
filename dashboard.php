<?php
include 'includes/db_connect.php';
include 'tasks.php'; // Include CRUD functionality
session_start();

// Display tasks
echo "<h2>Your Tasks</h2>";
$sql = "SELECT * FROM tasks WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($task = $result->fetch_assoc()) {
    echo "<div>";
    echo "<h3>" . htmlspecialchars($task['title']) . "</h3>";
    echo "<p>" . htmlspecialchars($task['description']) . "</p>";
    echo "<p>Status: " . htmlspecialchars($task['status']) . "</p>";
    echo "<form method='POST'>
            <input type='hidden' name='task_id' value='" . $task['id'] . "'>
            <select name='status'>
                <option value='Pending'>Pending</option>
                <option value='In Progress'>In Progress</option>
                <option value='Completed'>Completed</option>
            </select>
            <button type='submit' name='update_task'>Update</button>
            <button type='submit' name='delete_task'>Delete</button>
          </form>";
    echo "</div>";
}
$stmt->close();
?>
