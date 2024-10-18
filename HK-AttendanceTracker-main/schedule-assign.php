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

// Fetch the assigned teacher for the logged-in student
$student_id = $_SESSION['id']; // Assuming you store the student ID in the session
$teacher = null;

if ($student_id) {
    $sqlTeacher = "SELECT u.username AS teacher_name 
                   FROM users u 
                   INNER JOIN schedule s ON u.id = s.assigned_by 
                   WHERE s.user_id = ? LIMIT 1";

    $stmt = $conn->prepare($sqlTeacher);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
    }

    $stmt->close();
}

// Handle schedule assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $user_id = $_POST['student'];
    $subject = $_POST['subject'];
    $classroom = $_POST['classroom'];

    if ($start_time >= $end_time) {
        echo "Error: Start time must be earlier than end time.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO schedule (user_id, `date`, start_time, end_time, subject, classroom) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssss", $user_id, $date, $start_time, $end_time, $subject, $classroom);

        if ($stmt->execute()) {
            echo "Schedule assigned successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// PHP logout logic
if (isset($_GET['logout'])) {
    // Destroy the session
    session_destroy();
    // Redirect to the login page
    header("Location: multi-login.php");
    exit(); // Exit after header redirection
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
            <h1>Assign Student</h1>
            <div class="content-box">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="student">Select Student:</label>
                        <select name="student" required>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <option value="<?php echo $row['user_id']; ?>"><?php echo $row['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Select Date:</label>
                        <input 
                            type="date" 
                            name="date" 
                            required 
                            min="<?php echo date('Y-m-d'); ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="start_time">Select Start Time:</label>
                        <input 
                            type="time" 
                            name="start_time" 
                            required 
                            min="07:00"
                            onchange="validateEndTime()"
                        >
                    </div>
                    <div class="form-group">
                        <label for="end_time">Select End Time:</label>
                        <input 
                            type="time" 
                            name="end_time" 
                            required
                            max="18:30"
                        >
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" name="subject" required
                        placeholder="e.g., ITE314"
                        pattern="[A-Z0-9 ]+" 
                        title="Only uppercase letters and numbers are allowed.">
                    </div>
                    <div class="form-group">
                        <label for="classroom">Classroom:</label>
                        <input type="text" name="classroom" required
                        placeholder="e.g., ITS201"
                        pattern="[A-Z0-9 ]+" 
                        title="Only uppercase letters and numbers are allowed.">
                    </div>
                    <button type="submit" class="submit-button">Assign</button>
                </form>
            </div>
        </div>
    </div>

<!--<script>
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
</script>-->
</body>
</html>
