<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);
require '../includes/header.php';

$totalEmployees = $pdo->query("SELECT COUNT(*) FROM EMPLOYEE WHERE Role = 'Employee'")->fetchColumn();
$activeEnrollments = $pdo->query("SELECT COUNT(*) FROM COURSE_ENROLLMENT WHERE Status = 'In Progress'")->fetchColumn();
$totalCourses = $pdo->query('SELECT COUNT(*) FROM COURSE')->fetchColumn();
$expiringSoon = $pdo->query("
    SELECT COUNT(*) FROM CERTIFICATION
    WHERE ExpirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetchColumn();
?>

<div class="eyebrow">Admin Workspace</div>
<h1>System Overview</h1>
<p class="page-subtitle">A snapshot of training activity across the organization.</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Employees</div>
        <div class="stat-value"><?= $totalEmployees ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Enrollments</div>
        <div class="stat-value"><?= $activeEnrollments ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Courses Offered</div>
        <div class="stat-value"><?= $totalCourses ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Expiring Within 30 Days</div>
        <div class="stat-value"><?= $expiringSoon ?></div>
    </div>
</div>

<div class="card">
    <div class="card-row">
        <h2>Quick Links</h2>
    </div>
    <p class="page-subtitle" style="margin-bottom: 0;">
        Head to <a href="users.php" style="color: var(--accent-2); font-weight: 600; text-decoration: none;">Manage Users</a>
        to add or edit accounts, or <a href="courses.php" style="color: var(--accent-2); font-weight: 600; text-decoration: none;">Manage Courses</a>
        to update the training catalog.
    </p>
</div>

<?php require '../includes/footer.php'; ?>
