<?php
include 'includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables for messages
$profile_error = '';
$profile_success = '';

// Fetch user profile data
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Retrieve and sanitize input
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);

    // Basic input validation
    if (empty($new_username)) {
        $profile_error = "Username cannot be empty.";
    } else {
        // Check if the new username is already taken by another user
        $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $new_username, $_SESSION['user_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $profile_error = "Username is already taken. Please choose a different one.";
        } else {
            // Update username and password if provided
            if (!empty($new_password)) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssi", $new_username, $hashed_password, $_SESSION['user_id']);
            } else {
                // Update only username
                $update_sql = "UPDATE users SET username = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $new_username, $_SESSION['user_id']);
            }

            if ($update_stmt->execute()) {
                $profile_success = "Profile updated successfully!";
                // Update session username
                $_SESSION['username'] = $new_username;
                // Refresh user data
                $user['username'] = $new_username;
            } else {
                $profile_error = "Error updating profile: " . htmlspecialchars($update_stmt->error);
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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

        /* Edit Profile Form */
        .edit-profile-form {
            background-color: #ffffff;
            padding: 30px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .edit-profile-form h2 {
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

        .input-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
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

            .edit-profile-form {
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
        <!-- Edit Profile Form -->
        <div class="edit-profile-form">
            <h2>Edit Profile</h2>
            <?php if ($profile_error): ?>
                <div class="error-message"><?php echo htmlspecialchars($profile_error); ?></div>
            <?php endif; ?>
            <?php if ($profile_success): ?>
                <div class="success-message"><?php echo htmlspecialchars($profile_success); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password">
                </div>
                <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> TaskMaster. All rights reserved.</p>
    </div>
</body>
</html>
