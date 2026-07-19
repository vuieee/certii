<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Employee', 'Manager', 'Admin']);

$userId = $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);

// Initialize flag for the congratulatory modal popup
$showCongratsModal = false;

$quizQuestions = [
    1 => [
        'text' => 'Which action is most important when you first encounter a suspicious security message?',
        'choices' => [
            'A' => 'Open it and verify the sender.',
            'B' => 'Delete it immediately without reading.',
            'C' => 'Report it through the approved channel.',
            'D' => 'Forward it to a coworker.',
        ],
        'correct' => 'C',
    ],
    2 => [
        'text' => 'What makes a password strong and easy to manage?',
        'choices' => [
            'A' => 'Short and memorable, used across sites.',
            'B' => 'Long, unique, and stored in a secure manager.',
            'C' => 'Written on a sticky note near your desk.',
            'D' => 'A single word from the company handbook.',
        ],
        'correct' => 'B',
    ],
    3 => [
        'text' => 'The most effective first step in incident response is to:',
        'choices' => [
            'A' => 'Inform your manager after the incident is resolved.',
            'B' => 'Disconnect affected systems and notify security.',
            'C' => 'Share the issue on social media to raise awareness.',
            'D' => 'Restart your computer and keep working.',
        ],
        'correct' => 'B',
    ],
    4 => [
        'text' => 'When should you review company compliance training materials?',
        'choices' => [
            'A' => 'Only when your manager asks for it.',
            'B' => 'After a security incident occurs.',
            'C' => 'Regularly, as part of routine training refresh.',
            'D' => 'When you have extra free time at the end of the year.',
        ],
        'correct' => 'C',
    ],
    5 => [
        'text' => 'A certification is renewed when:',
        'choices' => [
            'A' => 'The expiration date passes without action.',
            'B' => 'You complete the course and the system records it.',
            'C' => 'You request a new badge from IT.',
            'D' => 'The course instructor approves it manually.',
        ],
        'correct' => 'B',
    ],
];

$quizResult = null;
$quizMessages = [];
$missedQuestions = [];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'enroll_course') {
        $stmt = $pdo->prepare('SELECT 1 FROM COURSE_ENROLLMENT WHERE EmployeeID = ? AND CourseID = ?');
        $stmt->execute([$userId, $id]);

        if ($stmt->fetch()) {
            flash('error', 'You are already enrolled in this course.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO COURSE_ENROLLMENT (EmployeeID, CourseID, EnrollmentDate, Status) VALUES (?, ?, CURDATE(), ?)');
            $stmt->execute([$userId, $id, 'In Progress']);
            flash('success', 'You are now enrolled. Scroll down to take the quiz.');
        }

        header('Location: course_detail.php?id=' . $id);
        exit();
    }
    
    if ($_POST['action'] === 'unenroll_course') {
        $stmt = $pdo->prepare('DELETE FROM COURSE_ENROLLMENT WHERE EmployeeID = ? AND CourseID = ? AND Status = ?');
        $stmt->execute([$userId, $id, 'In Progress']);
        flash('success', 'You have been unenrolled from the course.');
        
        header('Location: course_detail.php?id=' . $id);
        exit();
    }

    if ($_POST['action'] === 'submit_quiz') {
        if (empty($course['EnrollmentStatus'])) {
            $quizMessages[] = 'You must enroll in the course before taking the quiz.';
        } else {
            $submittedAnswers = $_POST['quiz'] ?? [];
            $missing = array_diff(array_keys($quizQuestions), array_keys($submittedAnswers));

            if (!empty($missing)) {
                $quizMessages[] = 'Please answer all quiz questions before submitting.';
                $quizResult = null;
            } else {
                $correctCount = 0;

                foreach ($quizQuestions as $questionId => $question) {
                    $selected = strtoupper(trim($submittedAnswers[$questionId] ?? ''));
                    if ($selected === $question['correct']) {
                        $correctCount++;
                    } else {
                        $missedQuestions[] = $questionId;
                    }
                }

                $passThreshold = 4;
                $passed = $correctCount >= $passThreshold;

                if ($passed) {
                    $quizResult = [
                        'score' => $correctCount,
                        'total' => count($quizQuestions),
                        'status' => 'passed',
                    ];

                    $stmt = $pdo->prepare('SELECT EnrollmentID, Status FROM COURSE_ENROLLMENT WHERE EmployeeID = ? AND CourseID = ?');
                    $stmt->execute([$userId, $id]);
                    $enrollment = $stmt->fetch();

                    if ($enrollment && $enrollment['Status'] !== 'Completed') {
                        $stmt = $pdo->prepare('UPDATE COURSE_ENROLLMENT SET Status = ? WHERE EnrollmentID = ?');
                        $stmt->execute(['Completed', $enrollment['EnrollmentID']]);
                    }

                    $stmt = $pdo->prepare('SELECT 1 FROM CERTIFICATION WHERE EmployeeID = ? AND CourseID = ?');
                    $stmt->execute([$userId, $id]);

                    if (!$stmt->fetch()) {
                        $issueDate = (new DateTime())->format('Y-m-d');
                        $expirationDate = (new DateTime('+1 year'))->format('Y-m-d');
                        $stmt = $pdo->prepare('INSERT INTO CERTIFICATION (EmployeeID, CourseID, IssueDate, ExpirationDate) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$userId, $id, $issueDate, $expirationDate]);
                        $course['IssueDate'] = $issueDate;
                        $course['ExpirationDate'] = $expirationDate;
                    }

                    $course['EnrollmentStatus'] = 'Completed';
                    $quizMessages[] = 'Great work! You passed the quiz and earned a certification.';
                    
                    // Trigger popup when newly passed
                    $showCongratsModal = true;
                } else {
                    $quizResult = [
                        'score' => $correctCount,
                        'total' => count($quizQuestions),
                        'status' => 'failed',
                    ];
                    $quizMessages[] = 'The quiz did not meet the passing threshold. Review the course material and try again.';
                }
            }
        }
    } elseif ($_POST['action'] === 'complete_course') {
        // Fallback or secondary completion method if needed
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
                $course['IssueDate'] = $issueDate;
                $course['ExpirationDate'] = $expirationDate;
            }
            flash('success', 'Course completed! A certification has been issued.');
            $showCongratsModal = true;
        }
    }
}

