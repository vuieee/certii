<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin', 'Manager']);

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
    $courseFilePath = $course['CourseFile'];

    if (!empty($_FILES['course_file']['tmp_name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['course_file']['name']));
        $newPath = 'uploads/' . uniqid('course_', true) . '_' . $fileName;

        if (move_uploaded_file($_FILES['course_file']['tmp_name'], __DIR__ . '/../' . $newPath)) {
            if ($courseFilePath && file_exists(__DIR__ . '/../' . $courseFilePath)) {
                unlink(__DIR__ . '/../' . $courseFilePath);
            }
            $courseFilePath = $newPath;
        }
    }

    $stmt = $pdo->prepare('UPDATE COURSE SET Title = ?, Category = ?, InstructorID = ?, CourseFile = ? WHERE CourseID = ?');
    $stmt->execute([$title, $category, $instructorId, $courseFilePath, $id]);

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
    <form method="POST" enctype="multipart/form-data">
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
        <div class="form-group">
            <label for="course_file">Course file</label>
            <input type="file" id="course_file" name="course_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip">
            <?php if (!empty($course['CourseFile'])): ?>
                <div class="form-hint">Current file: <a href="../<?= htmlspecialchars($course['CourseFile']) ?>" target="_blank"><?= htmlspecialchars(basename($course['CourseFile'])) ?></a></div>
            <?php endif; ?>
            <div class="form-hint">Upload a new file to replace the mock course content.</div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Save Changes</button>
            <a href="courses.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require '../includes/footer.php'; ?>
