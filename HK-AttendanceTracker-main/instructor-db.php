<?php
// Start session at the very beginning of the file
session_start();

require 'db-connection.php';

if ($_SESSION['role'] !== 'teacher') {
    header("Location: multi-login.php"); // Redirect if not a teacher
    exit();
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: multi-login.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "hk-management";

date_default_timezone_set('Asia/Manila');

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchDate = date('Y-m-d');
if (isset($_GET['searchDate']) && !empty($_GET['searchDate'])) {
    $searchDate = $_GET['searchDate'];
}

$scheduleList = [];

// Fetch schedules
$stmt = $conn->prepare("
    SELECT sc.user_id as schedule_id, s.name, sc.start_time, sc.end_time, sc.attendance_status, 
           s.hk_status, s.total_hours, s.user_id as student_id
    FROM schedule sc
    JOIN students s ON sc.user_id = s.user_id
    WHERE sc.date = ?
");
$stmt->bind_param("s", $searchDate);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $scheduleList[] = $row;
    }
}
$stmt->close();

// Handle attendance status update
if (isset($_POST['schedule_id']) && isset($_POST['status'])) {
    $schedule_id = $_POST['schedule_id'];
    $status = $_POST['status'];

    // Fetch student details and schedule duration
    $stmt = $conn->prepare("
        SELECT s.user_id as student_id, s.total_hours, sc.start_time, sc.end_time, sc.attendance_status
        FROM schedule sc
        JOIN students s ON sc.user_id = s.user_id
        WHERE sc.user_id = ?
    ");
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $stmt->bind_result($student_id, $total_hours, $start_time, $end_time, $attendance_status);
    $stmt->fetch();
    $stmt->close();

    // Calculate duration between start and end times
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $duration = $end->diff($start);
    $total_duration_minutes = ($duration->h * 60) + $duration->i;

    // Adjust total hours based on status change
    if ($status === 'Approved') {
        $new_total_minutes = max(0, ($total_hours * 60) - $total_duration_minutes);
    } elseif ($status === 'Absent' && $attendance_status === 'Approved') {
        $new_total_minutes = ($total_hours * 60) + $total_duration_minutes;
    } else {
        $new_total_minutes = $total_hours * 60;
    }

    $new_total_hours = floor($new_total_minutes / 60);
    $remaining_minutes = $new_total_minutes % 60;

    // Update student's total hours
    $update_stmt = $conn->prepare("UPDATE students SET total_hours = ? WHERE user_id = ?");
    $update_stmt->bind_param("di", $new_total_hours, $student_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Update attendance status in the schedule table
    $stmt = $conn->prepare("UPDATE schedule SET attendance_status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $status, $schedule_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Attendance status updated to '$status'."
    ]);
    exit();
}
    
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Instructor Dashnoard</title>    
     <link rel="icon" type="image" href="hk_logo.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
            background-image: url('hkat-upang.jpg'); /* Path to the uploaded image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: margin-left .5s; /* Animation for sidebar toggle */
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
        .main-content {
            flex-grow: 1;
            padding: 20px;
            color: white;
            transition: margin-left .5s; /* Animation for main content */
            margin-left: 0px; /*Initial margin for main content */
        }
        .main-content.hidden {
            margin-left: 0; /* Adjust margin when sidebar is hidden */
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid #b8860b;
        }
        th {
            background-color: #4a5d29;
        }
        input[type="date"], input[type="submit"], select {
            padding: 10px;
            border-radius: 4px;
            margin-right: 10px;
            font-size: 16px;
        }
        .schedule-form {
            margin-top: 20px;
        }
        .no-results {
            color: white; /* Red color for no results */
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar" id="sidebar">
        <div class="logo"></div>
        <h2>UPang HK <br> Attendance Tracker</h2> 
        <div class="nav-item active"><a href="instructor-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="schedule-assign.php">Student Assign</a></div>
            <div class="nav-item"><a href="teacher-profile.php">Profile</a></div>
        <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div> 
    </div>
    <div class="main-content" id="main-content">
        <div class="dashboard-title">Schedule for <?php echo $searchDate; ?></div>
        
        <form method="GET" action="" class="schedule-form">
            <input type="date" name="searchDate" value="<?php echo $searchDate; ?>" required>
            <input type="submit" value="Search">
        </form>

        <table>
        <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Attendance Status</th>
                        <th>HK Status</th>
                        <th>Total Hours Rendered<th>
                        <th>Total Hours Remaining</th>
                        <th>Actions</th>
                    </tr>
                </thead>
        </table>
        <?php if (empty($scheduleList)): ?>
            <div class="no-results">No schedules found for this date.</div>
        <?php else: ?>
            <table>
                <tbody>
                    <?php foreach ($scheduleList as $schedule): ?>
                        <tr>
                            <td><?php echo $schedule['name']; ?></td>
                            <td><?php echo date("H:i", strtotime($schedule['start_time'])); ?></td>
                            <td><?php echo date("H:i", strtotime($schedule['end_time'])); ?></td>
                            <td><?php echo $schedule['attendance_status']; ?></td>
                            <td><?php echo $schedule['hk_status']; ?></td>
                            <!-- STATIC ADDITIONAL FIELD -->
                            <td>1<td>
                            <td><?php echo number_format($schedule['total_hours'], 2); ?> hours</td> <!-- Display total hours in decimal -->
                            <td>
                                <select class="status-select" data-schedule-id="<?php echo $schedule['schedule_id']; ?>">
                                    <option value="">Change Status</option>
                                    <option value="Approved">Present</option>
                                    <option value="Absent">Absent</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function () {
        const scheduleId = this.dataset.scheduleId;
        const status = this.value;

        if (scheduleId && status) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ schedule_id: scheduleId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload(); // Reload to reflect changes
            })
            .catch(error => console.error('Error:', error));
        }
    });
});

</script>

</body>
</html>
