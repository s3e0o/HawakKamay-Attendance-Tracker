<?php
session_start();
require 'db-connection.php'; 

$username = '';
$name = '';
$email = '';
$username_error = '';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch admin data based on session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $sql = "SELECT a.admin_id, a.name, u.email, u.password
            FROM admins a 
            JOIN users u ON a.user_id = u.id 
            WHERE u.username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    } else {
        header("Location: multi-login.php");
        exit();
    }
} else {
    header("Location: multi-login.php");
    exit();
}

// Save profile changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullName'])) {
    $name = $_POST['fullName'];
    $email = $_POST['email'];

    $update_admin_sql = "UPDATE admins SET name = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($update_admin_sql);
    $stmt->bind_param("si", $name, $admin['admin_id']);

    if ($stmt->execute()) {
        $update_email_sql = "UPDATE users SET email = ? WHERE id = (SELECT user_id FROM admins WHERE admin_id = ?)";
        $stmt_email = $conn->prepare($update_email_sql);
        $stmt_email->bind_param("si", $email, $admin['admin_id']);

        if ($stmt_email->execute()) {
            $_SESSION['success_message'] = "Profile successfully saved!";
        } else {
            $_SESSION['error_message'] = "Error updating email.";
        }
    } else {
        $_SESSION['error_message'] = "Error updating admin.";
    }
    header("Location: admin-profile.php");
    exit();
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $admin['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update_password_sql = "UPDATE users SET password = ? WHERE id = (SELECT user_id FROM admins WHERE admin_id = ?)";
            $stmt = $conn->prepare($update_password_sql);
            $stmt->bind_param("si", $hashed_new_password, $admin['admin_id']);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Password changed successfully.";
            } else {
                $_SESSION['error_message'] = "Error updating password.";
            }
        } else {
            $_SESSION['error_message'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['error_message'] = "Current password is incorrect.";
    }
    header("Location: admin-profile.php");
    exit();
}

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// PHP logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: multi-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - UPang HK Attendance Tracker</title>
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
        .input-group {
            margin-bottom: 10px;
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
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            border-radius: 5px;
            margin-bottom: 10px;
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
</head>
<body>
    <div class="container" id="container">
        <div class="sidebar" id="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Faculty</a></div>
            <div class="nav-item"><a href="student-list.php">Scholar</a></div>
            <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item active"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <div class="main-content" id="main-content">
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
            <h1 class="title">ADMIN PROFILE</h1>
            <form id="adminProfileForm" method="POST">
                <div class="input-group">
                    <label for="adminId">Admin ID:</label>
                    <input type="text" id="adminId" value="<?php echo $admin['admin_id']; ?>" readonly>
                </div>
                <div class="input-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
                <div class="button-container">
                    <button type="submit" class="save-button">Save</button>
                </div>
            </form>

            <h2 class="title">CHANGE PASSWORD</h2>
            <form method="POST">
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
                <div class="button-container">
                    <button type="submit" class="save-button">Change Password</button>
                </div>
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
