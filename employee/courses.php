<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Employee', 'Manager', 'Admin']);

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $courseId = (int) $_POST['course_id'];

    $stmt = $pdo->prepare('SELECT 1 FROM COURSE_ENROLLMENT WHERE EmployeeID = ? AND CourseID = ?');
    $stmt->execute([$userId, $courseId]);

    if ($stmt->fetch()) {
        flash('error', 'You are already enrolled in that course.');
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO COURSE_ENROLLMENT (EmployeeID, CourseID, EnrollmentDate, Status) VALUES (?, ?, CURDATE(), 'In Progress')"
        );
        $stmt->execute([$userId, $courseId]);
        flash('success', 'Enrolled successfully.');
    }

    header('Location: courses.php');
    exit();
}

require '../includes/header.php';

$stmt = $pdo->prepare('
    SELECT c.CourseID, c.Title, c.Category, c.CourseFile, i.Name AS InstructorName
    FROM COURSE c
    LEFT JOIN INSTRUCTOR i ON c.InstructorID = i.InstructorID
    WHERE c.CourseID NOT IN (
        SELECT CourseID FROM COURSE_ENROLLMENT WHERE EmployeeID = ?
    )
    ORDER BY c.Title
');
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();
?>

<div class="eyebrow">Training Catalog</div>
<h1>Available Courses</h1>
<p class="page-subtitle">Enroll in a course to start tracking your progress toward certification.</p>

<div class="card">
    <table>
        <tr>
            <th>Course</th>
            <th>Category</th>
            <th>Instructor</th>
            <th>File</th>
            <th></th>
        </tr>
        <?php if (empty($courses)): ?>
            <tr><td colspan="5" class="empty-state">You're enrolled in everything currently offered.</td></tr>
        <?php endif; ?>
        <?php foreach ($courses as $course): ?>
        <tr>
            <td><?= htmlspecialchars($course['Title']) ?></td>
            <td><?= htmlspecialchars($course['Category'] ?? 'General') ?></td>
            <td><?= htmlspecialchars($course['InstructorName'] ?? 'Unassigned') ?></td>
            <td>
                <?php if (!empty($course['CourseFile'])): ?>
                    <a href="../<?= htmlspecialchars($course['CourseFile']) ?>" target="_blank">Download</a>
                <?php else: ?>
                    None
                <?php endif; ?>
            </td>
            <td class="text-actions">
                <a class="edit" href="course_detail.php?id=<?= $course['CourseID'] ?>">View</a>
                <form method="POST" style="display:inline; margin-left:0.75rem;">
                    <input type="hidden" name="course_id" value="<?= $course['CourseID'] ?>">
                    <button type="submit" class="btn btn-sm">Enroll</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require '../includes/footer.php'; ?>
