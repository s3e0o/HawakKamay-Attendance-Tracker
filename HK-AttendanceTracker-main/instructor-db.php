<?php
// Start session at the very beginning of the file
session_start();

require 'db-connection.php';

// Database connection settings
$host = 'localhost';
$dbname = 'hk-management';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure only teacher role can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: multi-login.php");
    exit();
}

// Function to determine HK equivalent hours
function getHkEquivalentHours($hk_status) {
    switch ($hk_status) {
        case 'HK25':
            return 50;
        case 'HK50':
            return 90;
        case 'HK75':
            return 120;
        case 'HK100':
            return 150;
        default:
            return 0; // Default for any unexpected values
    }
}

// Set timezone and fetch search date
date_default_timezone_set('Asia/Manila');
$searchDate = date('Y-m-d');
if (isset($_GET['searchDate']) && !empty($_GET['searchDate'])) {
    $searchDate = $_GET['searchDate'];
}

$scheduleList = [];

// Fetch schedules and calculate HK equivalent hours
$stmt = $conn->prepare("
    SELECT sc.schedule_id, s.name, sc.start_time, sc.end_time, sc.attendance_status, 
       s.hk_status, s.total_hours, s.user_id AS student_id, sc.total_hours_rendered
        FROM schedule sc
        JOIN students s ON sc.user_id = s.user_id
        WHERE sc.date = ?
");
$stmt->bind_param("s", $searchDate);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['hk_equivalent_hours'] = getHkEquivalentHours($row['hk_status']);
        $scheduleList[] = $row;
    }
}
$stmt->close();

