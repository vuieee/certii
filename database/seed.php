<?php
/**
 * Database seeding script for mock data
 * Run this once to populate the database with sample data for testing
 */

require_once __DIR__ . '/../config/db.php';

try {
    // Clear existing data (for development only)
    $tables = ['CERTIFICATION', 'COURSE_ENROLLMENT', 'QUIZ_ATTEMPT', 'EMPLOYEE', 'COURSE', 'INSTRUCTOR'];
    foreach ($tables as $table) {
        $pdo->exec("DELETE FROM $table");
    }

    // Create instructors
    $instructors = [
        ['Name' => 'Sarah Johnson', 'Email' => 'sarah@company.com', 'Department' => 'Training'],
        ['Name' => 'Michael Chen', 'Email' => 'michael@company.com', 'Department' => 'Security'],
        ['Name' => 'Emily Rodriguez', 'Email' => 'emily@company.com', 'Department' => 'Compliance'],
    ];

    $instructorStmt = $pdo->prepare('INSERT INTO INSTRUCTOR (Name, Email, Department) VALUES (?, ?, ?)');
    foreach ($instructors as $instructor) {
        $instructorStmt->execute([$instructor['Name'], $instructor['Email'], $instructor['Department']]);
    }

    // Create courses
    $courses = [
        [
            'Title' => 'Security Fundamentals',
            'Category' => 'Security',
            'Description' => 'Learn the basics of cybersecurity and threat prevention',
            'CourseFile' => 'assets/courses/security.pdf',
            'InstructorID' => 1,
        ],
        [
            'Title' => 'Data Privacy Essentials',
            'Category' => 'Compliance',
            'Description' => 'Understand data protection regulations and best practices',
            'CourseFile' => 'assets/courses/privacy.pdf',
            'InstructorID' => 3,
        ],
        [
            'Title' => 'Password Security Best Practices',
            'Category' => 'Security',
            'Description' => 'Master secure password management techniques',
            'CourseFile' => 'assets/courses/passwords.pdf',
            'InstructorID' => 2,
        ],
        [
            'Title' => 'Incident Response Planning',
            'Category' => 'Security',
            'Description' => 'Develop effective incident response procedures',
            'CourseFile' => 'assets/courses/incident.pdf',
            'InstructorID' => 2,
        ],
        [
            'Title' => 'Workplace Compliance Overview',
            'Category' => 'Compliance',
            'Description' => 'General workplace compliance and policy training',
            'CourseFile' => 'assets/courses/compliance.pdf',
            'InstructorID' => 3,
        ],
        [
            'Title' => 'Email Security Awareness',
            'Category' => 'Security',
            'Description' => 'Identify and prevent email-based threats',
            'CourseFile' => 'assets/courses/email.pdf',
            'InstructorID' => 1,
        ],
        [
            'Title' => 'Remote Work Security',
            'Category' => 'Security',
            'Description' => 'Secure practices for remote and hybrid work',
            'CourseFile' => 'assets/courses/remote.pdf',
            'InstructorID' => 2,
        ],
        [
            'Title' => 'Access Control Fundamentals',
            'Category' => 'Security',
            'Description' => 'Understanding access management and permissions',
            'CourseFile' => 'assets/courses/access.pdf',
            'InstructorID' => 1,
        ],
        [
            'Title' => 'GDPR Compliance Training',
            'Category' => 'Compliance',
            'Description' => 'European data protection regulation requirements',
            'CourseFile' => 'assets/courses/gdpr.pdf',
            'InstructorID' => 3,
        ],
        [
            'Title' => 'Business Continuity Planning',
            'Category' => 'Compliance',
            'Description' => 'Prepare for business disruption scenarios',
            'CourseFile' => 'assets/courses/continuity.pdf',
            'InstructorID' => 3,
        ],
    ];

    $courseStmt = $pdo->prepare('INSERT INTO COURSE (Title, Category, Description, CourseFile, InstructorID) VALUES (?, ?, ?, ?, ?)');
    foreach ($courses as $course) {
        $courseStmt->execute([
            $course['Title'],
            $course['Category'],
            $course['Description'],
            $course['CourseFile'],
            $course['InstructorID'],
        ]);
    }

    // Create employees with managers
    $employees = [
        ['Name' => 'Alice Smith', 'Email' => 'alice@company.com', 'Department' => 'Engineering', 'Role' => 'Employee'],
        ['Name' => 'Bob Wilson', 'Email' => 'bob@company.com', 'Department' => 'Engineering', 'Role' => 'Employee'],
        ['Name' => 'Carol Davis', 'Email' => 'carol@company.com', 'Department' => 'Marketing', 'Role' => 'Employee'],
        ['Name' => 'David Lee', 'Email' => 'david@company.com', 'Department' => 'Marketing', 'Role' => 'Employee'],
        ['Name' => 'Eva Martinez', 'Email' => 'eva@company.com', 'Department' => 'Finance', 'Role' => 'Employee'],
        ['Name' => 'Frank Taylor', 'Email' => 'frank@company.com', 'Department' => 'Engineering', 'Role' => 'Employee'],
        ['Name' => 'Grace Wong', 'Email' => 'grace@company.com', 'Department' => 'Operations', 'Role' => 'Employee'],
        ['Name' => 'Henry Brown', 'Email' => 'henry@company.com', 'Department' => 'Finance', 'Role' => 'Employee'],
        ['Name' => 'Engineering Manager', 'Email' => 'eng.manager@company.com', 'Department' => 'Engineering', 'Role' => 'Manager'],
        ['Name' => 'Marketing Manager', 'Email' => 'mkt.manager@company.com', 'Department' => 'Marketing', 'Role' => 'Manager'],
        ['Name' => 'Finance Manager', 'Email' => 'fin.manager@company.com', 'Department' => 'Finance', 'Role' => 'Manager'],
        ['Name' => 'Admin User', 'Email' => 'admin@company.com', 'Department' => 'Administration', 'Role' => 'Admin'],
    ];

    $employeeStmt = $pdo->prepare('INSERT INTO EMPLOYEE (Name, Email, Department, Role, PasswordHash) VALUES (?, ?, ?, ?, ?)');
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);

    foreach ($employees as $employee) {
        $employeeStmt->execute([
            $employee['Name'],
            $employee['Email'],
            $employee['Department'],
            $employee['Role'],
            $passwordHash,
        ]);
    }

    // Create enrollments and certifications
    $enrollmentStmt = $pdo->prepare('INSERT INTO COURSE_ENROLLMENT (EmployeeID, CourseID, EnrollmentDate, Status) VALUES (?, ?, ?, ?)');
    $certificationStmt = $pdo->prepare('INSERT INTO CERTIFICATION (EmployeeID, CourseID, IssueDate, ExpirationDate) VALUES (?, ?, ?, ?)');
    $quizStmt = $pdo->prepare('INSERT INTO QUIZ_ATTEMPT (EmployeeID, CourseID, AttemptDate, Score, TotalQuestions, Passed) VALUES (?, ?, ?, ?, ?, ?)');

    // Alice: 7 courses completed, 1 in progress
    $alice_enrollments = [1, 2, 3, 4, 5, 6, 7, 8];
    for ($i = 0; $i < 7; $i++) {
        $enrollmentStmt->execute([1, $alice_enrollments[$i], date('Y-m-d', strtotime("-" . (30 - $i * 4) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (30 - $i * 4) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([1, $alice_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([1, $alice_enrollments[$i], $issueDate, 5, 5, 1]);
    }
    $enrollmentStmt->execute([1, 8, date('Y-m-d', strtotime("-2 days")), 'In Progress']);

    // Bob: 5 courses completed, 2 in progress
    $bob_enrollments = [1, 2, 3, 4, 5, 6, 7];
    for ($i = 0; $i < 5; $i++) {
        $enrollmentStmt->execute([2, $bob_enrollments[$i], date('Y-m-d', strtotime("-" . (20 - $i * 3) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (20 - $i * 3) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([2, $bob_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([2, $bob_enrollments[$i], $issueDate, 4, 5, 1]);
    }
    $enrollmentStmt->execute([2, 6, date('Y-m-d', strtotime("-5 days")), 'In Progress']);
    $enrollmentStmt->execute([2, 7, date('Y-m-d', strtotime("-1 day")), 'In Progress']);

    // Carol: 3 courses completed
    $carol_enrollments = [1, 3, 5];
    for ($i = 0; $i < 3; $i++) {
        $enrollmentStmt->execute([3, $carol_enrollments[$i], date('Y-m-d', strtotime("-" . (15 - $i * 5) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (15 - $i * 5) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([3, $carol_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([3, $carol_enrollments[$i], $issueDate, 5, 5, 1]);
    }

    // David: 4 courses completed, 1 in progress
    $david_enrollments = [2, 3, 5, 6, 7];
    for ($i = 0; $i < 4; $i++) {
        $enrollmentStmt->execute([4, $david_enrollments[$i], date('Y-m-d', strtotime("-" . (25 - $i * 6) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (25 - $i * 6) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([4, $david_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([4, $david_enrollments[$i], $issueDate, 4, 5, 1]);
    }
    $enrollmentStmt->execute([4, 7, date('Y-m-d', strtotime("-3 days")), 'In Progress']);

    // Eva: 6 courses completed, 2 in progress
    $eva_enrollments = [1, 2, 3, 4, 5, 9, 10, 8];
    for ($i = 0; $i < 6; $i++) {
        $enrollmentStmt->execute([5, $eva_enrollments[$i], date('Y-m-d', strtotime("-" . (35 - $i * 5) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (35 - $i * 5) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([5, $eva_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([5, $eva_enrollments[$i], $issueDate, 5, 5, 1]);
    }
    $enrollmentStmt->execute([5, 7, date('Y-m-d', strtotime("-4 days")), 'In Progress']);
    $enrollmentStmt->execute([5, 8, date('Y-m-d', strtotime("-1 day")), 'In Progress']);

    // Frank: 2 courses completed
    $frank_enrollments = [1, 2];
    for ($i = 0; $i < 2; $i++) {
        $enrollmentStmt->execute([6, $frank_enrollments[$i], date('Y-m-d', strtotime("-" . (10 - $i * 5) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (10 - $i * 5) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([6, $frank_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([6, $frank_enrollments[$i], $issueDate, 4, 5, 1]);
    }

    // Grace: 8 courses completed
    $grace_enrollments = [1, 2, 3, 4, 5, 6, 7, 8];
    for ($i = 0; $i < 8; $i++) {
        $enrollmentStmt->execute([7, $grace_enrollments[$i], date('Y-m-d', strtotime("-" . (40 - $i * 5) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (40 - $i * 5) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([7, $grace_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([7, $grace_enrollments[$i], $issueDate, 5, 5, 1]);
    }

    // Henry: 3 courses completed, 1 in progress
    $henry_enrollments = [2, 5, 9, 10];
    for ($i = 0; $i < 3; $i++) {
        $enrollmentStmt->execute([8, $henry_enrollments[$i], date('Y-m-d', strtotime("-" . (18 - $i * 5) . " days")), 'Completed']);
        $issueDate = date('Y-m-d', strtotime("-" . (18 - $i * 5) . " days"));
        $expirationDate = date('Y-m-d', strtotime($issueDate . " +1 year"));
        $certificationStmt->execute([8, $henry_enrollments[$i], $issueDate, $expirationDate]);
        $quizStmt->execute([8, $henry_enrollments[$i], $issueDate, 4, 5, 1]);
    }
    $enrollmentStmt->execute([8, 10, date('Y-m-d', strtotime("-2 days")), 'In Progress']);

    echo "✓ Database seeded successfully!\n";
    echo "  - Created 3 instructors\n";
    echo "  - Created 10 courses\n";
    echo "  - Created 12 employees (8 employees + 4 managers/admin)\n";
    echo "  - Created 40+ enrollments with certifications and quiz attempts\n";
    echo "\n";
    echo "Test Accounts:\n";
    echo "  Employee: alice@company.com (password: password123)\n";
    echo "  Manager: eng.manager@company.com (password: password123)\n";
    echo "  Admin: admin@company.com (password: password123)\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
