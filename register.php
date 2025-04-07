<?php
include 'includes/db_connect.php';
session_start();

$registration_error = '';
$registration_success = '';

// Include send_email.php
require 'send_email.php'; // Include the send_email.php file where you defined the sendEmail function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']); // Get the email from form
    $password = trim($_POST['password']);
    $role = 'User'; // Default role

    // Basic input validation
    if (empty($username) || empty($email) || empty($password)) {
        $registration_error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_error = "Please enter a valid email address.";
    } else {
        // Check if username already exists using prepared statements to prevent SQL injection
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $registration_error = "Error: Username already exists. Please choose a different username.";
            } else {
                // Hash the password before storing it
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user using prepared statements
                $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

                    if ($stmt->execute()) {
                        // Send confirmation email
                        $subject = "Welcome to Our Website!";
                        $body = "Hello $username,<br>Thank you for registering on our website. We are excited to have you onboard!<br><br>Best regards,<br>Your Task Management System";

                        $email_result = sendEmail($email, $subject, $body); // Send email to the user's email

                        if ($email_result === true) {
                            $registration_success = "Registration successful. A confirmation email has been sent to your inbox. <a href='login.php'>Click here to login</a>.";
                        } else {
                            $registration_success = "Registration successful, but there was an error sending the confirmation email.";
                        }
                    } else {
                        $registration_error = "Error: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                } else {
                    $registration_error = "An error occurred. Please try again later.";
                }
            }
            $check_stmt->close();
        } else {
            $registration_error = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        .register-form h2 {
            text-align: center;
            margin-bottom: 24px;
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

        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .login-link a {
            color: #6c63ff;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <form method="POST" class="register-form">
            <h2>Register</h2>
            <?php if ($registration_error): ?>
                <div class="error-message"><?php echo htmlspecialchars($registration_error); ?></div>
            <?php endif; ?>
            <?php if ($registration_success): ?>
                <div class="success-message"><?php echo $registration_success; ?></div>
            <?php endif; ?>
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn">Register</button>
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>.
            </div>
        </form>
    </div>
</body>
</html>
