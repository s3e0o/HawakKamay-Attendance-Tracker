<?php
session_start();

require 'db-connection.php';

if ($_SESSION['role'] !== 'student') {
    header("Location: multi-login.php"); // Redirect if not a student
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

    // Fetch notifications
    $sqlNotifications = "SELECT * FROM schedule_logs WHERE schedule_id IN 
                        (SELECT schedule_id FROM schedule WHERE user_id = ?) 
                        ORDER BY timestamp DESC";
    $stmt = $conn->prepare($sqlNotifications);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $notificationResult = $stmt->get_result();

    // Initialize the notifications array
    $notifications = [];
    if ($notificationResult->num_rows > 0) {
        $notifications = $notificationResult->fetch_all(MYSQLI_ASSOC);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM5VRZ0gZlL/Z+0vD05D1xZ6cDq0jP0VqJ7hF" crossorigin="anonymous">
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
            position: relative; /* Relative positioning for absolute elements inside */
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
            color: white;
        }
        .content-box {
            border-radius: 10px;
            padding: 20px;
            position: relative; /* Make this container relative */
        }
        .content-box2 {
            border-radius: 10px;
            padding: 20px;
        }
        .content-box h2 {
            margin-top: 50px;
        }
        .notification-icon {
            position: absolute; /* Make it absolute for positioning */
            top: 0px; /* Position it at the top */
            right: 0px; /* Position it to the right */
            cursor: pointer; /* Change cursor to pointer */
        }

        .notification-count {
            background: red; /* Background color for count */
            color: white; /* Text color */
            border-radius: 50%; /* Make it circular */
            padding: 1px 3px; /* Padding around the count */
            position: absolute; /* Position it absolutely */
            top: -5px; /* Position it above the icon */
            right: -5px; /* Position it to the right of the icon */
            font-size: 14px; /* Font size for count */
        }

        .notification-tray {
            display: none; /* Hide tray by default */
            position: absolute; /* Use absolute positioning */
            top: 20px; /* Position it below the notification icon */
            right: 20px; /* Position it at the right */
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
            backdrop-filter: blur(5px); /* Add a blur effect */
            padding: 10px; /* Add padding */
            border-radius: 10px; /* Rounded corners */
            width: 300px; /* Set a width */
            max-height: 300px; /* Set a max height */
            overflow-y: auto; /* Allow vertical scrolling */
            z-index: 10; /* Ensure it appears above other content */
        }

        /* Custom scrollbar styles */
        .notification-tray::-webkit-scrollbar {
            width: 8px; /* Width of the scrollbar */
        }

        .notification-tray::-webkit-scrollbar-thumb {
            background: #6b8e23; /* Color of the scrollbar */
            border-radius: 10px; /* Rounded corners */
        }

        .notification-tray::-webkit-scrollbar-track {
            background: #f1f1f1; /* Background of the scrollbar track */
        }

        .notification-tray h2 {
            margin: 0; /* Remove default margin */
            padding: 10px; /* Add padding for spacing around the title */
            font-size: 18px; /* Adjust font size as needed */
            color: #333; /* Change text color if desired */
        }
        .notification-tray ul {
            list-style-type: none; /* Remove bullet points */
            padding: 0; /* Remove padding */
            margin: 0; /* Remove margin */
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #BFA93B;
            padding: 8px;
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
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #A98D00;
            color: white;
        }
        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .no-results {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <div class="nav-item"><a href="student-dashboard.php">Dashboard</a></div>
            <div class="nav-item"><a href="view-schedule.php">View Schedule</a></div>
            <div class="nav-item"><a href="manage-grades.php">Manage Grades</a></div>
            <div class="nav-item"><a href="check-attendance.php">Check Attendance</a></div>
            <div class="nav-item"><a href="update-profile.php">Update Profile</a></div>
            <a class="logout-btn" href="?logout=true">Logout</a>
        </div>
        <main>
            <div class="content-box">
                <h2>SCHEDULE</h2>

                <div class="notification-icon" onclick="toggleNotificationTray()">
                    <img src="notification.png" alt="Notifications" style="width: 24px; height: 24px;">
                    <span class="notification-count"><?php echo count($notifications); ?></span>
                </div>

                <div class="notification-tray" id="notificationTray">
                    <h2>Notifications</h2>
                    <?php if (count($notifications) > 0): ?>
                        <ul>
                            <?php foreach ($notifications as $notification): ?>
                                <li>
                                    <?php echo htmlspecialchars($notification['changed_field']); ?> changed from 
                                    <strong><?php echo htmlspecialchars($notification['old_value']); ?></strong> to 
                                    <strong><?php echo htmlspecialchars($notification['new_value']); ?></strong> 
                                    on <?php echo htmlspecialchars($notification['timestamp']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No notifications.</p>
                    <?php endif; ?>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <h3>F2F Day:</h3>
                        <p>Monday</p>
                    </div>
                    <div class="info-box">
                        <h3>Required Hours:</h3>
                        <p>90</p>
                    </div>
                    <div class="info-box">
                        <h3>Total Hours Rendered:</h3>
                        <p></p>
                    </div>
                    <div class="info-box">
                        <h3>Total Hours Remaining:</h3>
                        <p>40</p>
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
            </div>
        </main>
    </div>
    <script>
        function toggleNotificationTray() {
            const tray = document.getElementById('notificationTray');
            if (tray.style.display === 'none' || tray.style.display === '') {
                tray.style.display = 'block'; // Show the notification tray
            } else {
                tray.style.display = 'none'; // Hide the notification tray
            }
        }
    </script>
</body>
</html>
