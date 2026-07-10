<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $department = htmlspecialchars($_POST['department']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!in_array($role, ['Employee', 'Manager', 'Admin'], true)) {
        $error = 'Invalid role selected.';
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO EMPLOYEE (Name, Department, Email, Password, Role) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$name, $department, $email, $hashedPassword, $role]);
            flash('success', 'User created successfully.');
            header('Location: users.php');
            exit();
        } catch (PDOException $e) {
            $error = 'That email is already in use.';
        }
    }
}

require '../includes/header.php';
?>

<div class="eyebrow">Admin Workspace</div>
<h1>Add User</h1>
<p class="page-subtitle">Create a new account and assign its role.</p>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card form-card">
    <form method="POST">
        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department">
        </div>
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="Employee">Employee</option>
                <option value="Manager">Manager</option>
                <option value="Admin">Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Temporary password</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Create User</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require '../includes/footer.php'; ?>
