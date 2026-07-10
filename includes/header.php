<?php
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$role = $_SESSION['role'];
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body data-role="<?= htmlspecialchars($role) ?>">
    <div class="app-shell">
        <aside class="sidebar">
            <div>
                <div class="brand">Tracker<span class="brand-dot">.</span></div>
                <div class="workspace-label"><?= htmlspecialchars($role) ?> workspace</div>

                <nav class="nav-group">
                    <?php if ($role === 'Admin'): ?>
                        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="../admin/dashboard.php">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/></svg>
                            Overview
                        </a>
                        <a class="nav-link <?= $currentPage === 'users.php' ? 'active' : '' ?>" href="../admin/users.php">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Manage Users
                        </a>
                        <a class="nav-link <?= $currentPage === 'courses.php' ? 'active' : '' ?>" href="../admin/courses.php">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            Manage Courses
                        </a>
                    <?php elseif ($role === 'Manager'): ?>
                        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="../manager/dashboard.php">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/></svg>
                            Overview
                        </a>
                        <a class="nav-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>" href="../manager/reports.php">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                            Team Reports
                        </a>
                    <?php else: ?>
                        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="../employee/dashboard.php">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/></svg>
                            My Dashboard
                        </a>
                        <a class="nav-link <?= $currentPage === 'courses.php' ? 'active' : '' ?>" href="../employee/courses.php">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            Available Courses
                        </a>
                    <?php endif; ?>
                </nav>
            </div>

            <div class="sidebar-footer">
                <a class="nav-link" href="../auth/logout.php">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    Log Out
                </a>
            </div>
        </aside>

        <main class="main-content">
            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
                <div class="alert <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>
