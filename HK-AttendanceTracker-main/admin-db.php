<?php
session_start(); // Start the session at the very beginning

require 'db-connection.php';

// PHP logout logic
if (isset($_GET['logout'])) {
    // Destroy the session
    session_destroy();
    // Redirect to the login page
    header("Location: multi-login.php");
    exit(); // Exit after header redirection
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: multi-login.php");
    exit();
}

// Fetch totals from the database
$total_students = 0;
$total_teachers = 0;
$total_hk25 = 0;
$total_hk50 = 0;
$total_hk75 = 0;
$total_hk100 = 0;

$conn = new mysqli("localhost", "root", "", "hk-management"); // Update with your actual database credentials

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get total number of students
$result_students = $conn->query("SELECT COUNT(*) as total FROM students");
if ($result_students) {
    $total_students = $result_students->fetch_assoc()['total'];
}

// Query to get total number of teachers
$result_teachers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'teacher'");
if ($result_teachers) {
    $total_teachers = $result_teachers->fetch_assoc()['total'];
}

// Queries to get the number of scholars by HK status
$result_hk25 = $conn->query("SELECT COUNT(*) as total FROM students WHERE hk_status = 'hk25'");
if ($result_hk25) {
    $total_hk25 = $result_hk25->fetch_assoc()['total'];
}

$result_hk50 = $conn->query("SELECT COUNT(*) as total FROM students WHERE hk_status = 'hk50'");
if ($result_hk50) {
    $total_hk50 = $result_hk50->fetch_assoc()['total'];
}

$result_hk75 = $conn->query("SELECT COUNT(*) as total FROM students WHERE hk_status = 'hk75'");
if ($result_hk75) {
    $total_hk75 = $result_hk75->fetch_assoc()['total'];
}

$result_hk100 = $conn->query("SELECT COUNT(*) as total FROM students WHERE hk_status = 'hk100'");
if ($result_hk100) {
    $total_hk100 = $result_hk100->fetch_assoc()['total'];
}

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
            background-image: url('hkat-upang.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: margin-left .5s; 
        }
        .container {
            display: flex;
            height: 100%;
            transition: margin-left .5s; /* Animation for container */
        }
        .sidebar {
            width: 200px;
            background-color: #A98D00;
            color: white;
            padding: 20px;
            transition: transform 0.3s ease; /* Animation for sidebar */
            position: relative;
            z-index: 2; /* Ensure sidebar is above main content */
        }
        .sidebar.hidden {
            transform: translateX(-100%); /* Move sidebar out of view */
            width: 0; /* Remove width when hidden */
            padding: 0; /* Remove padding when hidden */
            opacity: 0; /* Make sidebar invisible */
        }
        .logo {
            width: 150px;  /* Adjust size */
            height: 150px; /* Ensure it's square */
            background-image: url('hk_logo.png'); /* Background image path */
            background-size: cover;  /* Makes sure the image covers the entire div */
            background-position: center;
            border-radius: 50%; /* Make it a circle */
            margin: 0 auto 10px;
        }
        .nav-item {
            padding: 10px;
            margin: 5px 0;
        }
        .nav-item:hover {
            padding: 10px;
            margin: 5px 0;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        a {
            text-decoration: none;
            color: white;
        }
        .sidebar h2 {
            text-align: center;
            color: #4a5d29;
            font-size: medium;
        }
        .sidebar .logout-btn {
            text-decoration: none;
            color: white;
            display: block;
            padding: 10px;
            margin: 5px 0;
            background-color: #ff4c4c;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
        }
        .logout-btn {
        margin-top: auto; /* Push this to the bottom */
        padding: 10px; /* Optional: Adjust padding */
        text-align: center; /* Center text */
        color: white; /* Button text color */
        background-color: #f44336; /* Default button color */
        border: none; /* Remove default border */
        cursor: pointer; /* Pointer cursor on hover */
        transition: background-color 0.3s ease; /* Smooth transition */
        }


        .main-content {
            flex-grow: 1;
            padding: 20px;
            color: white;
            margin-left: 0px; 
        }
        .main-content.hidden {
            margin-left: 0; /* Adjust margin when sidebar is hidden */
        }
        .toggle-btn {
            background-color: #A98D00; /* Color of the toggle button */
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .dashboard-title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #BFA93B;
            padding: 15px;
            border-radius: 5px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .info-box p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #b8860b;
        }
        th {
            background-color: #4a5d29;
        }
        .section-title {
            font-size: 18px;
            margin: 20px 0 10px;
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="sidebar" id="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Instructor</a></div>
            <div class="nav-item"><a href="student-list.php">Student</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div> 
        <div class="main-content" id="main-content">
            <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>
            <h1 class="dashboard-title">ADMIN DASHBOARD</h1>
            <div class="info-grid">
                <div class="info-box">
                    <h3>Total Scholars</h3>
                    <p><?php echo $total_students; ?></p>
                </div>
                <div class="info-box">
                    <h3>Total Teachers/Coordinators</h3>
                    <p><?php echo $total_teachers; ?></p>
                </div>
                <div class="info-box">
                    <h3>Total HK25 Scholars</h3>
                    <p><?php echo $total_hk25; ?></p>
                </div>
                <div class="info-box">
                    <h3>Total HK50 Scholars</h3>
                    <p><?php echo $total_hk50; ?></p>
                </div>
                <div class="info-box">
                    <h3>Total HK75 Scholars</h3>
                    <p><?php echo $total_hk75; ?></p>
                </div>
                <div class="info-box">
                    <h3>Total HK100 Scholars</h3>
                    <p><?php echo $total_hk100; ?></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const container = document.getElementById('container');

        // Toggle the sidebar class to show/hide it
        sidebar.classList.toggle('hidden');

        // If the sidebar is hidden, reduce the container margin
        if (sidebar.classList.contains('hidden')) {
            container.style.marginLeft = '0'; // Sidebar hidden, no margin
        } else {
            container.style.marginLeft = '200px'; // Sidebar visible, add margin
        }
    }
    </script>
</body>
</html>
