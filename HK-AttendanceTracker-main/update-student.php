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

// Define the connection parameters
$host = "localhost";
$username = "root";
$password = "";
$dbname = "hk-management";

// Create a new connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if an ID was provided
if (isset($_GET['user_id'])) {
    $id = $_GET['user_id'];

    // Query to get the user data, including total_hours and student_id
    $sql = "SELECT user_id, student_id, name, email, course, level, hk_status, total_hours, status FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the user data
    $student = $result->fetch_assoc();
} else {
    die("User ID not provided.");
}

// Update user information on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $hk_status = $_POST['hk_status'];
    $total_hours = $_POST['total_hours']; // Get total_hours from form submission
    $status = $_POST['status'];

    // Update the user data, including total_hours and status
    $update_sql = "UPDATE students SET name=?, email=?, course=?, level=?, hk_status=?, total_hours=?, status=? WHERE user_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssssi", $name, $email, $course, $year_level, $hk_status, $total_hours, $status, $id);
    $update_stmt->execute();

    if ($update_stmt->affected_rows > 0) {
        // $message = "User updated successfully.";
        $_SESSION['success_message'] = "User updated succesfully.";
        header("Location: student-list.php");
        exit();
    } else {
        // $message = "No changes made or update failed.";
        $_SESSION['success_message'] = "No changes made or update failed.";
        header("Location: student-list.php");
        exit();
    }
}

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Edit Student - UPang HK Attendance Tracker</title>
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
            margin-left: -220px; /* When sidebar is hidden, extend content to full width */
        }
        .main-content:not(.sidebar-hidden) {
            margin-left: 10px; /* When sidebar is visible, keep content shifted */
        }
        .title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
        }
        /* form {
            background-color: #b8860b;
            padding: 20px;
            border-radius: 5px
        } */
        form {
            padding: 10px 20px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-right: 20px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 1px;
        }
        .form-group input {
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: white;
            color: black;
        }
        .form-group select {
            width: 103%;
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
            justify-self: flex-start;
            font-size: 16px;
        }
        input[type="text"], input[type="email"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9; 
            transition: border-color 0.3s ease;
            
        }
        .toggle-btn {
            background-color: #A98D00; 
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            background-color: #556b2f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-button:hover {
            background-color: #BFA93B;
        }
        .message {
            color: yellow;
            margin-top: 20px;
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

            totalHoursInput.value = hours; // Update total hours input
        }
    </script>
</head>
<body>

<div class="container">
    <div class="sidebar" id="sidebar">
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
    
    <div class="main-content">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h1 class="title">EDIT SCHOLAR INFORMATION</h1>
        <?php if (isset($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="student_id">Student No.:</label>
                <input type="text" name="student_id" 
                    value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" 
                    value="<?php echo htmlspecialchars($student['name']); ?>" required
                    pattern="[A-Za-z\s]+" title="Please enter letters and spaces only."
                    placeholder="Enter name of student (e.g., John Doe)">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" 
                    value="<?php echo htmlspecialchars($student['email']); ?>" required
                    placeholder="e.g., ab.cd.up@phinmaed.com" 
                    pattern="^[a-z.]+\.up@phinmaed\.com$" 
                    title="Please enter a valid email address ending with .up@phinmaed.com and containing only lowercase letters and dots">
            </div>

            <div class="form-group">
                <label for="course">Course:</label>
                <input type="text" name="course" 
                    value="<?php echo htmlspecialchars($student['course']); ?>" required
                    pattern="[A-Za-z\s]+" title="Please enter letters and spaces only."
                    placeholder="Enter course (e.g., BSIT)"
                    oninput="uppercaseInput(this)" maxlength="4">
            </div>

            <div class="form-group">
                <label for="year_level">Year Level:</label>
                <input type="number" name="year_level" 
                    value="<?php echo htmlspecialchars($student['level']); ?>" required
                    min="1" max="5" 
                    placeholder="Enter year level" 
                    title="Please enter a number between 1 and 5.">
            </div>

            <div class="form-group">
                <label for="total_hours">Total Hours:</label>
                <input type="text" name="total_hours" 
                    value="<?php echo htmlspecialchars($student['total_hours']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="hk_status">HK Status:</label>
                <select name="hk_status" required>
                    <option value="HK25" <?php echo ($student['hk_status'] == 'HK25') ? 'selected' : ''; ?>>HK25</option>
                    <option value="HK50" <?php echo ($student['hk_status'] == 'HK50') ? 'selected' : ''; ?>>HK50</option>
                    <option value="HK75" <?php echo ($student['hk_status'] == 'HK75') ? 'selected' : ''; ?>>HK75</option>
                    <option value="HK100" <?php echo ($student['hk_status'] == 'HK100') ? 'selected' : ''; ?>>HK100</option>
                </select>
            </div>

            <div class="form-group">
                <label for="student_status">Student Status:</label>
                <select name="status" required>
                    <option value="Active" <?php echo ($student['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($student['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="submit-button">Update</button>
        </form>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        updateTotalHours();
    });
    function uppercaseInput(input) {
        // Split the input value by spaces, capitalize each word, and join them back together
        input.value = input.value
            .toUpperCase() // Convert entire string to lowercase first
            .split(' ') // Split into words
            .join(' '); // Join words back into a string
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
