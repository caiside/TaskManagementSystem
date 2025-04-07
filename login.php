<?php
// ================== BACKEND LOGIC (PHP) ==================
include 'includes/db_connect.php';
include 'send_email.php'; // ✅ Reuse your PHPMailer function
session_start();

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user details using prepared statements to prevent SQL injection
    $sql = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $username, $username); // Allow email or username
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password using password_verify()
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // ============ SEND LOGIN NOTIFICATION ============
                $to = $user['email'];
                $subject = 'Login Notification';
                $body = "
                    <p>Hi <strong>{$user['username']}</strong>,</p>
                    <p>Your account was just accessed on <strong>" . date("l, F j, Y \a\\t g:i A") . "</strong>.</p>
                    <p>If this wasn't you, please change your password immediately.</p>
                    <p>— TaskManagement Team</p>
                ";
                sendEmail($to, $subject, $body);

                // Redirect based on role
                if ($user['role'] === 'Admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $login_error = "Invalid credentials.";
            }
        } else {
            $login_error = "Invalid credentials.";
        }
        $stmt->close();
    } else {
        $login_error = "An error occurred. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <!-- Google Fonts (optional) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet"
    />
    <style>
        /* ========== GLOBAL RESET & BASE STYLES ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100vh ;
            font-family: 'Roboto', sans-serif;
            background-color: #f3f4f6; /* fallback background */
        }

        /* ========== SPLIT SCREEN LAYOUT ========== */
        .split-screen-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ===== Left Panel: White background with login form ===== */
        .left-panel {
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 30px 80px;
        }

        /* (Optional) Top area for a logo or brand mark */
        .logo-area {
            margin-bottom: 2rem;
        }
        .logo-area img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        /* Welcome text, subheading, etc. */
        .welcome-text {
            margin-bottom: 1.5rem;
        }
        .welcome-text h1 {
            font-size: 2rem;
            margin-bottom: 8px;
            color: #1e1e1e;
        }
        .welcome-text p {
            font-size: 1rem;
            color: #666;
        }

        /* Error message styling */
        .error-message {
            background-color: #ffe6e6;
            color: #cc0000;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        /* ===== The Form Itself ===== */
        .login-form {
            display: flex;
            flex-direction: column;
            row-gap: 1rem;
        }

        .login-form label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.3rem;
            color: #333;
        }
        .login-form input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: border-color 0.2s;
            font-size: 16px;
        }
        .login-form input:focus {
            border-color: #6c63ff;
            outline: none;
        }

        /* Remember me & Forgot Password row */
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .remember-me {
            /* Ensure the checkbox and label text stay on one line */
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.9rem;
            color: #555;
        }
        /* Remove default top offset on some browsers */
        .remember-me input[type="checkbox"] {
            margin: 0;
            transform: translateY(0);
        }

        .forgot-password {
            font-size: 0.9rem;
            color: #6c63ff;
            text-decoration: none;
        }

        /* Login button */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .login-btn {
            background-color: #6c63ff;
            color: #fff;
            width: 100%;
        }
        .login-btn:hover {
            background-color: #574b90;
            box-shadow: 0 2px 6px rgba(87, 75, 144, 0.3);
        }

        /* Separator line with text in between */
        .separator {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            font-size: 0.9rem;
            color: #999;
        }
        .separator::before,
        .separator::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #ccc;
        }
        .separator span {
            margin: 0 0.5rem;
        }

        /* Google login button (example) */
        .google-btn {
            background-color: #f5f5f5;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .google-btn img {
            width: 18px;
            height: 18px;
        }
        .google-btn:hover {
            background-color: #e8e8e8;
        }

        /* Register Link at bottom */
        .register-link {
            margin-top: 1rem;
            font-size: 0.9rem;
            text-align: center;
        }
        .register-link a {
            color: #6c63ff;
            text-decoration: none;
        }

        /* ===== Right Panel: Colorful / Artistic Background ===== */
        .right-panel {
            flex: 1;
            background: #342c6a; /* fallback color */
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Replace this with an SVG or custom shapes to match the reference image. */
        .abstract-background {
            position: relative;
            width: 80%;
            height: 80%;
            background: #4b3bbf;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.2);
        }
        /* Example shapes inside the right panel */
        .shape {
            position: absolute;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            opacity: 0.6;
        }
        .shape-1 {
            top: 20%;
            left: 25%;
            background: #ffcf00;
        }
        .shape-2 {
            top: 50%;
            left: 60%;
            background: #5de2fc;
        }
        .shape-3 {
            bottom: 15%;
            left: 40%;
            background: #ff5c8a;
        }

        /* ========== RESPONSIVE MEDIA QUERY ========== */
        /* Hide the right panel entirely on screens <= 601px */
        @media (max-width: 600px) {
            .split-screen-container {
                display: block; /* Ensure it overrides grid */
            }
            .right-panel {
                display: none; /* Hide right panel */
            }
        }
    </style>
</head>
<body>
<div class="split-screen-container">
    <!-- ===== LEFT PANEL ===== -->
    <div class="left-panel">
        <!-- Optionally place a logo here -->
        <div class="logo-area">
            <img src="./assets/images/random-removebg-preview.png" alt="Logo" />
        </div>

        <!-- Welcome Heading/Text -->
        <div class="welcome-text">
            <h1>Welcome back!</h1>
            <p>Enter to get unlimited access to data & information.</p>
        </div>

        <!-- LOGIN FORM (PHP-FUNCTIONAL) -->
        <form method="POST" class="login-form">
            <!-- Display any login error -->
            <?php if ($login_error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <!-- Username / Email Field -->
            <div class="form-group">
                <label for="username">Email</label>
                <input type="text" id="username" name="username" placeholder="Enter your mail address" required />
            </div>

            <!-- Password Field -->
            <div class="form-group password-wrapper">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required />
            </div>

            <!-- Remember me & Forgot Password -->
            <div class="options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" /> Remember me
                </label>
                <a href="#" class="forgot-password">Forgot your password ?</a>
            </div>

            <!-- Login Button -->
            <button type="submit" class="btn login-btn">Log In</button>
        </form>

        <div class="separator">
            <span>Or Login with</span>
        </div>

        <!-- Google Sign-In Button -->
        <button class="btn google-btn">
            <img
                src="./assets/google-icon-logo-svgrepo-com.svg"
                alt="Google"
            />
            Sign up with Google
        </button>

        <!-- Register Link -->
        <div class="register-link">
            Don’t have an account?
            <a href="register.php">Register here</a>
        </div>
    </div>

    <!-- ===== RIGHT PANEL (Decorative) ===== -->
    <div class="right-panel">
        <!-- Replace this with your own illustration or pattern -->
        <div class="abstract-background">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
</div>
</body>
</html>
