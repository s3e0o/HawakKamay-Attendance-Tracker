<?php
session_start();

require 'db-connection.php';

// Initialize variables for the form fields
$teacherId = $name = $department = $email = '';

// Check if a teacher_id is provided for updating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Collect form data
    $teacherId = $_POST['teacher_id'];
    $name = $_POST['name'];
    $department = $_POST['department'];
    $email = $_POST['email'];
    
    // Database connection
    $conn = new mysqli("localhost", "root", "", "hk-management");

    // Check for connection error
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the teacher exists
    $check_teacher_sql = "SELECT * FROM teachers WHERE teacher_id = ?";
    $stmt_check_teacher = $conn->prepare($check_teacher_sql);
    $stmt_check_teacher->bind_param("s", $teacherId);
    $stmt_check_teacher->execute();
    $result_teacher = $stmt_check_teacher->get_result();

    if ($result_teacher->num_rows === 0) {
        $_SESSION['error'] = "No teacher found with this ID.";
        header("Location: teacher-list.php");
        exit();
    }

    // Check if the email already exists in the users table
    $check_email_sql = "SELECT * FROM users WHERE email = ? AND email != (SELECT email FROM users WHERE id = (SELECT user_id FROM teachers WHERE teacher_id = ?))";
    $stmt_check_email = $conn->prepare($check_email_sql);
    $stmt_check_email->bind_param("ss", $email, $teacherId);
    $stmt_check_email->execute();
    $result_email = $stmt_check_email->get_result();

    if ($result_email->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered.";
        header("Location: teacher-list.php");
        exit();
    }

    // Update the user in the 'users' table
    $update_user_sql = "UPDATE users SET email = ? WHERE id = (SELECT user_id FROM teachers WHERE teacher_id = ?)";
    $stmt_user = $conn->prepare($update_user_sql);
    $stmt_user->bind_param("ss", $email, $teacherId);
    $stmt_user->execute();

    // Update the teacher details in the 'teachers' table
    $update_teacher_sql = "UPDATE teachers SET name = ?, department = ? WHERE teacher_id = ?";
    $stmt_teacher = $conn->prepare($update_teacher_sql);
    $stmt_teacher->bind_param("sss", $name, $department, $teacherId);
    $stmt_teacher->execute();

    // Redirect back to the teacher list page after updating
    header("Location: teacher-list.php");
    exit();
}

// If a teacher_id is provided, fetch the existing data to pre-fill the form
if (isset($_GET['id'])) {
    $teacherId = $_GET['id'];
    
    // Fetch teacher details
    $fetch_teacher_sql = "SELECT name, department, email FROM teachers WHERE id = ?";
    $stmt_fetch_teacher = $conn->prepare($fetch_teacher_sql);
    $stmt_fetch_teacher->bind_param("s", $teacherId);
    $stmt_fetch_teacher->execute();
    $result_fetch_teacher = $stmt_fetch_teacher->get_result();

    if ($result_fetch_teacher->num_rows > 0) {
        $teacher_data = $result_fetch_teacher->fetch_assoc();
        $name = $teacher_data['name'];
        $department = $teacher_data['department'];
        
        // Fetch email from the users table using the user_id
        $fetch_email_sql = "SELECT email FROM users WHERE id = (SELECT user_id FROM teachers WHERE teacher_id = ?)";
        $stmt_fetch_email = $conn->prepare($fetch_email_sql);
        $stmt_fetch_email->bind_param("s", $teacherId);
        $stmt_fetch_email->execute();
        $result_fetch_email = $stmt_fetch_email->get_result();

        if ($result_fetch_email->num_rows > 0) {
            $email = $result_fetch_email->fetch_assoc()['email'];
        }
    }
}

// Display error message if set
if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']); // Clear the message after displaying
}
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
    <title>UPang HK Attendance Tracker - Admin</title>
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
        .logo {
            width: 150px; /* Adjust size */
            height: 150px; /* Ensure it's square */
            background-image: url('hk_logo.png'); /* Background image path */
            background-size: cover; /* Makes sure the image covers the entire div */
            background-position: center;
            border-radius: 50%; /* Make it a circle */
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
            padding: 20px;
            color: white;
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
        }
        .content-box {
            /*background-color: #4a5d29;*/
            border-radius: 10px;
            padding: 20px;
            /*box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); /* Added shadow for depth */
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"] {
            width: 70%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9; /* Light background for input */
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #4a5d29; /* Highlight border on focus */
            outline: none; /* Remove default outline */
        }
        .submit-button {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px; /* Increased font size */
        }
        .submit-button:hover {
            background-color: #a57900; /* Darker shade on hover */
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
        .actions {
            display: flex;
            gap: 10px;
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
        .delete-icon {
            color: #e24a4a;
        }
        .search-results-container {
            display: <?php echo !empty($searchResults) ? 'block' : 'none'; ?>; /* Show if there are results */
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item active"><a href="teacher-list.php">Instructor</a></div>
            <div class="nav-item"><a href="student-list.php">Student</a></div>
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
                <h2>Update Teacher Information </h2>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <!-- <div class="form-group">
                        <label for="teacher_id">Teacher ID:</label>
                        <input type="text" id="teacher_id" name="teacher_id" value="<?php echo htmlspecialchars($teacherId); ?>" readonly>
                    </div> -->
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter Name" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>" placeholder="Enter Department" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter Email" required>
                    </div>
                    <button type="submit" name="update" class="submit-button">Update</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
