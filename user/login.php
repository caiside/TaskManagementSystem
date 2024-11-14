<?php
// user/login.php
require '../config.php';
require '../includes/functions.php';

// Remove the session_start() below if you have it in config.php
// session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $usernameOrEmail = trim($_POST['username_or_email']);
    $password        = $_POST['password'];

    // Validate inputs
    if (empty($usernameOrEmail) || empty($password)) {
        $error = 'Please enter your username/email and password.';
    } else {
        // Retrieve user data
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['email']     = $user['email']; // Add this line

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>

<!-- HTML Login Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <title>Login</title>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <!-- Form fields -->
            <input type="text" name="username_or_email" placeholder="Username or Email" value="<?= htmlspecialchars($usernameOrEmail ?? '') ?>">
            <input type="password" name="password" placeholder="Password">
            <button type="submit">Login</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
