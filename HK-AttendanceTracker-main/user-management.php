<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include 'admin_db.php'; 

// If the form is submitted, handle the POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data and validate input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['Course']);
    $year_level = trim($_POST['year_level']);
    $hk_number = trim($_POST['hk_number']);
    $student_id = trim($_POST['student_id']); 

    // Set the total hours based on HK number
    switch ($hk_number) {
        case 'HK25':
            $total_hours = 45;
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
        default:
            $total_hours = 0; 
            break;
    }

    // Simple validation to check if fields are not empty
    if (empty($name) || empty($email) || empty($course) || empty($year_level) || empty($hk_number) || empty($student_id)) {
        $message = "All fields are required!";
    } else {
        // Prepare the SQL statement to prevent SQL injection
        $sql = "INSERT INTO students (name, email, Course, year_level, hk_number, student_id, total_hours) 
                VALUES (:name, :email, :course, :year_level, :hk_number, :student_id, :total_hours)";

        try {
            // Prepare and execute the statement
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':course' => $course,
                ':year_level' => $year_level,
                ':hk_number' => $hk_number,
                ':student_id' => $student_id,
                ':total_hours' => $total_hours
            ]);

            $message = "User successfully added!";
        } catch (PDOException $e) {
            // Capture and display any SQL errors
            $message = "Error: " . $e->getMessage();
        }
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert User</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
        }
        .container {
            display: flex;
            height: 100%;
        }
        .sidebar {
            width: 200px;
            background-color: #b8860b;
            color: white;
            padding: 20px;
        }
        .logo {
            width: 100px;
            height: 100px;
            background-color: #556b2f;
            border-radius: 50%;
            margin: 0 auto 20px;
        }
        .nav-item {
            padding: 10px;
            margin: 5px 0;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            flex-grow: 1;
            background-color: #556b2f;
            padding: 20px;
            color: white;
        }
        .dashboard-title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
        }
        .section-title {
            font-size: 18px;
            margin: 20px 0 10px;
        }
        form {
            background-color: #b8860b;
            padding: 20px;
            border-radius: 5px;
            margin: 10px;
        }
        input[type="text"], input[type="email"], select {
            width: 98%;
            padding: 10px;
            margin: 10px 0px;
            border: none;
            border-radius: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
        </div>
        <div class="main-content">
            <div class="dashboard-title">Insert User</div>

            <form action="user-management.php" method="POST">
                <div class="section-title">Add New User</div>
                <input type="text" name="name" placeholder="Name" required><br>
                <input type="text" name="student_id" placeholder="Student ID" required><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="text" name="Course" placeholder="Course" required><br>
                <input type="text" name="year_level" placeholder="Year Level" required><br>
                <select name="hk_number" required>
                    <option value="" disabled>Select HK Number</option>
                    <option value="HK25">HK25</option>
                    <option value="HK50">HK50</option>
                    <option value="HK75">HK75</option>
                    <option value="HK100">HK100</option>
                </select>
                <input type="submit" value="Submit">
            </form>

            <!-- Display success or error messages -->
            <?php if (isset($message)): ?>
                <p class="message"><?php echo $message; ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
