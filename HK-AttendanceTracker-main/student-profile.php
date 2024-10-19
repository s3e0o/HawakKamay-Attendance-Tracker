<?php
session_start();

require 'db-connection.php'; // Include the connection file

$name = '';
$email = '';
$student_id = '';
$username = '';

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query to fetch student details based on username
    $sql = "SELECT s.student_id, s.name, u.email, u.password 
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            WHERE u.username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username); // Bind username
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the student exists
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_id = $student['student_id']; // Store student ID for updates
        $name = $student['name'];
        $email = $student['email'];
        $hashed_password = $student['password']; // Store the hashed password for verification
    } else {
        header("Location: multi-login.php"); // Redirect if student not found
        exit();
    }
} else {
    header("Location: multi-login.php"); // Redirect if not logged in
    exit();
}

// Save changes when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = $_POST['fullName'];
    $email = $_POST['email'];

    // Update the students table
    $update_sql = "UPDATE students SET name = ? WHERE student_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $name, $student_id);

    if ($stmt->execute()) {
        header("Location: student-profile.php"); // Reload the profile page
        exit();
    } else {
        echo "Error updating student: " . $stmt->error;
    }
}

// Handle password change
// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify the current password
    if (password_verify($current_password, $hashed_password)) {
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_password_sql = "UPDATE users SET password = ? WHERE username = ?";
            $stmt = $conn->prepare($update_password_sql);
            $stmt->bind_param("ss", $hashed_new_password, $username); // Assuming you want to update based on the username

            if ($stmt->execute()) {
                echo "Password changed successfully.";
            } else {
                echo "Error updating password: " . $stmt->error;
            }
        } else {
            echo "New passwords do not match.";
        }
    } else {
        echo "Current password is incorrect.";
    }
}


// Logout logic
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: multi-login.php"); // Redirect to login page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - UPang HK Attendance Tracker</title>
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
            transition: margin-left .5s; 
        }
        .container {
            display: flex;
            height: 100%;
            transition: margin-left .5s; 
        }
        .sidebar {
            width: 200px;
            height: auto;
            background-color: #A98D00;
            color: white;
            padding: 20px;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 2;
        }
        .sidebar.hidden {
            transform: translateX(-100%);
            width: 0;
            padding: 0;
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
            color: white;
            background-color: #ff4c4c;
            text-align: center;
            padding: 10px;
            margin-top: 10px;
            display: block;
            border-radius: 10px;
        }
        .sidebar .logout-btn:hover {
            background-color: #ff3333;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            color: white;
            margin-left: 0px; 
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
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            background-color: #b8860b;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            margin-left: 20px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: white;
        }
        .input-group input {
            width: 70%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: white;
            color: black;
            margin-top: 5px;
        }
        .save-button {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            border-radius: 5px;
        }
        .save-button:hover {
            background-color: #8b6914;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br>Attendance Tracker</h2>
            <div class="nav-item"><a href="student-db.php">Schedule</a></div>
            <div class="nav-item active"><a href="student-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <div class="main-content">
            <h1>Student Profile</h1>
            <form method="POST" action="student-profile.php">
                <div class="input-group">
                    <label for="studentId">Student ID:</label>
                    <input type="text" id="studentId" name="studentId" value="<?php echo $student_id; ?>" readonly>
                </div>

                <div class="input-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($name); ?>" required
                    pattern="[A-Za-z\s]+" 
                    title="Only letters and spaces are allowed.">
                </div>
                
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>

                <button type="submit" name="save" class="save-button">Save</button>
            </form>

            <h2>Change Password</h2>
            <form method="POST" action="student-profile.php">
                <div class="input-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="input-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" name="change_password" class="save-button">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
