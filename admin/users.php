<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);

$users = $pdo->query('SELECT EmployeeID, Name, Email, Department, Role FROM EMPLOYEE ORDER BY Name')->fetchAll();
$employeeRows = $pdo->query(
    'SELECT e.EmployeeID, e.Name, e.Department, c.Title AS CourseTitle, i.Name AS InstructorName, ce.Status
     FROM EMPLOYEE e
     LEFT JOIN COURSE_ENROLLMENT ce ON e.EmployeeID = ce.EmployeeID
     LEFT JOIN COURSE c ON ce.CourseID = c.CourseID
     LEFT JOIN INSTRUCTOR i ON c.InstructorID = i.InstructorID
     WHERE e.Role = "Employee"
     ORDER BY e.Name, ce.EnrollmentDate DESC'
)->fetchAll();

$employees = [];
foreach ($employeeRows as $row) {
    $id = $row['EmployeeID'];
    if (!isset($employees[$id])) {
        $employees[$id] = [
            'EmployeeID' => $id,
            'Name' => $row['Name'],
            'Department' => $row['Department'],
            'courses' => [],
            'completed' => 0,
            'in_progress' => 0,
        ];
    }

    if ($row['CourseTitle']) {
        $employees[$id]['courses'][] = [
            'title' => $row['CourseTitle'],
            'instructor' => $row['InstructorName'] ?? 'Unassigned',
            'status' => $row['Status'],
        ];

        if ($row['Status'] === 'Completed') {
            $employees[$id]['completed']++;
        } elseif ($row['Status'] === 'In Progress') {
            $employees[$id]['in_progress']++;
        }
    }
}

$employees = array_slice($employees, 0, 10, true);

require '../includes/header.php';
?>

<div class="eyebrow">Admin Workspace</div>
<h1>Manage Users</h1>
<p class="page-subtitle">Every account with access to the tracker.</p>

<div class="card">
    <div class="card-row">
        <h2>Employee Training Snapshot</h2>
        <span style="color: var(--ink-soft);">Showing the first 10 employee records with course progress and assigned instructor.</span>
    </div>
    <table>
        <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Courses</th>
            <th>Progress</th>
        </tr>
        <?php if (empty($employees)): ?>
            <tr><td colspan="4" class="empty-state">No employees are currently enrolled in courses.</td></tr>
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