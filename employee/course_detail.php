<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Employee', 'Manager', 'Admin']);

$userId = $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_course') {
    $stmt = $pdo->prepare('SELECT EnrollmentID, Status FROM COURSE_ENROLLMENT WHERE EmployeeID = ? AND CourseID = ?');
    $stmt->execute([$userId, $id]);
    $enrollment = $stmt->fetch();

    if (!$enrollment) {
        flash('error', 'You must enroll before completing this course.');
    } elseif ($enrollment['Status'] === 'Completed') {
        flash('success', 'This course is already marked as completed.');
    } else {
        $stmt = $pdo->prepare('UPDATE COURSE_ENROLLMENT SET Status = ? WHERE EnrollmentID = ?');
        $stmt->execute(['Completed', $enrollment['EnrollmentID']]);

        $stmt = $pdo->prepare('SELECT 1 FROM CERTIFICATION WHERE EmployeeID = ? AND CourseID = ?');
        $stmt->execute([$userId, $id]);

        if (!$stmt->fetch()) {
            $issueDate = (new DateTime())->format('Y-m-d');
            $expirationDate = (new DateTime('+1 year'))->format('Y-m-d');
            $stmt = $pdo->prepare('INSERT INTO CERTIFICATION (EmployeeID, CourseID, IssueDate, ExpirationDate) VALUES (?, ?, ?, ?)');
            $stmt->execute([$userId, $id, $issueDate, $expirationDate]);
        }

        flash('success', 'Course completed! A mock certification has been issued.');
    }

    header('Location: course_detail.php?id=' . $id);
    exit();
}

$stmt = $pdo->prepare('SELECT c.CourseID, c.Title, c.Category, c.CourseFile, i.Name AS InstructorName, e.Status AS EnrollmentStatus, cert.IssueDate, cert.ExpirationDate
    FROM COURSE c
    LEFT JOIN INSTRUCTOR i ON c.InstructorID = i.InstructorID
    LEFT JOIN COURSE_ENROLLMENT e ON c.CourseID = e.CourseID AND e.EmployeeID = ?
    LEFT JOIN CERTIFICATION cert ON c.CourseID = cert.CourseID AND cert.EmployeeID = ?
    WHERE c.CourseID = ?
');
$stmt->execute([$userId, $userId, $id]);
$course = $stmt->fetch();

if (!$course) {
    flash('error', 'Course not found.');
    header('Location: courses.php');
    exit();
}

$instructions = [
    'Read the course overview and objectives.',
    'Review the provided file materials carefully.',
    'Answer the included practice questions honestly.',
    'Note important compliance and safety requirements.',
    'Apply the guidance to daily workflows.',
    'Track your progress after each section.',
    'Reach out to your instructor for clarification.',
    'Use the mock questions to check comprehension.',
    'Complete the course to earn a mock certification.',
    'Download the certificate once the course is finished.'
];

require '../includes/header.php';
?>

<div class="eyebrow">Course Preview</div>
<h1><?= htmlspecialchars($course['Title']) ?></h1>
<p class="page-subtitle"><?= htmlspecialchars($course['Category'] ?? 'General training') ?> — Instructor: <?= htmlspecialchars($course['InstructorName'] ?? 'Unassigned') ?></p>

<div class="card">
    <div class="card-row">
        <div>
            <strong>Status:</strong>
            <?php if ($course['EnrollmentStatus'] === 'Completed'): ?>
                <span class="badge valid">Completed</span>
            <?php elseif ($course['EnrollmentStatus'] === 'In Progress'): ?>
                <span class="badge neutral">In Progress</span>
            <?php else: ?>
                <span class="badge neutral">Not Enrolled</span>
            <?php endif; ?>
        </div>
        <?php if (!empty($course['CourseFile'])): ?>
            <a class="btn btn-sm" href="../<?= htmlspecialchars($course['CourseFile']) ?>" target="_blank">Open Course File</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($course['IssueDate']) && !empty($course['ExpirationDate'])): ?>
        <div class="card-row" style="margin-top: 1rem;">
            <div><strong>Certification issued:</strong> <?= htmlspecialchars($course['IssueDate']) ?></div>
            <div><strong>Expires:</strong> <?= htmlspecialchars($course['ExpirationDate']) ?></div>
        </div>
    <?php endif; ?>

    <div style="margin-top:1.5rem;">
        <h3>10 Mock Instructions</h3>
        <ol>
            <?php foreach ($instructions as $instruction): ?>
                <li><?= htmlspecialchars($instruction) ?></li>
            <?php endforeach; ?>
        </ol>
    </div>

    <div style="margin-top:1.5rem;">
        <h3>10 Mock Questions</h3>
        <ol>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <li>Mock question <?= $i ?> for <?= htmlspecialchars($course['Title']) ?>.</li>
            <?php endfor; ?>
        </ol>
    </div>

    <?php if ($course['EnrollmentStatus'] === 'In Progress'): ?>
        <form method="POST" style="margin-top: 1.5rem;">
            <input type="hidden" name="action" value="complete_course">
            <button type="submit" class="btn">Complete Course and Issue Certificate</button>
        </form>
    <?php elseif ($course['EnrollmentStatus'] !== 'Completed'): ?>
        <p style="margin-top:1.5rem; color: var(--ink-soft);">Enroll from the catalog page first to start this mock course.</p>
    <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>