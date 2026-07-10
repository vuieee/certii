<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../' . dashboardRelativePath($_SESSION['role']));
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM EMPLOYEE WHERE Email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user_id'] = $user['EmployeeID'];
        $_SESSION['role'] = $user['Role'];
        $_SESSION['name'] = $user['Name'];

        header('Location: ../' . dashboardRelativePath($user['Role']));
        exit();
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-card">
            <div class="brand">Tracker<span class="brand-dot">.</span></div>
            <h2>Welcome back</h2>
            <p class="page-subtitle">Sign in to continue to your workspace.</p>

            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Sign In</button>
            </form>

            <p class="auth-footer">Don't have an account? <a href="register.php">Create one</a></p>
        </div>
    </div>
</body>
</html>
