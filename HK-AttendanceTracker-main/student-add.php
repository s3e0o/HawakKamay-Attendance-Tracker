<?php
session_start();

require 'db-connection.php';

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
    $course = strtoupper($_POST['course']); // Capitalize course field strtoupper
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
            $total_hours = 50;
            $hk_equivalent_hours = 50; 
            break;
        case 'HK50':
            $total_hours = 90;
            $hk_equivalent_hours = 90; 
            break;
        case 'HK75':
            $total_hours = 120;
            $hk_equivalent_hours = 120; 
            break;
        case 'HK100':
            $total_hours = 150;
            $hk_equivalent_hours = 150; 
            break;
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "hk-management");

    // Check for connection error
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check for duplicates in the scholars table
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
        $_SESSION['error_message'] = "A scholar with this ID or name already exists.";
        header("Location: student-add.php");
        exit();
    } elseif ($result_email->num_rows > 0) {
        $_SESSION['error_message'] = "This email is already registered.";
        header("Location: student-add.php");
        exit();
    } else {
        // Hash the scholar ID to use as the default password
        $hashed_password = password_hash($studentId, PASSWORD_DEFAULT);

        // First, insert the new user into the 'users' table
        $insert_user_sql = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, 'student')";
        $stmt_user = $conn->prepare($insert_user_sql);
        $stmt_user->bind_param("sss", $email, $studentId, $hashed_password);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id; // Get the ID of the inserted user

        // Insert the scholar data into the 'scholars' table
        $insert_student_sql = "INSERT INTO students (student_id, name, email, course, level, hk_status, total_hours, hk_equivalent_hours, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_student = $conn->prepare($insert_student_sql);
        $stmt_student->bind_param("ssssssiii", $studentId, $name, $email, $course, $level, $hk_status, $total_hours, $hk_equivalent_hours, $user_id);
        $stmt_student->execute();

        // Redirect back to the scholar list page after saving
        $_SESSION['success_message'] = "Scholar added successfully.";
        header("Location: student-list.php");
        exit();
    }
}

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

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
    <title>Admin Add Scholar - UPang HK Attendance Tracker</title>
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
        .content-box {
            /*background-color: #4a5d29;*/
            border-radius: 20px;
            padding: 5px 10px 5px 10px;
            width: 80%
        }
        .title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
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
            justify-self: start;
            font-size: 16px;
        }
        .submit-button:hover {
            background-color: #BFA93B;
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
            main {
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
    <script>
        function updateTotalHours() {
            var hkSelect = document.querySelector('select[name="hk_status"]');
            var totalHoursInput = document.querySelector('input[name="total_hours"]');
            var selectedHK = hkSelect.value;

            var hours = 0;
            switch (selectedHK) {
                case 'HK25':
                    hours = 50;
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
            <div class="nav-item"><a href="teacher-list.php">Faculty</a></div>
            <div class="nav-item active"><a href="student-list.php">Scholar</a></div>
            <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <main>
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
            <h1 class="title">ADD SCHOLAR</h1>

            <!-- <div class="content-box">
                <div class="header">
                    <h2>ADMIN</h2>
                </div> -->
                <div class="content-box">   
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="student_id">StudentID:</label>
                            <input type="text" id="student_id" name="student_id" required
                                placeholder="Enter StudentID (e.g., 03-1234-56789)"
                                pattern="^\d{2}-\d{4}-\d{5,6}$" 
                                title="Format must be: 00-0000-00000 (two digits, dash, four digits, dash, five to six digits)">
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name"  
                                placeholder="Enter name of scholar (e.g., John Doe)"
                                pattern="^[A-Za-z\s]+$" 
                                title="Please enter letters only." 
                                maxlength="50" required
                                oninput="capitalizeName(this)">
                        </div>
                        <div class="form-group">
                            <label for="course">Course:</label>
                            <input type="text" id="course" name="course" 
                                placeholder="Enter course of scholar (e.g., BSIT)"
                                pattern="^[A-Za-z\s]+$" 
                                title="Please enter letters only." 
                                minlength="3"
                                maxlength="4" required
                                oninput="uppercaseInput(this)">
                        </div>
                        <div class="form-group">
                            <label for="level">Year Level:</label>
                            <input type="number" id="level" name="level" 
                                min="1" max="5" 
                                placeholder="Enter year level of scholar"
                                title="Please enter a number between 1 and 5."
                                required>
                        </div>
                        <div class="form-group">
                            <label for="hk_status">HK Status:</label>
                            <select id="hk_status" name="hk_status" onchange="updateTotalHours()" required>
                                <option value="" disabled selected>Select HK Status</option>
                                <option value="HK25">HK25</option>
                                <option value="HK50">HK50</option>
                                <option value="HK75">HK75</option>
                                <option value="HK100">HK100</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required
                                placeholder="e.g., ab.cd.up@phinmaed.com" 
                                pattern="^[a-z.]+\.up@phinmaed\.com$" 
                                title="Please enter a valid email address ending with .up@phinmaed.com and containing only lowercase letters and dots">
                        </div>
                        <button type="submit" class="submit-button" name="save">Save</button>
                    </form>

                    <?php if ($success_message): ?>
                        <div style="color: yellow;"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div style="color: red;"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        function capitalizeName(input) {
            // Split the input value by spaces, capitalize each word, and join them back together
            input.value = input.value
                .toLowerCase() // Convert entire string to lowercase first
                .split(' ') // Split into words
                .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize the first letter
                .join(' '); // Join words back into a string
        }

        function uppercaseInput(input) {
            // Split the input value by spaces, capitalize each word, and join them back together
            input.value = input.value
                .toUpperCase() // Convert entire string to lowercase first
                .split(' ') // Split into words
                .join(' '); // Join words back into a string
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
