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

// Fetch schedule details for editing
$schedule_id = $_GET['schedule_id'] ?? null;

if ($schedule_id) {
    $schedule_query = $conn->query(
        "SELECT s.schedule_id, s.user_id, s.date, s.start_time, s.end_time, s.subject, s.classroom, st.name 
         FROM schedule s 
         JOIN students st ON s.user_id = st.user_id 
         WHERE s.schedule_id = $schedule_id"
    );
    $schedule = $schedule_query->fetch_assoc();
}

// Handle schedule editing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $user_id = $_POST['user_id'];  // Correct hidden input usage
    $subject = $_POST['subject'];
    $classroom = $_POST['classroom'];

    // Validate times
    if ($start_time >= $end_time) {
        $_SESSION['error_message'] = "Start time must be earlier than end time.";
        header("Location: schedule-edit.php?schedule_id=$schedule_id");
        exit();
    }

    // Prepare the update query
    $stmt = $conn->prepare(
        "UPDATE schedule 
         SET user_id = ?, date = ?, start_time = ?, end_time = ?, subject = ?, classroom = ? 
         WHERE schedule_id = ?"
    );
    $stmt->bind_param("isssssi", $user_id, $date, $start_time, $end_time, $subject, $classroom, $schedule_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Schedule updated successfully.";
        header("Location: schedule-edit.php?schedule_id=$schedule_id&success=1");
        exit();
    } else {
        $_SESSION['error_message'] = "An error occurred while updating the schedule.";
        header("Location: schedule-edit.php?schedule_id=$schedule_id");
        exit();
    }
}

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// PHP logout logic
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: multi-login.php"); // Redirect to login page
    exit(); // Exit after redirection
}

// Function to log changes
function logChanges($conn, $schedule_id, $current_schedule, $changed_by, $date, $start_time, $end_time, $subject, $classroom) {
    $fields = [
        'date' => [$current_schedule['date'], $date],
        'start_time' => [$current_schedule['start_time'], $start_time],
        'end_time' => [$current_schedule['end_time'], $end_time],
        'subject' => [$current_schedule['subject'], $subject],
        'classroom' => [$current_schedule['classroom'], $classroom]
    ];

    foreach ($fields as $field => [$old_value, $new_value]) {
        if ($old_value !== $new_value) {
            $stmt = $conn->prepare(
                "INSERT INTO schedule_logs (schedule_id, changed_by, changed_field, old_value, new_value)
                VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("iisss", $schedule_id, $changed_by, $field, $old_value, $new_value);
            $stmt->execute();
            $stmt->close();
        }
    }
}


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Edit Schedule - UPang HK Attendance Tracker</title>
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
        .content-box {
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
        .submit-button:hover {
            background-color: #BFA93B;
        }
        .sidebar h2 {
            text-align: center;
            color: #4a5d29;
            font-size: medium;
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
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="instructor-db.php">Dashboard</a></div>
            <div class="nav-item active"><a href="schedule-assign.php">Scholar Assign</a></div>
            <div class="nav-item"><a href="teacher-profile.php">Profile</a></div>
            <div class="nav-item">
            <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>
        </div>
        <div class="main-content">
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
            <h1 class="title">EDIT SCHOLAR ASSIGNMENT INFORMATION</h1>
            <div class="content-box">
                <?php if ($schedule): ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?schedule_id=' . htmlspecialchars($schedule_id); ?>">
                        <div class="form-group">
                            <label for="student">Student Name:</label>
                            <input type="text" value="<?php echo htmlspecialchars($schedule['name']); ?>" readonly>
                            <input type="hidden" name="user_id" value="<?php echo $schedule['user_id']; ?>">
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
                                min="07:00"
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
                                max="18:30"
                                value="<?php echo $schedule['end_time']; ?>" 
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject Code:</label>
                            <input type="text" name="subject" value="<?php echo $schedule['subject']; ?>" required
                            pattern="[A-Z0-9 ]+" 
                            maxlength="6"
                            oninput="uppercaseInput(this)"
                            title="Only uppercase letters and numbers are allowed.">
                        </div>

                        <div class="form-group">
                            <label for="classroom">Classroom:</label>
                            <input type="text" name="classroom" value="<?php echo $schedule['classroom']; ?>" required
                            pattern="[A-Z0-9 ]+" 
                            maxlength="6"
                            oninput="uppercaseInput(this)"
                            title="Only uppercase letters and numbers are allowed.">
                        </div>
                        <button type="submit" class="submit-button">Update</button>
                    </form>
                <?php else: ?>
                    <p>Schedule not found.</p>
                <?php endif; ?>

                <?php if ($success_message): ?>
                <div style="color: yellow;"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div style="color: red;"><?php echo $error_message; ?></div>
            <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
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

        function uppercaseInput(input) {
            // Split the input value by spaces, capitalize each word, and join them back together
            input.value = input.value
                .toUpperCase() 
                .split(' ') 
                .join(' '); 
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('sidebar-hidden');
        }
    </script>
</body>
</html>