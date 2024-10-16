<?php
session_start();

require 'db-connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
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

    // Check for duplicates in the teachers table
    $check_teacher_sql = "SELECT * FROM teachers WHERE teacher_id = ? OR name = ?";
    $stmt_check_teacher = $conn->prepare($check_teacher_sql);
    $stmt_check_teacher->bind_param("ss", $teacherId, $name);
    $stmt_check_teacher->execute();
    $result_teacher = $stmt_check_teacher->get_result();

    // Check for duplicates in the users table
    $check_email_sql = "SELECT * FROM users WHERE email = ?";
    $stmt_check_email = $conn->prepare($check_email_sql);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_email = $stmt_check_email->get_result();

    // If duplicates are found, set a session message and redirect back
    if ($result_teacher->num_rows > 0) {
        $_SESSION['error'] = "A teacher with this ID or name already exists.";
        header("Location: teacher-add.php");
        exit();
    } elseif ($result_email->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered.";
        header("Location: teacher-add.php");
        exit();
    } else {
        // Hash the teacher ID to use as the default password
        $hashed_password = password_hash($teacherId, PASSWORD_DEFAULT);

        // First, insert the new user into the 'users' table
        $insert_user_sql = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, 'teacher')";
        $stmt_user = $conn->prepare($insert_user_sql);
        $stmt_user->bind_param("sss", $email, $teacherId, $hashed_password);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id; // Get the ID of the inserted user

        // Insert the teacher data into the 'teachers' table
        $insert_teacher_sql = "INSERT INTO teachers (teacher_id, name, department, user_id) VALUES (?, ?, ?, ?)";
        $stmt_teacher = $conn->prepare($insert_teacher_sql);
        $stmt_teacher->bind_param("sssi", $teacherId, $name, $department, $user_id);
        $stmt_teacher->execute();

        // Redirect back to the teacher list page after saving
        header("Location: teacher-list.php");
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
            border-radius: 10px;
            padding: 20px;
            width: 80%;
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
            color: #cccccc;
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
                <div class="content-box">
                    <h2>ADD TEACHER/COORDINATOR</h2>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="teacher_id">TeacherID:</label>
                            <input type="text" id="teacher_id" name="teacher_id" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="department">Department:</label>
                            <input type="text" id="department" name="department" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <button type="submit" name="save" class="submit-button">Add</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>