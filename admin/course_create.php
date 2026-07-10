<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $category = htmlspecialchars($_POST['category']);
    $instructorId = $_POST['instructor_id'] !== '' ? (int) $_POST['instructor_id'] : null;

    $stmt = $pdo->prepare('INSERT INTO COURSE (InstructorID, Title, Category) VALUES (?, ?, ?)');
    $stmt->execute([$instructorId, $title, $category]);

    flash('success', 'Course created successfully.');
    header('Location: courses.php');
    exit();
}

require '../includes/header.php';

$instructors = $pdo->query('SELECT * FROM INSTRUCTOR ORDER BY Name')->fetchAll();
?>

<div class="eyebrow">Admin Workspace</div>
<h1>Add Course</h1>
<p class="page-subtitle">Add a new course to the training catalog.</p>

<div class="card form-card">
    <form method="POST">
        <div class="form-group">
            <label for="title">Course title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" placeholder="e.g. Security, Compliance">
        </div>
        <div class="form-group">
            <label for="instructor_id">Instructor</label>
            <select id="instructor_id" name="instructor_id">
                <option value="">Unassigned</option>
                <?php foreach ($instructors as $instructor): ?>
                    <option value="<?= $instructor['InstructorID'] ?>"><?= htmlspecialchars($instructor['Name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Create Course</button>
            <a href="courses.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require '../includes/footer.php'; ?>
