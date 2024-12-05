<?php
include 'includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user tasks
$sql = "SELECT * FROM tasks WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user profile data
$user_sql = "SELECT username FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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

        /* Main Content */
        .container {
            flex: 1;
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Welcome Section */
        .welcome {
            margin-bottom: 30px;
        }

        .welcome h1 {
            font-size: 32px;
            color: #333333;
            margin-bottom: 10px;
        }

        .welcome p {
            font-size: 18px;
            color: #555555;
        }

        /* Buttons */
        .buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .buttons form {
            margin: 0;
        }

        .buttons button {
            padding: 12px 20px;
            background-color: #6c63ff;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .buttons button:hover {
            background-color: #574b90;
        }

        /* Tasks Section */
        .tasks {
            margin-bottom: 30px;
        }

        .tasks h2 {
            font-size: 24px;
            color: #333333;
            margin-bottom: 15px;
        }

        .task-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .task-card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .task-card h3 {
            font-size: 20px;
            color: #6c63ff;
            margin-bottom: 10px;
        }

        .task-card p {
            font-size: 16px;
            color: #555555;
            margin-bottom: 8px;
        }

        .task-status {
            font-weight: 500;
            color: #ff6347; /* Default color for 'Pending' */
        }

        .task-status.completed {
            color: #32cd32;
        }

        /* No Tasks Message */
        .no-tasks {
            font-size: 18px;
            color: #555555;
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
            .buttons {
                flex-direction: column;
            }

            .buttons button {
                width: 100%;
            }

            .task-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="#" class="logo">TaskMaster</a>
        <div class="nav-links">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome">
            <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p>Manage your tasks efficiently and stay organized.</p>
        </div>

        <!-- Action Buttons -->
        <div class="buttons">
            <!-- Edit Profile Button -->
            <form method="GET" action="edit_profile.php">
                <button type="submit">Edit Profile</button>
            </form>

            <!-- Create New Task Button -->
            <form method="GET" action="create_task.php">
                <button type="submit">Create New Task</button>
            </form>
        </div>

        <!-- Tasks Section -->
        <div class="tasks">
            <h2>Your Tasks</h2>
            <?php
            if ($result->num_rows > 0) {
                echo '<div class="task-list">';
                while ($task = $result->fetch_assoc()) {
                    // Determine task status color
                    $status_class = '';
                    if (strtolower($task['status']) === 'completed') {
                        $status_class = 'completed';
                    }
                    echo "<div class='task-card'>";
                    echo "<h3>" . htmlspecialchars($task['title']) . "</h3>";
                    echo "<p>" . htmlspecialchars($task['description']) . "</p>";
                    echo "<p>Status: <span class='task-status " . $status_class . "'>" . htmlspecialchars($task['status']) . "</span></p>";
                    echo "</div>";
                }
                echo '</div>';
            } else {
                echo "<p class='no-tasks'>No tasks found. Start by creating a new task!</p>";
            }
            $stmt->close();
            $user_stmt->close();
            ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> TaskMaster. All rights reserved.</p>
    </div>
</body>
</html>
