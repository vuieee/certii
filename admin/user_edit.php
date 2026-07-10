<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM EMPLOYEE WHERE EmployeeID = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    flash('error', 'User not found.');
    header('Location: users.php');
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $department = htmlspecialchars($_POST['department']);
    $role = $_POST['role'];
    $newPassword = $_POST['password'];

    if (!in_array($role, ['Employee', 'Manager', 'Admin'], true)) {
        $error = 'Invalid role selected.';
    } else {
        try {
            if ($newPassword !== '') {
                $stmt = $pdo->prepare(
                    'UPDATE EMPLOYEE SET Name = ?, Department = ?, Email = ?, Role = ?, Password = ? WHERE EmployeeID = ?'
                );
                $stmt->execute([$name, $department, $email, $role, password_hash($newPassword, PASSWORD_BCRYPT), $id]);
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE EMPLOYEE SET Name = ?, Department = ?, Email = ?, Role = ? WHERE EmployeeID = ?'
                );
                $stmt->execute([$name, $department, $email, $role, $id]);
            }

            flash('success', 'User updated successfully.');
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
<h1>Edit User</h1>
<p class="page-subtitle">Update <?= htmlspecialchars($user['Name']) ?>'s account.</p>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card form-card">
    <form method="POST">
        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['Name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" value="<?= htmlspecialchars($user['Department'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <?php foreach (['Employee', 'Manager', 'Admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= $user['Role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="password">New password</label>
            <input type="password" id="password" name="password" minlength="8">
        </div>
        <p class="form-hint">Leave the password blank to keep the current one.</p>
        <div class="form-actions">
            <button type="submit" class="btn">Save Changes</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require '../includes/footer.php'; ?>