require '../includes/header.php';
?>

<div class="eyebrow">Course Preview</div>
<h1><?= htmlspecialchars($course['Title']) ?></h1>
<p class="page-subtitle"><?= htmlspecialchars($course['Category'] ?? 'General training') ?> — Instructor: <?= htmlspecialchars($course['InstructorName'] ?? 'Unassigned') ?></p>

<div class="card">
    <div class="card-row">
        <div style="display: flex; align-items: center; gap: 1rem;">
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
            
            <?php if ($course['EnrollmentStatus'] === 'Completed'): ?>
                <button onclick="document.getElementById('certModal').style.display='flex'" class="btn btn-sm">View Certificate</button>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($course['CourseFile'])): ?>
            <a class="btn btn-sm" href="../<?= htmlspecialchars($course['CourseFile']) ?>" target="_blank">Open Course File</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($course['IssueDate']) && !empty($course['ExpirationDate'])): ?>
        <div style="margin-top: 1.5rem; display: flex; gap: 2.5rem; flex-wrap: wrap;">
            <div><strong>Certification issued:</strong> <?= htmlspecialchars($course['IssueDate']) ?></div>
            <div><strong>Expires:</strong> <?= htmlspecialchars($course['ExpirationDate']) ?></div>
        </div>
    <?php endif; ?>

    <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
        <h2 style="margin-bottom: 0.75rem;">Course overview</h2>
        <p style="margin-bottom: 1.5rem; line-height: 1.6;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. This course blends practical compliance guidance with hands-on security best practices, so you can treat every training module like the real thing.</p>

        <h3 style="margin-bottom: 0.75rem;">What you'll learn</h3>
        <ul style="padding-left: 1.5rem; margin-bottom: 1.5rem; line-height: 1.6;">
            <li style="margin-bottom: 0.25rem;">How to identify common security and compliance risks.</li>
            <li style="margin-bottom: 0.25rem;">Why policy review matters and how to apply it.</li>
            <li style="margin-bottom: 0.25rem;">How to respond to suspicious incidents safely.</li>
            <li style="margin-bottom: 0.25rem;">Best practices for secure passwords and remote work.</li>
            <li style="margin-bottom: 0.25rem;">How sign-off and certification keep your team aligned.</li>
        </ul>

        <h3 style="margin-bottom: 0.75rem;">Course sections</h3>
        <ol style="padding-left: 1.5rem; margin-bottom: 1.5rem; line-height: 1.6;">
            <li style="margin-bottom: 0.25rem;"><strong>Introduction:</strong> Why this training matters and how it applies to your team.</li>
            <li style="margin-bottom: 0.25rem;"><strong>Fundamentals:</strong> Clear examples and simple workflows you can use immediately.</li>
            <li style="margin-bottom: 0.25rem;"><strong>Practice:</strong> Scenarios, questions, and a short quiz to test your understanding.</li>
        </ol>

        <?php if (empty($course['EnrollmentStatus'])): ?>
            <form method="POST" style="margin-top:2rem; display:inline-flex; gap:1rem; align-items:center;">
                <input type="hidden" name="action" value="enroll_course">
                <button type="submit" class="btn btn-sm">Enroll in this course</button>
            </form>
            <p style="margin-top:0.75rem; color: var(--ink-soft);">Once enrolled, you can submit the quiz and earn a certification.</p>
        <?php elseif ($course['EnrollmentStatus'] === 'In Progress'): ?>
            <form method="POST" style="margin-top:2rem; display:inline-flex; gap:1rem; align-items:center;">
                <input type="hidden" name="action" value="unenroll_course">
                <button type="submit" class="btn btn-sm" onclick="return confirm('Are you sure you want to unenroll from this course?');">Unenroll from course</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-row" style="margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Quiz</h2>
        <span style="color: var(--ink-soft);">Answer 4 out of 5 correctly to pass and earn your certification.</span>
    </div>

    <?php if ($quizMessages): ?>
        <?php foreach ($quizMessages as $message): ?>
            <div class="alert <?= $quizResult && $quizResult['status'] === 'passed' ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($quizResult && $quizResult['status'] === 'passed'): ?>
        <div style="margin-bottom: 1rem;">
            <strong>Score:</strong> <?= htmlspecialchars($quizResult['score']) ?>/<?= htmlspecialchars($quizResult['total']) ?> — <span class="badge valid">Passed</span>
        </div>
        <p style="margin-bottom: 1rem;">Your certification has been issued and will appear on your dashboard.</p>
        <?php if (!empty($course['IssueDate'])): ?>
            <div style="margin-bottom: 0.25rem;"><strong>Certificate issued:</strong> <?= htmlspecialchars($course['IssueDate']) ?></div>
            <div><strong>Valid until:</strong> <?= htmlspecialchars($course['ExpirationDate']) ?></div>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($course['EnrollmentStatus'] === 'Completed'): ?>
            <div style="margin-bottom: 0.5rem;"><strong>Score:</strong> Completed course — certification already recorded.</div>
            <p style="margin-bottom: 1.5rem;">Review the course content again anytime or view your certification from the dashboard.</p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="submit_quiz">
            
            <div style="position: relative;">
                <?php if (empty($course['EnrollmentStatus'])): ?>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; z-index: 10;">
                        <div style="background-color: var(--surface, #ffffff); padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1px solid var(--border-color); font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                            Enroll first to take the quiz
                        </div>
                    </div>
                <?php endif; ?>

                <div style="<?= empty($course['EnrollmentStatus']) ? 'filter: blur(5px); opacity: 0.7; pointer-events: none; user-select: none;' : '' ?>">
                    <?php foreach ($quizQuestions as $questionId => $question): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <strong>Question <?= $questionId ?>:</strong> <?= htmlspecialchars($question['text']) ?>
                            <div style="margin-top: 0.5rem;">
                                <?php foreach ($question['choices'] as $choiceKey => $choiceLabel): ?>
                                    <label style="display:block; margin:0.5rem 0 0 0;">
                                        <input type="radio" name="quiz[<?= $questionId ?>]" value="<?= $choiceKey ?>" <?= empty($course['EnrollmentStatus']) ? 'disabled' : 'required' ?>>
                                        <?= htmlspecialchars($choiceLabel) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn" <?= empty($course['EnrollmentStatus']) ? 'disabled' : '' ?>>Submit Quiz</button>
                </div>
            </div>
        </form>

        <?php if (!empty($course['EnrollmentStatus']) && $quizResult && $quizResult['status'] === 'failed'): ?>
            <div style="margin-top: 1.5rem; color: var(--ink-soft);">
                <strong>Missed questions:</strong>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem;">
                    <?php foreach ($missedQuestions as $missed): ?>
                        <li>Question <?= htmlspecialchars($missed) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Fake Certificate Modal -->
