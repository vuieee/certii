<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Employee', 'Manager', 'Admin']);
require '../includes/header.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('
    SELECT c.Title, c.Category, e.Status, e.EnrollmentDate
    FROM COURSE_ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.EmployeeID = ?
    ORDER BY e.EnrollmentDate DESC
');
$stmt->execute([$userId]);
$enrollments = $stmt->fetchAll();

$stmt = $pdo->prepare('
    SELECT c.Title, cert.IssueDate, cert.ExpirationDate
    FROM CERTIFICATION cert
    JOIN COURSE c ON cert.CourseID = c.CourseID
    WHERE cert.EmployeeID = ?
    ORDER BY cert.ExpirationDate ASC
');
$stmt->execute([$userId]);
$certifications = $stmt->fetchAll();
?>

<div class="eyebrow">My Workspace</div>
<h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
<p class="page-subtitle">Here's where your certifications and training stand today.</p>

<div class="card">
    <div class="card-row">
        <h2>My Certifications</h2>
    </div>
    <table>
        <tr>
            <th>Course</th>
            <th>Issue Date</th>
            <th>Expiration Date</th>
            <th>Status</th>
        </tr>
        <?php if (empty($certifications)): ?>
            <tr><td colspan="4" class="empty-state">No certifications on record yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($certifications as $cert): $status = certStatus($cert['ExpirationDate']); ?>
        <tr>
            <td><?= htmlspecialchars($cert['Title']) ?></td>
            <td><?= htmlspecialchars($cert['IssueDate']) ?></td>
            <td><?= htmlspecialchars($cert['ExpirationDate']) ?></td>
            <td><span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <div class="card-row">
        <h2>Training Progress</h2>
    </div>
    <table>
        <tr>
            <th>Course</th>
            <th>Category</th>
            <th>Enrollment Date</th>
            <th>Status</th>
        </tr>
        <?php if (empty($enrollments)): ?>
            <tr><td colspan="4" class="empty-state">You're not enrolled in any courses yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($enrollments as $enr): ?>
        <tr>
            <td><?= htmlspecialchars($enr['Title']) ?></td>
            <td><?= htmlspecialchars($enr['Category']) ?></td>
            <td><?= htmlspecialchars($enr['EnrollmentDate']) ?></td>
            <td><span class="badge <?= $enr['Status'] === 'Completed' ? 'valid' : 'neutral' ?>"><?= htmlspecialchars($enr['Status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require '../includes/footer.php'; ?>
