<?php
include 'includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$task_error = '';
$task_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    // Retrieve and sanitize input
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    // Basic input validation
    if (empty($title) || empty($description)) {
        $task_error = "Please fill in all required fields.";
    } else {
        // Prepare and execute the insert statement to prevent SQL injection
        $sql = "INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iss", $_SESSION['user_id'], $title, $description);
            if ($stmt->execute()) {
                $task_success = "Task created successfully!";
            } else {
                $task_error = "Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $task_error = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <!-- Optional: Link to Google Fonts for better typography -->
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
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }

        /* Create Task Form */
        .create-task-form {
            background-color: #ffffff;
            padding: 30px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .create-task-form h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555555;
            font-size: 14px;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            border-color: #6c63ff;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: #6c63ff;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #574b90;
        }

        .error-message {
            background-color: #ffe6e6;
            color: #cc0000;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .success-message {
            background-color: #e6ffe6;
            color: #006600;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
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

            .create-task-form {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="dashboard.php" class="logo">TaskMaster</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Create Task Form -->
        <div class="create-task-form">
            <h2>Create a New Task</h2>
            <?php if ($task_error): ?>
                <div class="error-message"><?php echo htmlspecialchars($task_error); ?></div>
            <?php endif; ?>
            <?php if ($task_success): ?>
                <div class="success-message"><?php echo htmlspecialchars($task_success); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="title">Task Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter task title" required>
                </div>
                <div class="input-group">
                    <label for="description">Task Description</label>
                    <textarea id="description" name="description" rows="5" placeholder="Enter task description" required></textarea>
                </div>
                <button type="submit" name="create_task" class="btn">Add Task</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> TaskMaster. All rights reserved.</p>
    </div>
</body>
</html>
