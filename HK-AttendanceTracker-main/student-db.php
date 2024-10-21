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
                       sc.assigned_by,
                       sc.attendance_status,
                       sc.total_hours_rendered,
                       s.hk_equivalent_hours,
                       s.total_hours,
                       s.hk_status
                FROM schedule sc
                INNER JOIN users u ON sc.user_id = u.id
                INNER JOIN students s ON u.id = s.user_id
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
    
    header("Location: multi-login.php");
    exit();
}

$stmt->close();
$conn->close();

// PHP logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: multi-login.php");
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar Dashboard - UPang HK Attendance Tracker</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM5VRZ0gZlL/Z+0vD05D1xZ6cDq0jP0VqJ7hF" crossorigin="anonymous">
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
        main {
            flex-grow: 1;
            padding: 20px;
            color: white;
            overflow-y: auto;
            height: 100%;
            transition: margin-left 0.3s ease;
        }
        main.sidebar-hidden {
            margin-left: -220px; /* When sidebar is hidden, extend content to full width */
        }
        main:not(.sidebar-hidden) {
            margin-left: 10px; /* When sidebar is visible, keep content shifted */
        }
        .title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
        }
        .content-box {
            border-radius: 10px;
            /* padding: 0 10px; */
            position: relative; 
        }
        /* .content-box2 {
            border-radius: 10px;
            padding: 0 20px;
        } */
        /* .content-box h2 {
            margin-top: 50px;
        } */
        .notification-icon {
            position: absolute; 
            top: 0px; 
            right: 0px; 
            cursor: pointer; 
        }

        .notification-count {
            background: red; 
            color: white; 
            border-radius: 50%; 
            padding: 1px 3px; 
            position: absolute; 
            top: -5px; 
            right: -5px; 
            font-size: 14px; 
        }

        .notification-tray {
            display: none; 
            position: absolute; 
            top: 20px; 
            right: 20px; 
            background-color: rgba(255, 255, 255, 0.8); 
            backdrop-filter: blur(5px); 
            padding: 10px; 
            border-radius: 10px; 
            width: 300px; 
            max-height: 300px; 
            overflow-y: auto; 
            z-index: 10; 
            color: #333;
        }

        
        .notification-tray::-webkit-scrollbar {
            width: 8px; 
        }

        .notification-tray::-webkit-scrollbar-thumb {
            background: #6b8e23; 
            border-radius: 10px; 
        }

        .notification-tray::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }

        .notification-tray h2 {
            margin: 0; 
            margin-bottom: 2px;
            padding: 10px; 
            font-size: 18px; 
            color: #333; 
        }
        .notification-tray ul {
            list-style-type: none; 
            padding: 0; 
            margin-bottom: 2px; 
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
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #6b8e23;
        }
        th {
            background-color: #4a5d29;
            color: white;
        }
        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .no-results {
            text-align: center;
            font-weight: bold;
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
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br>Attendance Tracker</h2>
            <div class="nav-item active"><a href="student-db.php">Dashboard</a></div>
            <!-- <div class="nav-item"><a href="view-schedule.php">View Schedule</a></div>
            <div class="nav-item"><a href="manage-grades.php">Manage Grades</a></div>
            <div class="nav-item"><a href="check-attendance.php">Check Attendance</a></div> -->
            <div class="nav-item"><a href="student-profile.php">Profile</a></div>
            <div class="nav-item"><a class="logout-btn" href="?logout=true">Logout</a></div>
        </div>
        <main>
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>

            <div class="content-box">
            <h1 class="title">Welcome, <?php echo htmlspecialchars($username); ?></h1>

                <div class="notification-icon" onclick="toggleNotificationTray()">
                    <img src="notification.png" alt="Notifications" style="width: 24px; height: 24px;">
                    <span class="notification-count" style="display: <?php echo count($notifications) > 0 ? 'inline' : 'none'; ?>;">
                        <?php echo count($notifications); ?>
                    </span>
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
                        <h3>HK Status</h3>
                        <p>
                            <?php 
                            
                            echo htmlspecialchars(count($scheduleList) > 0 ? $scheduleList[0]['hk_status'] : 'N/A'); 
                            ?>
                        </p>
                    </div>
                    
                    <div class="info-box">
                        <h3>HK Status Equivalent Hours</h3>
                        <p>
                            <?php 
                           
                            echo htmlspecialchars(count($scheduleList) > 0 ? $scheduleList[0]['hk_equivalent_hours'] : 'N/A'); 
                            ?>
                        </p>
                    </div>

                    <div class="info-box">
                        <h3>Total Hours Rendered</h3>
                        <p>
                            <?php 
                            
                            $totalHoursRendered = 0;
                            foreach ($scheduleList as $schedule) {
                                $totalHoursRendered += (int)$schedule['total_hours_rendered']; // Ensure you're summing integers
                            }
                            echo htmlspecialchars($totalHoursRendered > 0 ? $totalHoursRendered : '0'); 
                            ?>
                        </p>
                    </div>

                    <div class="info-box">
                        <h3>Total Hours Remaining</h3>
                        <p>
                            <?php 
                            
                            echo htmlspecialchars(count($scheduleList) > 0 ? $scheduleList[0]['total_hours'] : 'N/A'); 
                            ?>
                        </p>
                    </div>
                </div>

                <h2>SCHEDULE</h2>
                <div class="content-box">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Subject</th>
                                <th>Classroom</th>
                                <th>Instructor</th>
                                <th>Status</th>
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
                                    <td><?php echo htmlspecialchars($schedule['attendance_status']); ?></td>
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
        // function toggleNotificationTray() {
        //     const tray = document.getElementById('notificationTray');
        //     if (tray.style.display === 'none' || tray.style.display === '') {
        //         tray.style.display = 'block'; 
        //     } else {
        //         tray.style.display = 'none'; 
        //     }
        // }

        function toggleNotificationTray() {
            const tray = document.getElementById('notificationTray');
            const notificationCount = document.querySelector('.notification-count');

            // Toggle the display of the notification tray
            if (tray.style.display === 'none' || tray.style.display === '') {
                tray.style.display = 'block'; 

                // Reset the notification count to 0 and hide the badge
                notificationCount.textContent = '0';
                notificationCount.style.display = 'none'; 
            } else {
                tray.style.display = 'none'; 
            }
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('main');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('sidebar-hidden');
        }
    </script>
</body>
</html>
