<?php
session_start();
require '../config/db.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $department = htmlspecialchars($_POST['department']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO EMPLOYEE (Name, Department, Email, Password, Role) VALUES (?, ?, ?, ?, 'Employee')"
            );
            $stmt->execute([$name, $department, $email, $hashedPassword]);
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $error = 'That email is already registered.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account - Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-card">
            <div class="brand">Tracker<span class="brand-dot">.</span></div>
            <h2>Create your account</h2>
            <p class="page-subtitle">New employees start here.</p>

            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" required autofocus>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" placeholder="e.g. Engineering">
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm password</label>
                    <input type="password" id="confirm" name="confirm" required minlength="8">
                </div>
                <button type="submit" class="btn" style="width: 100%;">Create Account</button>
            </form>

            <p class="auth-footer">Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </div>
</body>
</html>
