<?php
include 'includes/db_connect.php';
session_start();

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied. Admins only.";
    exit;
}

// Handle user role updates and deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        // Update user role
        $user_id = intval($_POST['user_id']);
        $new_role = $_POST['role'] === 'Admin' ? 'Admin' : 'User';

        $update_sql = "UPDATE users SET role = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_role, $user_id);
        if ($update_stmt->execute()) {
            $user_message = "User role updated successfully.";
        } else {
            $user_error = "Error updating user role: " . htmlspecialchars($update_stmt->error);
        }
        $update_stmt->close();
    }

    if (isset($_POST['delete_user'])) {
        // Delete user
        $user_id = intval($_POST['user_id']);

        // Prevent admin from deleting themselves
        if ($user_id === intval($_SESSION['user_id'])) {
            $user_error = "You cannot delete your own account.";
        } else {
            $delete_sql = "DELETE FROM users WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
            if ($delete_stmt->execute()) {
                $user_message = "User deleted successfully.";
            } else {
                $user_error = "Error deleting user: " . htmlspecialchars($delete_stmt->error);
            }
            $delete_stmt->close();
        }
    }

    if (isset($_POST['update_task'])) {
        // Update task status
        $task_id = intval($_POST['task_id']);
        $new_status = $_POST['status'];

        // Validate status
        $valid_statuses = ['Pending', 'In Progress', 'Completed'];
        if (!in_array($new_status, $valid_statuses)) {
            $task_error = "Invalid task status.";
        } else {
            $update_task_sql = "UPDATE tasks SET status = ? WHERE id = ?";
            $update_task_stmt = $conn->prepare($update_task_sql);
            $update_task_stmt->bind_param("si", $new_status, $task_id);
            if ($update_task_stmt->execute()) {
                $task_message = "Task status updated successfully.";
            } else {
                $task_error = "Error updating task status: " . htmlspecialchars($update_task_stmt->error);
            }
            $update_task_stmt->close();
        }
    }

    if (isset($_POST['delete_task'])) {
        // Delete task
        $task_id = intval($_POST['task_id']);

        $delete_task_sql = "DELETE FROM tasks WHERE id = ?";
        $delete_task_stmt = $conn->prepare($delete_task_sql);
        $delete_task_stmt->bind_param("i", $task_id);
        if ($delete_task_stmt->execute()) {
            $task_message = "Task deleted successfully.";
        } else {
            $task_error = "Error deleting task: " . htmlspecialchars($delete_task_stmt->error);
        }
        $delete_task_stmt->close();
    }
}

// Fetch all users
$sql_users = "SELECT id, username, role FROM users";
$result_users = $conn->query($sql_users);

// Fetch all tasks
$sql_tasks = "SELECT t.id, t.title, t.description, t.status, u.username FROM tasks t JOIN users u ON t.user_id = u.id";
$result_tasks = $conn->query($sql_tasks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Google Fonts for better typography -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Reset some default browser styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f0f2f5, #c3cfe2);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation Bar */
        .navbar {
            background-color: #ffffff;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: 500;
            color: #6c63ff;
            text-decoration: none;
        }

        .navbar .nav-links a {
            margin-left: 20px;
            text-decoration: none;
            color: #333333;
            font-size: 16px;
            transition: color 0.3s;
        }

        .navbar .nav-links a:hover {
            color: #6c63ff;
        }

        /* Main Container */
        .container {
            flex: 1;
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Section Titles */
        h2 {
            font-size: 24px;
            color: #333333;
            margin-bottom: 15px;
            margin-top: 30px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #6c63ff;
            color: #ffffff;
            font-weight: 500;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Forms within tables */
        form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        select {
            padding: 6px 10px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        select:focus {
            border-color: #6c63ff;
            outline: none;
        }

        button {
            padding: 6px 12px;
            background-color: #6c63ff;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #574b90;
        }

        /* Feedback Messages */
        .message {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .error-message {
            background-color: #ffe6e6;
            color: #cc0000;
        }

        .success-message {
            background-color: #e6ffe6;
            color: #006600;
        }

        /* Footer */
        .footer {
            background-color: #ffffff;
            padding: 15px 30px;
            text-align: center;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
        }

        .footer p {
            font-size: 14px;
            color: #777777;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar .nav-links a {
                margin-left: 10px;
                font-size: 14px;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                border: 1px solid #cccccc;
                margin-bottom: 5px;
                border-radius: 8px;
                overflow: hidden;
            }

            td {
                border: none;
                border-bottom: 1px solid #eeeeee;
                position: relative;
                padding-left: 50%;
                white-space: pre-wrap;
                word-wrap: break-word;
            }

            td::before {
                position: absolute;
                top: 12px;
                left: 15px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: 500;
                font-size: 14px;
                color: #333333;
            }

            /* Labels for responsive tables */
            td:nth-of-type(1)::before { content: "ID"; }
            td:nth-of-type(2)::before { content: "Username"; }
            td:nth-of-type(3)::before { content: "Role"; }
            td:nth-of-type(4)::before { content: "Action"; }

            /* Task Table */
            .tasks-table td:nth-of-type(1)::before { content: "ID"; }
            .tasks-table td:nth-of-type(2)::before { content: "Title"; }
            .tasks-table td:nth-of-type(3)::before { content: "Description"; }
            .tasks-table td:nth-of-type(4)::before { content: "Status"; }
            .tasks-table td:nth-of-type(5)::before { content: "Assigned To"; }
            .tasks-table td:nth-of-type(6)::before { content: "Action"; }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="dashboard.php" class="logo">TaskMaster Admin</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Feedback Messages -->
        <?php if (isset($user_error)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($user_error); ?></div>
        <?php endif; ?>
        <?php if (isset($user_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($user_message); ?></div>
        <?php endif; ?>
        <?php if (isset($task_error)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($task_error); ?></div>
        <?php endif; ?>
        <?php if (isset($task_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($task_message); ?></div>
        <?php endif; ?>

        <!-- User Management -->
        <h2>Manage Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_users->num_rows > 0): ?>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <select name="role" required>
                                        <option value="User" <?php echo $user['role'] === 'User' ? 'selected' : ''; ?>>User</option>
                                        <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <button type="submit" name="update_user">Update Role</button>
                                    <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?');">Delete User</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 15px;">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Task Management -->
        <h2>Manage Tasks</h2>
        <table class="tasks-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_tasks->num_rows > 0): ?>
                    <?php while ($task = $result_tasks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['id']); ?></td>
                            <td><?php echo htmlspecialchars($task['title']); ?></td>
                            <td><?php echo htmlspecialchars($task['description']); ?></td>
                            <td><?php echo htmlspecialchars($task['status']); ?></td>
                            <td><?php echo htmlspecialchars($task['username']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                    <select name="status" required>
                                        <option value="Pending" <?php echo $task['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="In Progress" <?php echo $task['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Completed" <?php echo $task['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <button type="submit" name="update_task">Update Status</button>
                                    <button type="submit" name="delete_task" onclick="return confirm('Are you sure you want to delete this task?');">Delete Task</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 15px;">No tasks found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> TaskMaster. All rights reserved.</p>
    </div>
</body>
</html>
