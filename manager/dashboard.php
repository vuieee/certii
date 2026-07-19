<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Manager', 'Admin']);

// Fetch all employees
$teamStmt = $pdo->query("SELECT EmployeeID, Name, Department FROM EMPLOYEE WHERE Role = 'Employee' ORDER BY Name");
$team = $teamStmt->fetchAll();

$employees = [];
foreach ($team as $member) {
    $employees[$member['EmployeeID']] = [
        'Name' => $member['Name'],
        'Department' => $member['Department'],
        'courses' => [],
        'completed' => 0,
        'in_progress' => 0,
    ];
}

// Fetch all enrollments for employees
$courseStmt = $pdo->query("SELECT ce.EmployeeID, c.Title, i.Name AS InstructorName, ce.Status
    FROM COURSE_ENROLLMENT ce
    JOIN COURSE c ON ce.CourseID = c.CourseID
    LEFT JOIN INSTRUCTOR i ON c.InstructorID = i.InstructorID
    WHERE ce.EmployeeID IN (SELECT EmployeeID FROM EMPLOYEE WHERE Role = 'Employee')");

foreach ($courseStmt->fetchAll() as $row) {
    if (!isset($employees[$row['EmployeeID']])) {
        continue;
    }
    $employees[$row['EmployeeID']]['courses'][] = [
        'title' => $row['Title'],
        'instructor' => $row['InstructorName'] ?? 'Unassigned',
        'status' => $row['Status'],
    ];
    if ($row['Status'] === 'Completed') {
        $employees[$row['EmployeeID']]['completed']++;
    } elseif ($row['Status'] === 'In Progress') {
        $employees[$row['EmployeeID']]['in_progress']++;
    }
}

// Calculate Statistics for Graph
$totalCompleted = 0;
$totalInProgress = 0;
foreach ($employees as $emp) {
    $totalCompleted += $emp['completed'];
    $totalInProgress += $emp['in_progress'];
}
$totalEnrollments = $totalCompleted + $totalInProgress;
$completionPercentage = $totalEnrollments > 0 ? round(($totalCompleted / $totalEnrollments) * 100) : 0;

// Fetch Recent Certifications Issued for Stats Table
$statsStmt = $pdo->query("
    SELECT c.Title, e.Name, cert.IssueDate
    FROM CERTIFICATION cert
    JOIN EMPLOYEE e ON cert.EmployeeID = e.EmployeeID
    JOIN COURSE c ON cert.CourseID = c.CourseID
    WHERE e.Role = 'Employee'
    ORDER BY cert.IssueDate DESC
    LIMIT 5
");
$recentCerts = $statsStmt->fetchAll();

require '../includes/header.php';
?>

<div class="eyebrow">Manager Workspace</div>
<h1>Team Overview</h1>
<p class="page-subtitle">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>. Here's how your team is tracking.</p>

<div class="card">
    <div class="card-row">
        <h2>Team progress</h2>
        <a href="reports.php" class="btn btn-sm btn-secondary">Full Report</a>
    </div>
    <table>
        <tr>
            <th>Employee Name</th>
            <th>Department</th>
            <th>Courses</th>
            <th>Progress</th>
        </tr>
        <?php if (empty($employees)): ?>
            <tr><td colspan="4" class="empty-state">No employees assigned yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($employees as $employee): ?>
            <tr>
                <td><?= htmlspecialchars($employee['Name']) ?></td>
                <td><?= htmlspecialchars($employee['Department'] ?? 'N/A') ?></td>
                <td>
                    <?php if (!empty($employee['courses'])): ?>
                        <ul style="margin:0; padding-left:1.2rem; list-style: disc;">
                            <?php foreach ($employee['courses'] as $course): ?>
                                <li><?= htmlspecialchars($course['title']) ?> (<?= htmlspecialchars($course['instructor']) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <span class="badge neutral">No enrollments</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge valid"><?= $employee['completed'] ?> completed</span>
                    <span class="badge neutral" style="margin-left:0.5rem;"><?= $employee['in_progress'] ?> in progress</span>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card" style="margin-top: 2rem;">
    <h2>Team Statistics & Progress</h2>
    <div style="display: flex; gap: 2.5rem; margin-top: 1.5rem; flex-wrap: wrap;">
        
        <!-- Statistics Graph / Progress Bar -->
        <div style="flex: 1; min-width: 250px; background: #fafafa; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
            <h3 style="margin-top:0; font-size: 1rem; color: var(--ink-soft);">Overall Completion Rate</h3>
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                <div style="flex-grow: 1; background: #e0e0e0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: <?= $completionPercentage ?>%; background: #2e7d32; height: 100%; transition: width 0.5s ease-in-out;"></div>
                </div>
                <strong style="font-size: 1.25rem;"><?= $completionPercentage ?>%</strong>
            </div>
            
            <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; color: var(--ink-soft); font-size: 0.875rem;">
                <span><strong><?= $totalCompleted ?></strong> Courses Completed</span>
                <span><strong><?= $totalInProgress ?></strong> In Progress</span>
            </div>
            <div style="margin-top: 0.5rem; color: var(--ink-soft); font-size: 0.875rem;">
                <span>Total Active Enrollments: <strong><?= $totalEnrollments ?></strong></span>
            </div>
        </div>

        <!-- Recent Certifications Table -->
        <div style="flex: 2; min-width: 300px;">
            <h3 style="margin-top:0; font-size: 1rem; margin-bottom: 1rem; color: var(--ink-soft);">Recently Issued Certifications</h3>
            <table style="margin: 0; width: 100%; text-align: left; border-collapse: collapse;">
                <tr>
                    <th style="padding: 0 0 0.75rem 0; border-bottom: 2px solid var(--border-color); color: var(--ink-soft); font-size: 0.875rem;">Employee</th>
                    <th style="padding: 0 0 0.75rem 0; border-bottom: 2px solid var(--border-color); color: var(--ink-soft); font-size: 0.875rem;">Course</th>
                    <th style="padding: 0 0 0.75rem 0; border-bottom: 2px solid var(--border-color); color: var(--ink-soft); font-size: 0.875rem;">Date Issued</th>
                </tr>
                <?php if (empty($recentCerts)): ?>
                    <tr><td colspan="3" class="empty-state" style="padding: 1rem 0;">No recent certifications.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentCerts as $cert): ?>
                    <tr>
                        <td style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);"><?= htmlspecialchars($cert['Name']) ?></td>
                        <td style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);"><?= htmlspecialchars($cert['Title']) ?></td>
                        <td style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);"><?= htmlspecialchars($cert['IssueDate']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
        
    </div>
</div>

<?php require '../includes/footer.php'; ?>