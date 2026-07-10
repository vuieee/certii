<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Admin']);

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('DELETE FROM COURSE WHERE CourseID = ?');
$stmt->execute([$id]);

flash('success', 'Course deleted.');
header('Location: courses.php');
exit();
