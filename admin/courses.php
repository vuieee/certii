<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin', 'Manager']);

// Handle POST requests for Instructors (Add & Remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'remove_instructor') {
        $instructorId = (int)$_POST['instructor_id'];

        // Unassign instructor from courses before deleting to prevent DB errors
        $stmt = $pdo->prepare('UPDATE COURSE SET InstructorID = NULL WHERE InstructorID = ?');
        $stmt->execute([$instructorId]);

        // Delete instructor
        $stmt = $pdo->prepare('DELETE FROM INSTRUCTOR WHERE InstructorID = ?');
        $stmt->execute([$instructorId]);

        flash('success', 'Instructor successfully removed.');
        header('Location: courses.php');
        exit();
    } elseif (isset($_POST['instructor_name'])) {
        $name = htmlspecialchars($_POST['instructor_name']);
        $expertise = htmlspecialchars($_POST['instructor_expertise']);
        $type = $_POST['instructor_type'];

        $stmt = $pdo->prepare('INSERT INTO INSTRUCTOR (Name, Expertise, Type) VALUES (?, ?, ?)');
        $stmt->execute([$name, $expertise, $type]);
        
        flash('success', 'Instructor added.');
        header('Location: courses.php');
        exit();
    }
}

require '../includes/header.php';

$courses = $pdo->query('
    SELECT c.CourseID, c.Title, c.Category, c.CourseFile, i.Name AS InstructorName
    FROM COURSE c
    LEFT JOIN INSTRUCTOR i ON c.InstructorID = i.InstructorID
    ORDER BY c.Title
')->fetchAll();

// Fetch instructors with their course count
$instructors = $pdo->query('
    SELECT i.InstructorID, i.Name, i.Expertise, i.Type, COUNT(c.CourseID) as CourseCount
    FROM INSTRUCTOR i
    LEFT JOIN COURSE c ON i.InstructorID = c.InstructorID
    GROUP BY i.InstructorID, i.Name, i.Expertise, i.Type
    ORDER BY i.Name
')->fetchAll();
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
            <th>Assigned Courses</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($instructors as $instructor): ?>
        <tr>
            <td><?= htmlspecialchars($instructor['Name']) ?></td>
            <td><?= htmlspecialchars($instructor['Expertise']) ?></td>
            <td><span class="badge neutral"><?= htmlspecialchars($instructor['Type']) ?></span></td>
            <td><?= (int)$instructor['CourseCount'] ?> course(s)</td>
            <td class="text-actions">
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this instructor? Any courses they teach will be marked as Unassigned.');">
                    <input type="hidden" name="action" value="remove_instructor">
                    <input type="hidden" name="instructor_id" value="<?= $instructor['InstructorID'] ?>">
                    <button type="submit" class="delete" style="background: transparent; border: none; padding: 0; cursor: pointer; font-size: 0.88rem; font-weight: 500; font-family: inherit;">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <form method="POST" style="margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
        <h3 style="font-size: 1rem; margin-bottom: 1rem;">Add New Instructor</h3>
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