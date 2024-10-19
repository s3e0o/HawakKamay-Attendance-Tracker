<?php
session_start();

require 'db-connection.php';

if ($_SESSION['role'] !== 'student') {
    header("Location: multi-login.php"); // Redirect if not an admin
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hk-management');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the logged-in student's ID
$student_id = $_SESSION['id'] ?? null; // Use 'id' as stored in the session from login
$username = $_SESSION['username'] ?? 'Guest'; // Get username from session

if ($student_id) { // Proceed only if a user is logged in
    // Prepare SQL query to fetch schedule for the logged-in student
    $sqlSchedule = "SELECT sc.date, 
                       sc.start_time, 
                       sc.end_time, 
                       sc.subject, 
                       sc.classroom, 
                       sc.assigned_by
                FROM schedule sc
                INNER JOIN users u ON sc.user_id = u.id
                WHERE u.id = ?";

    $stmt = $conn->prepare($sqlSchedule);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $scheduleResult = $stmt->get_result();

    // Initialize the scheduleList array
    $scheduleList = [];
    if ($scheduleResult->num_rows > 0) {
        $scheduleList = $scheduleResult->fetch_all(MYSQLI_ASSOC);
    }
} else {
    // Redirect to login if no user is logged in
    header("Location: multi-login.php");
    exit();
}

// Close the statement and connection
$stmt->close();
$conn->close();

// PHP logout logic
if (isset($_GET['logout'])) {
    // Destroy the session
    session_destroy();
    // Redirect to the login page
    header("Location: multi-login.php");
    exit(); // Exit after header redirection
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPang HK Attendance Tracker - Student Dashboard</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        /* Your CSS styles */
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
            transition: margin-left .5s;
        }
        .sidebar {
            width: 200px;
            background-color: #A98D00;
            color: white;
            padding: 20px;
            position: relative;
            z-index: 2;
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
            font-size: medium;
            color: #4a5d29;
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
        main {
            flex-grow: 1;
            padding: 20px;
            color: white;
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
            color: white;
        }
        .content-box {
            /*background-color: #4a5d29;*/
            border-radius: 10px;
            padding: 20px;
        }
        .content-box2 {
            /*background-color: #4a5d29;*/
            border-radius: 10px;
            padding: 20px;
        }
        .content-box h2 {
            /*background-color: #4a5d29;*/
            margin-top: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #BFA93B;
            padding: 8  px;
            border-radius: 5px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 25px;
        }
        .info-box p {
            margin: 0;
            font-size: 23px;
            font-weight: bold;
            
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #6b8e23;
        }
        th {
            background-color: #3e4d22;
        }
        .no-results {
            margin-top: 20px;
            color: #ffcc00;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item active"><a href="student-db.php">Schedule</a></div>
            <div class="nav-item"><a href="student-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>
        </div>

        <!-- Main content -->
        <main>

            <div class="content-box">
                <h2>SCHEDULE</h2>
                <div class="info-grid">
                <div class="info-box">
                    <h3>F2F Day:</h3>
                    <td><p>Monday</p></td>
                   <!-- <p><?php echo $total_hk25; ?></p> -->
                </div>
                <div class="info-box">
                    <h3>Required Hours:</h3>
                    <td><p>90</p></td>
                    <!--<p><?php echo $total_students; ?></p> -->
                </div>
                <div class="info-box">
                    <h3>Total Hours Rendered:</h3>
                    <td><p>50</p></td>
                    <!--<p><?php echo $total_teachers; ?></p> -->
                </div>
                <div class="info-box">
                    <h3>Total Hours Remaining:</h3>
                    <td><p>40</p></td>
                   <!-- <p><?php echo $total_hk25; ?></p> -->
                </div>
            </div>
            <div class="content-box2">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Subject</th>
                            <th>Classroom</th>
                            <th>Instructor</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($scheduleList) > 0): ?>
                        <?php foreach ($scheduleList as $schedule): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($schedule['date']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['end_time']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['subject']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['classroom']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['assigned_by']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-results">No schedule available.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
