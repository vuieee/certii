<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin', 'Manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $category = htmlspecialchars($_POST['category']);
    $instructorId = $_POST['instructor_id'] !== '' ? (int) $_POST['instructor_id'] : null;

    $stmt = $pdo->prepare('INSERT INTO COURSE (InstructorID, Title, Category) VALUES (?, ?, ?)');
    $stmt->execute([$instructorId, $title, $category]);
    $courseId = (int) $pdo->lastInsertId();

    $courseFilePath = null;
    if (!empty($_FILES['course_file']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['course_file']['name']));
        $filePath = 'uploads/' . uniqid('course_', true) . '_' . $fileName;

        if (move_uploaded_file($_FILES['course_file']['tmp_name'], __DIR__ . '/../' . $filePath)) {
            $courseFilePath = $filePath;
        }
    }

    if ($courseFilePath !== null) {
        $stmt = $pdo->prepare('UPDATE COURSE SET CourseFile = ? WHERE CourseID = ?');
        $stmt->execute([$courseFilePath, $courseId]);
    }

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
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Course title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" placeholder="e.g. Security, Compliance">
        </div>
        <div class="form-group">
            <label for="course_file">Course file</label>
            <input type="file" id="course_file" name="course_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip">
            <div class="form-hint">Upload a PDF or supporting file to represent the mock course.</div>
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
