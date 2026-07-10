<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);
require '../includes/header.php';

$users = $pdo->query('SELECT EmployeeID, Name, Email, Department, Role FROM EMPLOYEE ORDER BY Name')->fetchAll();
?>

<div class="eyebrow">Admin Workspace</div>
<h1>Manage Users</h1>
<p class="page-subtitle">Every account with access to the tracker.</p>

<div class="card">
    <div class="card-row">
        <h2>System Users</h2>
        <a href="user_create.php" class="btn btn-sm">+ Add User</a>
    </div>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['Name']) ?></td>
            <td><?= htmlspecialchars($u['Email']) ?></td>
            <td><?= htmlspecialchars($u['Department'] ?? 'N/A') ?></td>
            <td><span class="badge neutral"><?= htmlspecialchars($u['Role']) ?></span></td>
            <td class="text-actions">
                <a class="edit" href="user_edit.php?id=<?= $u['EmployeeID'] ?>">Edit</a>
                <?php if ($u['EmployeeID'] != $_SESSION['user_id']): ?>
                <a class="delete" href="user_delete.php?id=<?= $u['EmployeeID'] ?>" onclick="return confirm('Delete this user? This cannot be undone.')">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require '../includes/footer.php'; ?>
