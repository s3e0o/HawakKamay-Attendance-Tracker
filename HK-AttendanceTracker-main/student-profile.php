<?php
session_start();

require 'db-connection.php'; 

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
    $stmt->bind_param('s', $username); 
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the student exists
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_id = $student['student_id']; 
        $name = $student['name'];
        $email = $student['email'];
        $hashed_password = $student['password']; 
    } else {
        header("Location: multi-login.php"); 
        exit();
    }
} else {
    header("Location: multi-login.php"); 
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
        $_SESSION['success_message'] = "Profile updated successfully.";
        header("Location: student-profile.php"); 
        exit();
    } else {
        // echo "Error updating student: " . $stmt->error;
        $_SESSION['error_message'] = "Error updating student";
        header("Location: student-profile.php");
        exit();
    }
}

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
            $stmt->bind_param("ss", $hashed_new_password, $username); 

            if ($stmt->execute()) {
                // echo "Password changed successfully.";
                $_SESSION['success_message'] = "Password changed successfully.";
                header("Location: student-profile.php");
                exit();
            } else {
                // echo "Error updating password: " . $stmt->error;
                $_SESSION['error_message'] = "Error updating password.";
                header("Location: student-profile.php");
                exit();
            }
        } else {
            // echo "New passwords do not match.";
            $_SESSION['error_message'] = "New passwords do not match.";
            header("Location: student-profile.php");
            exit();
        }
    } else {
        // echo "Current password is incorrect.";
        $_SESSION['error_message'] = "Current password is incorrect.";
        header("Location: student-profile.php");
        exit();
    
    }
}

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

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
        .toggle-btn {
            background-color: #A98D00;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 15px;
            margin-right: 20px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: white;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: white;
            color: black;
            margin-top: 5px;
        }   
        form {
            display: grid;
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px;
            margin-bottom: 20px;
        }
        .button-container {
            grid-column: span 3;  
            display: flex;
            justify-content: flex-start; 
        }
        .save-button {
            width: 50%;
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 0px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
        .save-button:hover {
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
            <h2>UPang HK <br>Attendance Tracker</h2>
            <div class="nav-item"><a href="student-db.php">Dashboard</a></div>
            <div class="nav-item active"><a href="student-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <div class="main-content">
            <h1 class="title">Student Profile</h1>
            <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
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

            <h1 class="title">Change Password</h1>
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
            <?php if ($success_message): ?>
                <div style="color: yellow;"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div style="color: red;"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('sidebar-hidden');
        }
    </script>
</body>
</html>
