<?php
session_start();

// Function to redirect based on role
function redirectToRolePage($role) {
    switch ($role) {
        case 'admin':
            header("Location: admin-db.php");
            break;
        case 'teacher':
            header("Location: instructor-db.php");
            break;
        case 'student':
            header("Location: student-db.php");
            break;
        default:
            header("Location: multi-login.php");
            break;
    }
}

// Check if user is logged in and has a role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role']; // Assuming you store user role in the session
} else {
    // If not logged in, redirect to login
    header("Location: multi-login.php");
    exit();
}

?>
