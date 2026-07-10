-- Corporate Training & Certification Tracker
-- Run this file once to create the database, tables, and seed data.

CREATE DATABASE IF NOT EXISTS corporate_training_db;
USE corporate_training_db;

CREATE TABLE INSTRUCTOR (
    InstructorID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Expertise VARCHAR(100) NOT NULL,
    Type ENUM('Internal', 'External') NOT NULL
);

-- Also serves as the users table for authentication.
CREATE TABLE EMPLOYEE (
    EmployeeID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Department VARCHAR(100),
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('Employee', 'Manager', 'Admin') DEFAULT 'Employee'
);

CREATE TABLE COURSE (
    CourseID INT AUTO_INCREMENT PRIMARY KEY,
    InstructorID INT,
    Title VARCHAR(150) NOT NULL,
    Category VARCHAR(100),
    FOREIGN KEY (InstructorID) REFERENCES INSTRUCTOR(InstructorID) ON DELETE SET NULL
);

CREATE TABLE COURSE_ENROLLMENT (
    EnrollmentID INT AUTO_INCREMENT PRIMARY KEY,
    EmployeeID INT,
    CourseID INT,
    EnrollmentDate DATE NOT NULL,
    Status ENUM('In Progress', 'Completed') DEFAULT 'In Progress',
    FOREIGN KEY (EmployeeID) REFERENCES EMPLOYEE(EmployeeID) ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES COURSE(CourseID) ON DELETE CASCADE
);

CREATE TABLE CERTIFICATION (
    CertID INT AUTO_INCREMENT PRIMARY KEY,
    EmployeeID INT,
    CourseID INT,
    IssueDate DATE NOT NULL,
    ExpirationDate DATE NOT NULL,
    FOREIGN KEY (EmployeeID) REFERENCES EMPLOYEE(EmployeeID) ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES COURSE(CourseID) ON DELETE CASCADE
);

-- Seed data. All seeded passwords are 'password123' (BCRYPT hashed).
INSERT INTO EMPLOYEE (Name, Department, Email, Password, Role) VALUES
('Admin User', 'IT', 'admin@example.com', '$2y$10$4vR8xmqGxldI.d6g.3qgi.cWH2jJugxjQ.4fj2fhnJ788xwoDHdlW', 'Admin'),
('Manager User', 'HR', 'manager@example.com', '$2y$10$4vR8xmqGxldI.d6g.3qgi.cWH2jJugxjQ.4fj2fhnJ788xwoDHdlW', 'Manager'),
('John Doe', 'Engineering', 'employee@example.com', '$2y$10$4vR8xmqGxldI.d6g.3qgi.cWH2jJugxjQ.4fj2fhnJ788xwoDHdlW', 'Employee');

INSERT INTO INSTRUCTOR (Name, Expertise, Type) VALUES
('Alice Smith', 'Cybersecurity', 'Internal'),
('Bob Johnson', 'Compliance', 'External');

INSERT INTO COURSE (InstructorID, Title, Category) VALUES
(1, 'Data Privacy Basics', 'Security'),
(2, 'Workplace Safety 101', 'Compliance');

INSERT INTO COURSE_ENROLLMENT (EmployeeID, CourseID, EnrollmentDate, Status) VALUES
(3, 1, '2026-06-01', 'Completed'),
(3, 2, '2026-06-15', 'In Progress');

-- Expiration date is set close to today so the "Expiring Soon" status is visible on first run.
INSERT INTO CERTIFICATION (EmployeeID, CourseID, IssueDate, ExpirationDate) VALUES
(3, 1, '2026-06-10', '2026-07-15');
