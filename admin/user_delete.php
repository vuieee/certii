<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);

$id = (int) ($_GET['id'] ?? 0);

if ($id === (int) $_SESSION['user_id']) {
    flash('error', 'You cannot delete your own account.');
} else {
    $stmt = $pdo->prepare('DELETE FROM EMPLOYEE WHERE EmployeeID = ?');
    $stmt->execute([$id]);
    flash('success', 'User deleted.');
}

header('Location: users.php');
exit();
