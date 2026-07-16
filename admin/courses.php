<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin', 'Manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructor_name'])) {
    $name = htmlspecialchars($_POST['instructor_name']);
    $expertise = htmlspecialchars($_POST['instructor_expertise']);
    $type = $_POST['instructor_type'];

    $stmt = $pdo->prepare('INSERT INTO INSTRUCTOR (Name, Expertise, Type) VALUES (?, ?, ?)');
    $stmt->execute([$name, $expertise, $type]);
    flash('success', 'Instructor added.');
    header('Location: courses.php');
    exit();
}

require '../includes/header.php';

$courses = $pdo->query('
    SELECT c.CourseID, c.Title, c.Category, c.CourseFile, i.Name AS InstructorName
    FROM COURSE c
    LEFT JOIN INSTRUCTOR i ON c.InstructorID = i.InstructorID
    ORDER BY c.Title
')->fetchAll();

$instructors = $pdo->query('SELECT * FROM INSTRUCTOR ORDER BY Name')->fetchAll();
?>

<div class="eyebrow">Admin Workspace</div>
<h1>Manage Courses</h1>
<p class="page-subtitle">The training catalog and its instructors.</p>

<div class="card">
    <div class="card-row">
        <h2>Course Catalog</h2>
        <a href="course_create.php" class="btn btn-sm">+ Add Course</a>
    </div>
    <table>
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Instructor</th>
            <th>File</th>
            <th>Actions</th>
        </tr>
        <?php if (empty($courses)): ?>
            <tr><td colspan="5" class="empty-state">No courses yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($courses as $course): ?>
        <tr>
            <td><?= htmlspecialchars($course['Title']) ?></td>
            <td><?= htmlspecialchars($course['Category'] ?? 'General') ?></td>
            <td><?= htmlspecialchars($course['InstructorName'] ?? 'Unassigned') ?></td>
            <td>
                <?php if (!empty($course['CourseFile'])): ?>
                    <a href="../<?= htmlspecialchars($course['CourseFile']) ?>" target="_blank">Open</a>
                <?php else: ?>
                    None
                <?php endif; ?>
            </td>
            <td class="text-actions">
                <a class="edit" href="course_edit.php?id=<?= $course['CourseID'] ?>">Edit</a>
                <a class="delete" href="course_delete.php?id=<?= $course['CourseID'] ?>" onclick="return confirm('Delete this course? Enrollments and certifications tied to it will also be removed.')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <div class="card-row">
        <h2>Instructors</h2>
    </div>
    <table>
        <tr>
            <th>Name</th>
            <th>Expertise</th>
            <th>Type</th>
        </tr>
        <?php foreach ($instructors as $instructor): ?>
        <tr>
            <td><?= htmlspecialchars($instructor['Name']) ?></td>
            <td><?= htmlspecialchars($instructor['Expertise']) ?></td>
            <td><span class="badge neutral"><?= htmlspecialchars($instructor['Type']) ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <form method="POST" style="margin-top: 1.5rem;">
        <div class="form-group">
            <label for="instructor_name">Instructor name</label>
            <input type="text" id="instructor_name" name="instructor_name" required>
        </div>
        <div class="form-group">
            <label for="instructor_expertise">Expertise</label>
            <input type="text" id="instructor_expertise" name="instructor_expertise" required>
        </div>
        <div class="form-group">
            <label for="instructor_type">Type</label>
            <select id="instructor_type" name="instructor_type" required>
                <option value="Internal">Internal</option>
                <option value="External">External</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm">Add Instructor</button>
    </form>
</div>

<?php require '../includes/footer.php'; ?>
