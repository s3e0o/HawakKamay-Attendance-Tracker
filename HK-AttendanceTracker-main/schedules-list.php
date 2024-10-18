<?php
session_start();

require 'db-connection.php';

// Fetch all schedules along with student names
$sql = "
    SELECT 
        s.schedule_id, 
        st.name AS student_name, 
        s.date, 
        s.start_time, 
        s.end_time, 
        s.subject, 
        s.classroom, 
        s.attendance_status, 
        s.assigned_by 
    FROM schedule s
    INNER JOIN students st ON s.user_id = st.user_id
    ORDER BY s.date, s.start_time";

$result = $conn->query($sql);

// PHP logout logic
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: multi-login.php"); // Redirect to login page
    exit(); // Exit after redirection
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
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
        .main-content {
            flex-grow: 1;
            padding: 20px;
            color: white;
        }
        h1 {
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid white;
            text-align: left;
        }
        th {
            background-color: #4a5d29;
        }
        .sidebar h2 {
            text-align: center;
            font-size: medium;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Instructor</a></div>
            <div class="nav-item"><a href="student-list.php">Student</a></div>
            <div class="nav-item active"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <div class="main-content">
            <h1>All Student Schedules</h1>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Subject</th>
                        <th>Classroom</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['schedule_id']; ?></td>
                                <td><?php echo $row['student_name']; ?></td>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['start_time']; ?></td>
                                <td><?php echo $row['end_time']; ?></td>
                                <td><?php echo $row['subject']; ?></td>
                                <td><?php echo $row['classroom']; ?></td>
                                <td><?php echo $row['attendance_status']; ?></td>
                                <td><?php echo $row['assigned_by']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">No schedules found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function validateTimeInputs() {
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');

    const startTime = startTimeInput.value;
    const endTime = endTimeInput.value;

    if (startTime && endTime && startTime >= endTime) {
        alert("Error: Start time must be earlier than end time.");
        endTimeInput.value = ""; // Reset end time if invalid
    }
}
    </script>
</body>
</html>
