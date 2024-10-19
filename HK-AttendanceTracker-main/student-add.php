<?php
session_start();

require 'db-connection.php';

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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    // Collect form data
    $studentId = $_POST['student_id'];
    $name = $_POST['name'];
    $course = ($_POST['course']); // Capitalize course field strtoupper
    $level = $_POST['level'];
    $hk_status = $_POST['hk_status'];
    $email = $_POST['email'];

    // Validate name: letters only
    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $_SESSION['error'] = "Name can only contain letters.";
        header("Location: student-add.php");
        exit();
    }

    // Validate level: numbers from 1 to 5
    if (!preg_match("/^[1-5]$/", $level)) {
        $_SESSION['error'] = "Level must be a number between 1 and 5.";
        header("Location: student-add.php");
        exit();
    }

    // Calculate total hours based on HK status
    $total_hours = 0;
    switch ($hk_status) {
        case 'HK25':
            $total_hours = 25; // Corrected to 25 hours
            break;
        case 'HK50':
            $total_hours = 90;
            break;
        case 'HK75':
            $total_hours = 120;
            break;
        case 'HK100':
            $total_hours = 150;
            break;
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "hk-management");

    // Check for connection error
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check for duplicates in the students table
    $check_student_sql = "SELECT * FROM students WHERE student_id = ? OR name = ?";
    $stmt_check_student = $conn->prepare($check_student_sql);
    $stmt_check_student->bind_param("ss", $studentId, $name);
    $stmt_check_student->execute();
    $result_student = $stmt_check_student->get_result();

    // Check for duplicates in the users table
    $check_email_sql = "SELECT * FROM users WHERE email = ?";
    $stmt_check_email = $conn->prepare($check_email_sql);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_email = $stmt_check_email->get_result();

    // If duplicates are found, set a session message and redirect back
    if ($result_student->num_rows > 0) {
        $_SESSION['error'] = "A student with this ID or name already exists.";
        header("Location: student-add.php");
        exit();
    } elseif ($result_email->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered.";
        header("Location: student-add.php");
        exit();
    } else {
        // Hash the student ID to use as the default password
        $hashed_password = password_hash($studentId, PASSWORD_DEFAULT);

        // First, insert the new user into the 'users' table
        $insert_user_sql = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, 'student')";
        $stmt_user = $conn->prepare($insert_user_sql);
        $stmt_user->bind_param("sss", $email, $studentId, $hashed_password);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id; // Get the ID of the inserted user

        // Insert the student data into the 'students' table
        $insert_student_sql = "INSERT INTO students (student_id, name, email, course, level, hk_status, total_hours, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_student = $conn->prepare($insert_student_sql);
        $stmt_student->bind_param("ssssssii", $studentId, $name, $email, $course, $level, $hk_status, $total_hours, $user_id);
        $stmt_student->execute();

        // Redirect back to the student list page after saving
        header("Location: student-list.php");
        exit();
    }
}

// Display error message if set
if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']); // Clear the message after displaying
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPang HK Attendance Tracker - Admin</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            color: white;
            background-color: red; /* Change as needed */
        }
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
        main {
            flex-grow: 1;
            /* background-color: #556b2f; */
            padding: 20px;
            color: white;
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
        }
        .content-box {
            /*background-color: #4a5d29;*/
            border-radius: 20px;
            padding: 5px 10px 5px 10px;
            width: 80%
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
        }
        .add-new {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
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
        .form-group input {
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: white;
            color: black;
        }
        .form-group input::placeholder {
            color: gray;
        }
        select {
            padding: 8px;
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
    </style>
    <script>
        function updateTotalHours() {
            var hkSelect = document.querySelector('select[name="hk_status"]');
            var totalHoursInput = document.querySelector('input[name="total_hours"]');
            var selectedHK = hkSelect.value;

            var hours = 0;
            switch (selectedHK) {
                case 'HK25':
                    hours = 45;
                    break;
                case 'HK50':
                    hours = 90;
                    break;
                case 'HK75':
                    hours = 120;
                    break;
                case 'HK100':
                    hours = 150;
                    break;
            }
            totalHoursInput.value = hours;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
        <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Instructor</a></div>
            <div class="nav-item active"><a href="student-list.php">Student</a></div>
            <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <main>
            <div class="content-box">
                <div class="header">
                    <h2>ADMIN</h2>
                </div>
                <div class="content-box">
                    <h2>ADD STUDENT</h2>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="student_id">StudentID:</label>
                            <input type="text" id="student_id" name="student_id" required
                            placeholder="Enter StudentID (e.g., 03-1234-5678)"
                            pattern="^[0-9-]+$" 
                            title="Only numbers and dashes are allowed, with no spaces.">
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" required 
                                placeholder="Enter name of student(e.g., John Doe)"
                                pattern="[A-Za-z\s]+" title="Please enter letters only." >
                        </div>
                        <div class="form-group">
                            <label for="course">Course:</label>
                            <input type="text" id="course" name="course" required
                            placeholder="Enter course of student(e.g., BSIT)"
                            pattern="[A-Za-z\s]+" title="Please enter letters only." 
                            >
                        </div>
                        <div class="form-group">
                            <label for="level">Year Level:</label>
                            <input type="number" id="level" name="level" required
                                min="1" max="5" 
                                placeholder="Enter year level of student"
                                title="Please enter a number between 1 and 5."
                                >
                        </div>
                        <div class="form-group">
                            <label for="hk_status">HK Status:</label>
                            <select id="hk_status" name="hk_status" onchange="updateTotalHours()" required
                                placeholder="Enter hk status of student">
                                <option value="HK25">HK25</option>
                                <option value="HK50">HK50</option>
                                <option value="HK75">HK75</option>
                                <option value="HK100">HK100</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required
                                placeholder="Enter email of student">
                        </div>
                        <button type="submit" class="submit-button" name="save">Save</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