<style>
.modal-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    justify-content: center;
    align-items: center;
}
.modal-card {
    background-color: #fff;
    padding: 3.5rem;
    border-radius: 12px;
    width: 90%;
    max-width: 750px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    position: relative;
    text-align: center;
}
.cert-border {
    border: 3px solid var(--border-color, #eaeaea);
    padding: 3rem 2rem;
    margin: 1.5rem 0;
    border-radius: 8px;
}
</style>

<div id="certModal" class="modal-overlay" style="<?= $showCongratsModal ? 'display:flex;' : '' ?>">
    <div class="modal-card">
        
        <?php if ($showCongratsModal): ?>
            <h2 style="color: #2e7d32; margin-bottom: 0.5rem;">Congratulations, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>!</h2>
            <p style="color: var(--ink-soft);">You have successfully completed this module.</p>
        <?php else: ?>
            <h2 style="margin-bottom: 0.5rem;">Certificate of Completion</h2>
        <?php endif; ?>

        <div class="cert-border">
            <p style="text-transform: uppercase; letter-spacing: 2px; font-size: 0.85rem; color: var(--ink-soft);">This certifies that</p>
            <h3 style="font-size: 2rem; margin: 1.25rem 0;"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></h3>
            <p style="color: var(--ink-soft);">has successfully completed the training requirements for</p>
            <h4 style="font-size: 1.35rem; margin: 1.25rem 0; font-weight: 600;"><?= htmlspecialchars($course['Title']) ?></h4>
            
            <div style="display: flex; justify-content: space-between; margin-top: 3rem; font-size: 0.9rem; text-align: left; color: var(--ink-soft);">
                <div>
                    <strong>Date Issued:</strong><br>
                    <?= htmlspecialchars($course['IssueDate']) ?>
                </div>
                <div style="text-align: right;">
                    <strong>Valid Until:</strong><br>
                    <?= htmlspecialchars($course['ExpirationDate']) ?>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
            <button style="padding: 0.6rem 1.25rem; border-radius: 6px; border: 1px solid var(--border-color, #d1d1d1); background: transparent; color: var(--ink, #333); cursor: pointer; font-size: 0.875rem; font-weight: 500;" onclick="document.getElementById('certModal').style.display='none'">Close</button>
            
            <!-- This data URI generates a blank PDF which will prompt the browser's "Save As" file explorer dialog -->
            <a href="data:application/pdf;base64,JVBERi0xLjQKJcOkw7zDtsOfCjIgMCBvYmoKPDwvTGVuZ3RoIDMgMCBSL0ZpbHRlci9GbGF0ZURlY29kZT4+CnN0cmVhbQp4nDPQM1Qo5ypUMFAwALJMLY31jBQK0tMzi1RKMvPTEvMUQkP1jPUMTAx0DE30zPRMlAwVYmOBgsbGBiA5EwVDYwMDMwVDKK8QKGdoCAA0kBRNCmVuZHN0cmVhbQplbmRvYmoKCjMgMCBvYmoKOTEKZW5kb2JqCgo0IDAgb2JqCjw8L1R5cGUvUGFnZS9NZWRpYUJveFswIDAgNTk1LjI4IDg0MS44OV0vUmVzb3VyY2VzPDwvRm9udDw8L0YxIDEgMCBSPj4+Pi9Db250ZW50cyAyIDAgUi9QYXJlbnQgNSAwIFI+PgplbmRvYmoKCjEgMCBvYmoKPDwvVHlwZS9Gb250L1N1YnR5cGUvVHlwZTEvQmFzZUZvbnQvSGVsdmV0aWNhPj4KZW5kb2JqCgo1IDAgb2JqCjw8L1R5cGUvUGFnZXMvQ291bnQgMS9LaWRzWzQgMCBSXT4+CmVuZG9iagoKNiAwIG9iago8PC9UeXBlL0NhdGFsb2cvUGFnZXMgNSAwIFI+PgplbmRvYmoKCjcgMCBvYmoKPDwvUHJvZHVjZXIoRlBERiAxLjg2KS9DcmVhdGlvbkRhdGUoRDoyMDI0MTAyMjEzMTEwOSswMCcwMCcpPj4KZW5kb2JqCgp4cmVmCjAgOAowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMDAyMzEgMDAwMDAgbiAKMDAwMDAwMDAxNSAwMDAwMCBuIAowMDAwMDAwMTgzIDAwMDAwIG4gCjAwMDAwMDAyMDQgMDAwMDAgbiAKMDAwMDAwMDMxOSAwMDAwMCBuIAowMDAwMDAwMzc2IDAwMDAwIG4gCjAwMDAwMDA0MjYgMDAwMDAgbiAKdHJhaWxlcgo8PC9TaXplIDgvUm9vdCA2IDAgUi9JbmZvIDcgMCBSPj4Kc3RhcnR4cmVmCjUyNQolJUVPRgo=" download="Certificate_<?= htmlspecialchars(str_replace(' ', '_', $course['Title'])) ?>.pdf" style="padding: 0.6rem 1.25rem; border-radius: 6px; border: 1px solid var(--ink, #333); background: var(--ink, #333); color: var(--surface, #fff); cursor: pointer; font-size: 0.875rem; font-weight: 500; text-decoration: none;">Export as PDF</a>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>