// Handle attendance status updates
if (isset($_POST['schedule_id']) && isset($_POST['status'])) {
    $schedule_id = $_POST['schedule_id'];
    $status = $_POST['status'];

    // Fetch schedule details
    $stmt = $conn->prepare("
        SELECT s.user_id AS student_id, s.total_hours, s.hk_status, sc.start_time, sc.end_time, 
               sc.attendance_status, sc.total_hours_rendered
        FROM schedule sc
        JOIN students s ON sc.user_id = s.user_id
        WHERE sc.schedule_id = ?
    ");
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $stmt->bind_result($student_id, $remaining_hours, $hk_status, $start_time, $end_time, $attendance_status, $hours_rendered);
    $stmt->fetch();
    $stmt->close();

    // Calculate the duration of the session in hours
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $duration = $end->diff($start);
    $session_hours = $duration->h + ($duration->i / 60); // Convert to hours

    // Get HK equivalent hours
    $hk_equivalent_hours = getHkEquivalentHours($hk_status);

    // Adjust hours based on attendance status
    if ($status === 'Present' && $attendance_status !== 'Present') {
        // Marking as present: Add session hours to rendered
        $hours_rendered += $session_hours;
    } elseif ($status === 'Absent' && $attendance_status === 'Present') {
        // Reverting to absent: Subtract session hours from rendered
        $hours_rendered = max(0, $hours_rendered - $session_hours);
    }

    // Calculate remaining hours accurately
    $remaining_hours = max(0, $hk_equivalent_hours - $hours_rendered);
    $remaining_hours = min($hk_equivalent_hours, $remaining_hours);

    // Update the schedule with new rendered hours and status
    $update_stmt = $conn->prepare("
        UPDATE schedule 
        SET total_hours_rendered = ?, attendance_status = ? 
        WHERE schedule_id = ?
    ");
    $update_stmt->bind_param("dsi", $hours_rendered, $status, $schedule_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Update the student's total hours in the students table
    $update_student_stmt = $conn->prepare("
        UPDATE students 
        SET total_hours = ? 
        WHERE user_id = ?
    ");
    $update_student_stmt->bind_param("di", $remaining_hours, $student_id);
    $update_student_stmt->execute();
    $update_student_stmt->close();

    // Send JSON response to avoid frontend errors
    $_SESSION['success_message'] = "Attendance status and hours updated successfully.";
    echo json_encode(['message' => 'Attendance status and hours updated successfully.']);
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $delete_stmt = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ?");
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        // echo "<script>alert('Schedule deleted successfully.'); window.location.href = 'instructor-db.php';</script>";
        $_SESSION['success_message'] = "Schedule deleted successfully.";
        header("Location: instructor-db.php");
        exit();
    } else {
        // echo "<script>alert('Failed to delete schedule.'); window.location.href = 'instructor-db.php';</script>";
        $_SESSION['error_message'] = "Failed to delete schedule.";
        header("Location: instructor-db.php");
        exit();
    }
    // $delete_stmt->close();
    // exit();
}

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: multi-login.php");
    exit();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Faculty Dashboard - UPang HK Attendance Tracker</title>    
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
            margin-left: -220px; 
        }
        .main-content:not(.sidebar-hidden) {
            margin-left: 10px; 
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
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #b8860b;
        }
        th {
            background-color: #4a5d29;
        }
        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .actions {
            display: flex;
            gap: 10px;
            cursor: pointer;
            padding: 25px;
        }
        .actions button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        .actions img {
            width: 20px;
            height: 20px;
        }
        .edit-icon {
            color: #4a90e2;
        }
        .edit-icon:hover {
            color: #88bffd;
            transform: scale(1.2);
        }
        .delete-icon {
            color: #e24a4a;
        }
        .delete-icon:hover {
            color: #ff8077;
            transform: scale(1.2);
        }
        input[type="date"]{
            padding: 10px;
            border-radius: 4px;
            margin-right: 10px;
            font-size: 16px;
            cursor: pointer;
        } input[type="submit"], select {
            padding: 10px;
            border-radius: 4px;
            margin-right: 10px;
            font-size: 16px;
            cursor: pointer;
            background-color: #A98D00;
            color: white;
        }
        select {
            background-color: #b8860b;
        }
        input[type="submit"]:hover {
            background-color: #BFA93B;
        }
        .schedule-form {
            margin-top: 20px;
            cursor: pointer;
        }
        .no-results {
            margin-top: 10px;
            color: white; 
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
            left: 200px; 
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                padding: 5px;
            }
            .main-content {
                padding: 10px;
            }
            .toggle-btn {
                left: 10px;
            }
            .logo {
                width: 100px;
                height: 100px;
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
    <div class="sidebar" id="sidebar">
        <div class="logo"></div>
        <h2>UPang HK <br> Attendance Tracker</h2> 
        <div class="nav-item active"><a href="instructor-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="schedule-assign.php">Scholar Assign</a></div>
            <div class="nav-item"><a href="teacher-profile.php">Profile</a></div>
        <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div> 
    </div>
    <div class="main-content" id="main-content">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
        <h1 class="title">SCHEDULE FOR <?php echo $searchDate; ?></h1>
        
        <form method="GET" action="" class="schedule-form">
            <input type="date" name="searchDate" value="<?php echo $searchDate; ?>" required>
            <input type="submit" value="Search">
        </form>

        <?php if ($success_message): ?>
            <div style="color: yellow;"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="color: red;"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (empty($scheduleList)): ?>
            <div class="no-results">No schedules found for this date.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>HK Status</th>
                        <th>HK Status <br> Equivalent Hours</th>
                        <th>Total Hours <br>Rendered</th>
                        <th>Total Hours <br>Remaining</th>
                        <th>Attendance <br>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduleList as $schedule): ?>
                        <tr>
                            <td><?php echo $schedule['name']; ?></td>
                            <td><?php echo date("H:i", strtotime($schedule['start_time'])); ?></td>
                            <td><?php echo date("H:i", strtotime($schedule['end_time'])); ?></td>
                            <td><?php echo $schedule['hk_status']; ?></td>
                            <td><?php echo $schedule['hk_equivalent_hours']; ?> hours</td>
                            <td><?php echo $schedule['total_hours_rendered']; ?> hours</td>
                            <td><?php echo number_format($schedule['total_hours'], 2); ?> hours</td> <!-- Display total hours in decimal -->
                            <td><?php echo $schedule['attendance_status']; ?></td>
                            <td class="actions">
                                <select class="status-select" data-schedule-id="<?php echo $schedule['schedule_id']; ?>">
                                    <option value="">Change Status</option>
                                    <option value="Present" <?php echo $schedule['attendance_status'] === 'Present' ? 'selected' : ''; ?>>Present</option>
                                    <option value="Absent" <?php echo $schedule['attendance_status'] === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                </select>
                                <button class="edit" onclick="location.href='schedule-edit.php?schedule_id=<?php echo urlencode($schedule['schedule_id']); ?>'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="edit-icon">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button class="delete" onclick="confirmDelete('<?php echo $schedule['schedule_id']; ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="delete-icon">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>

    function confirmDelete(scheduleId) {
        if (confirm('Are you sure you want to delete this schedule?')) {
            // Redirect to the same page with the delete_id parameter
            window.location.href = '?delete_id=' + encodeURIComponent(scheduleId);
        }
    }

    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function () {
            const scheduleId = this.dataset.scheduleId;
            const status = this.value;

            if (status) {
                fetch('instructor-db.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        schedule_id: scheduleId,
                        status: status
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        alert(data.message);  
                        location.reload();  
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update attendance status. Please try again.');
                });
            }
        });
    });

    
    function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('sidebar-hidden');
        }
</script>

</body>
</html>