<?php
session_start();
// Database connection settings
$host = 'localhost';
$dbname = 'hk-management';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students for the dropdown
$students = $conn->query("SELECT user_id, name FROM students");

// Fetch existing schedule details for editing
$schedule_id = $_GET['schedule_id'] ?? null;

if ($schedule_id) {
    $schedule_query = $conn->query("SELECT * FROM schedule WHERE schedule_id = $schedule_id");
    $schedule = $schedule_query->fetch_assoc();
}

// Handle schedule editing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $user_id = $_POST['student_id']; // Use hidden input
    $subject = $_POST['subject'];
    $classroom = $_POST['classroom'];

    if ($start_time >= $end_time) {
        echo "<script>alert('Error: Start time must be earlier than end time.');</script>";
    } else {
        $stmt = $conn->prepare(
            "UPDATE schedule 
             SET user_id = ?, `date` = ?, start_time = ?, end_time = ?, subject = ?, classroom = ?,
             WHERE schedule_id = ?"
        );
        $stmt->bind_param("isssssi", $user_id, $date, $start_time, $end_time, $subject, $classroom, $schedule_id);

        if ($stmt->execute()) {
            // Redirect to the edit page or a summary page
            header("Location: schedule-edit.php?schedule_id=$schedule_id&success=1");
            exit();
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

// PHP logout logic
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
    <title>Instructor Dashboard</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
            background-image: url('hkat-upang.jpg'); /* Use the same background */
            background-size: cover;
            background-position: center;
        }
        .container {
            display: flex;
            height: 100%;
        }
        .sidebar {
            width: 200px;
            background-color: #A98D00;
            color: white;
            padding: 20px;
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
        .sidebar .logout-btn:hover {
            background-color: #ff3333;
            transition: background-color 0.3s ease;
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
        .main-content {
            flex-grow: 1;
            padding: 20px;
            color: white;
        }
        h1 {
            font-size: 24px;
        }
        .content-box {
            /*background-color: #4a5d29;*/
            border-radius: 10px;
            padding: 20px;
            width: 80%;
        }
        form {
            display: grid;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
        }
        .form-group input, select {
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: white;
            color: black;
        }
        .submit-button {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            justify-self: end;
            font-size: 16px;
        }
        .sidebar h2 {
            text-align: center;
            color: #4a5d29;
            font-size: medium;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="instructor-db.php">Dashboard</a></div>
            <div class="nav-item active"><a href="schedule-assign.php">Student Assign</a></div>
            <div class="nav-item"><a href="teacher-profile.php">Profile</a></div>
            <div class="nav-item">
            <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>
            <!-- Add other menu items as needed -->
        </div>
        <div class="main-content">
            <h1>Edit Schedule</h1>
            <div class="content-box">
                <?php if ($schedule): ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="student">Student:</label>
                            <input type="text" name="student" 
                                value="<?php echo htmlspecialchars($students->fetch_assoc()['name']); ?>" 
                                readonly>
                            <input type="hidden" name="student_id" value="<?php echo $schedule['user_id']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="date">Select Date:</label>
                            <input 
                                type="date" 
                                name="date" 
                                value="<?php echo $schedule['date']; ?>" 
                                required 
                                min="<?php echo date('Y-m-d'); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="start_time">Select Start Time:</label>
                            <input 
                                type="time" 
                                name="start_time" 
                                value="<?php echo $schedule['start_time']; ?>" 
                                required 
                                onchange="validateEndTime()"
                            >
                        </div>
                        <div class="form-group">
                            <label for="end_time">Select End Time:</label>
                            <input 
                                type="time" 
                                name="end_time" 
                                value="<?php echo $schedule['end_time']; ?>" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject:</label>
                            <input type="text" name="subject" value="<?php echo $schedule['subject']; ?>" required
                            pattern="[A-Z0-9 ]+" 
                            title="Only uppercase letters and numbers are allowed.">
                        </div>
                        <div class="form-group">
                            <label for="classroom">Classroom:</label>
                            <input type="text" name="classroom" value="<?php echo $schedule['classroom']; ?>" required
                            pattern="[A-Z0-9 ]+" 
                            title="Only uppercase letters and numbers are allowed.">
                        </div>
                        <button type="submit" class="submit-button">Update</button>
                    </form>
                <?php else: ?>
                    <p>Schedule not found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Display success or error alerts based on URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            alert('Schedule updated successfully!');
        }
        if (urlParams.has('error')) {
            alert('An error occurred while updating the schedule.');
        }
        function setMinStartTime() {
            const dateInput = document.querySelector('input[name="date"]');
            const startTimeInput = document.querySelector('input[name="start_time"]');
            const today = new Date().toISOString().split('T')[0];

            if (dateInput.value === today) {
                const currentTime = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                startTimeInput.min = currentTime;
            } else {
                startTimeInput.min = '00:00';
            }
        }

        function validateEndTime() {
            const startTimeInput = document.querySelector('input[name="start_time"]');
            const endTimeInput = document.querySelector('input[name="end_time"]');

            endTimeInput.min = startTimeInput.value;
        }
    </script>
</body>
</html>