<?php
session_start(); // Start the session at the very beginning
// include 'session-check.php';

require 'db-connection.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: multi-login.php"); // Redirect if not an admin
    exit();
}

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
    <title>Admin Dashboard - UPang HK Attendance Tracker</title>
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
            background-attachment: fixed;
            overflow: hidden;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 200px;
            background-color: #A98D00;
            color: white;
            padding: 20px;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .sidebar.hidden {
            transform: translateX(-100%);
            opacity: 0;
        }
        .logo {
            width: 150px;  
            height: 150px; 
            background-image: url('hk_logo.png'); 
            background-size: cover;  
            background-position: center;
            border-radius: 50%; 
            margin: 0 auto 10px;
        }
        .nav-item {
            padding: 10px;
            margin: 5px 0;
        }
        .nav-item:hover {
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
            background-color: #f44336; 
            text-decoration: none;
            color: white;
            display: block;
            padding: 10px;
            margin: 5px 0;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            color: white;
            overflow-y: auto;
            height: 100%;
            transition: margin-left 0.3s ease;
        }
        .main-content.sidebar-hidden {
            margin-left: -220px; /* When sidebar is hidden, extend content to full width */
        }
        .main-content:not(.sidebar-hidden) {
            margin-left: 10px; /* When sidebar is visible, keep content shifted */
        }
        .title {
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
            background-color: #A98D00;
            padding: 15px;
            border-radius: 5px;
        }
        .info-box:hover {
            background-color: #BFA93B;
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
        .toggle-btn {
            background-color: #6b8e23;
            position: absolute;
            top: 0;
            left: 0px;
            padding: 10px;
            color: white;
            cursor: pointer;
            z-index: 3;
            transition: left 0.3s ease;
        }
        .sidebar-hidden + .toggle-btn {
            left: 200px; /* Adjust toggle button when sidebar is hidden */
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
            }
            .main-content {
                padding: 10px;
            }
            .toggle-btn {
                left: 10px;
            }
        }
        @media (max-width: 480px) {
            h1 {
                font-size: 20px;
            }
            th, td {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="sidebar" id="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item active"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Faculty</a></div>
            <div class="nav-item"><a href="student-list.php">Scholar</a></div>
            <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div> 
        <div class="main-content" id="main-content">
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>

            <!-- <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button> -->
            <!-- <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1> Display logged-in user's name -->
            <h1 class="title">ADMIN DASHBOARD</h1>
            <div class="info-grid">
                <div class="info-box">
                    <h3>Total Scholars</h3>
                    <p><?php echo $total_students; ?></p>
                </div>
                <div class="info-box">
                    <h3>Total Faculties</h3>
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
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('sidebar-hidden');
        }
    </script>
</body>
</html>
