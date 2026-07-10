<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Manager', 'Admin']);
require '../includes/header.php';
?>

<div class="eyebrow">Manager Workspace</div>
<h1>Team Overview</h1>
<p class="page-subtitle">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>. Here's how your team is tracking.</p>

<div class="card">
    <div class="card-row">
        <h2>Team Compliance</h2>
        <a href="reports.php" class="btn btn-sm btn-secondary">Full Report</a>
    </div>
    <table>
        <tr>
            <th>Employee Name</th>
            <th>Department</th>
            <th>Active Courses</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT Name, Department, EmployeeID FROM EMPLOYEE WHERE Role = 'Employee'");
        $team = $stmt->fetchAll();

        foreach ($team as $member):
            $enrollStmt = $pdo->prepare("SELECT COUNT(*) FROM COURSE_ENROLLMENT WHERE EmployeeID = ? AND Status = 'In Progress'");
            $enrollStmt->execute([$member['EmployeeID']]);
            $activeCourses = $enrollStmt->fetchColumn();
        ?>
        <tr>
            <td><?= htmlspecialchars($member['Name']) ?></td>
            <td><?= htmlspecialchars($member['Department'] ?? 'N/A') ?></td>
            <td><?= $activeCourses ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($team)): ?>
            <tr><td colspan="3" class="empty-state">No employees assigned yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div class="card">
    <h2>My Personal Training</h2>
    <p class="page-subtitle" style="margin-top: 0.75rem; margin-bottom: 0;">
        Managers keep their own certifications current too.
        <br><a href="../employee/dashboard.php" style="color: var(--accent-2); font-weight: 600; text-decoration: none;">&rarr; View my training progress</a>
    </p>
</div>

<?php require '../includes/footer.php'; ?>
