<?php
session_start();

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
if (isset($_GET['userId'])) {
    $id = $_GET['userId'];

    // Query to get the user data, including total_hours
    $sql = "SELECT user_id, name, email, course, level, hk_status, total_hours, status FROM students WHERE user_id = ?";
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
        $message = "User updated successfully.";
    } else {
        $message = "No changes made or update failed.";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        /* Your existing styles */
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
        .logout-btn {
            margin-top: auto; /* Push this to the bottom */
            padding: 10px; /* Optional: Adjust padding */
            text-align: center; /* Center text */
            color: white; /* Button text color */
            background-color: #f44336; /* Default button color */
            border: none; /* Remove default border */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s ease; /* Smooth transition */
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            color: white;
        }
        .dashboard-title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
        }
        form {
            /*background-color: #b8860b;*/
            padding: 20px;
            border-radius: 5px;
        }
        input[type="text"], input[type="email"],select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9; /* Light background for input */
            transition: border-color 0.3s ease;
            
        }
        .toggle-btn {
            background-color: #A98D00; /* Color of the toggle button */
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
        input[type="submit"]:hover {
            background-color: #4a5d29;
        }
        .message {
            color: yellow;
            margin-top: 20px;
        }
        .section-title {
            font-size: 18px;
            margin: 20px 0 10px;
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
        <div class="nav-item"><a href="teacher-list.php">Instructor</a></div>
        <div class="nav-item active"><a href="student-list.php">Student</a></div>
        <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
        <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
        <div class="nav-item">
            <a href="?logout=true" class="logout-btn">Log Out</a>
        </div>   
    </div> 
    
    <div class="main-content">
        <h2 class="dashboard-title">Update Student Information</h2>

        <?php if (isset($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($student['name']); ?>" required
                placeholder="Enter name of student(e.g., John Doe)"
                pattern="[A-Za-z\s]+" title="Please enter letters only.">
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            <input type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($student['course']); ?>" required
                placeholder="Enter course of student(e.g., BSIT)"
                pattern="[A-Za-z\s]+" title="Please enter letters only.">
            <input type="text" name="year_level" placeholder="Year Level" value="<?php echo htmlspecialchars($student['level']); ?>" required
                min="1" max="5" 
                placeholder="Enter year level of student"
                title="Please enter a number between 1 and 5.">
            <input type="text" name="total_hours" value="<?php echo htmlspecialchars($student['total_hours']); ?>" readonly> 

            
            <select name="hk_status" onchange="updateTotalHours()" required>
                <option value="HK25" <?php echo ($student['hk_status'] == 'HK25') ? 'selected' : ''; ?>>HK25</option>
                <option value="HK50" <?php echo ($student['hk_status'] == 'HK50') ? 'selected' : ''; ?>>HK50</option>
                <option value="HK75" <?php echo ($student['hk_status'] == 'HK75') ? 'selected' : ''; ?>>HK75</option>
                <option value="HK100" <?php echo ($student['hk_status'] == 'HK100') ? 'selected' : ''; ?>>HK100</option>
            </select>

            <select name="status" required>
                <option value="Active" <?php echo ($student['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo ($student['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            
            <!-- \<input type="text" name="total_hours" value="<?php echo htmlspecialchars($student['total_hours']); ?>" readonly> Make it a readonly input -->

            <input type="submit" value="Update User">
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        updateTotalHours();
    });
</script>

</body>
</html>
