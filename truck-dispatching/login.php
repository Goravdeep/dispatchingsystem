<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple authentication (replace with secure authentication)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user'] = 'admin';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Truck Dispatching</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h1>🚛 Truck Dispatching</h1>
        <form method="POST">
            <div class="input-group">
                <label>Username:</label>
                <input type="text" name="username" value="admin" required>
            </div>
            <div class="input-group">
                <label>Password:</label>
                <input type="password" name="password" value="admin123" required>
            </div>
            <button type="submit" name="login">Login</button>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        </form>
        <p class="demo-credentials">Demo: admin / admin123</p>
    </div>
</body>
</html>