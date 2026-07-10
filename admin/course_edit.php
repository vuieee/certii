<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM COURSE WHERE CourseID = ?');
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    flash('error', 'Course not found.');
    header('Location: courses.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $category = htmlspecialchars($_POST['category']);
    $instructorId = $_POST['instructor_id'] !== '' ? (int) $_POST['instructor_id'] : null;

    $stmt = $pdo->prepare('UPDATE COURSE SET Title = ?, Category = ?, InstructorID = ? WHERE CourseID = ?');
    $stmt->execute([$title, $category, $instructorId, $id]);

    flash('success', 'Course updated successfully.');
    header('Location: courses.php');
    exit();
}

require '../includes/header.php';

$instructors = $pdo->query('SELECT * FROM INSTRUCTOR ORDER BY Name')->fetchAll();
?>

<div class="eyebrow">Admin Workspace</div>
<h1>Edit Course</h1>
<p class="page-subtitle">Update <?= htmlspecialchars($course['Title']) ?>.</p>

<div class="card form-card">
    <form method="POST">
        <div class="form-group">
            <label for="title">Course title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($course['Title']) ?>" required>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($course['Category'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="instructor_id">Instructor</label>
            <select id="instructor_id" name="instructor_id">
                <option value="">Unassigned</option>
                <?php foreach ($instructors as $instructor): ?>
                    <option value="<?= $instructor['InstructorID'] ?>" <?= $course['InstructorID'] == $instructor['InstructorID'] ? 'selected' : '' ?>><?= htmlspecialchars($instructor['Name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Save Changes</button>
            <a href="courses.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require '../includes/footer.php'; ?>
