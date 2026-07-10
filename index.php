<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . dashboardRelativePath($_SESSION['role']));
} else {
    header('Location: auth/login.php');
}
exit();
