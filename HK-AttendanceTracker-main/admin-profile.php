<?php
session_start();

require 'db-connection.php'; 

$username = '';
$name = '';
$email = '';
$username_error = '';

// Fetch admin data based on session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query to fetch admin details based on username
    $sql = "SELECT a.admin_id, a.name, u.email 
            FROM admins a 
            JOIN users u ON a.user_id = u.id 
            WHERE u.username = ?"; // Change to use username

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username); // Use username for binding
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the admin exists
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_id = $admin['admin_id']; // Keep admin_id for updates
        $name = $admin['name'];
        $email = $admin['email'];
    } else {
        header("Location: multi-login.php");
        exit();
    }
} else {
    header("Location: multi-login.php");
    exit();
}

// Save changes when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = $_POST['fullName'];
    $email = $_POST['email'];

    // Update the admins table
    $update_admin_sql = "UPDATE admins SET name = ?, email = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($update_admin_sql);
    $stmt->bind_param("ssi", $name, $email, $admin_id);

    if ($stmt->execute()) {
        header("Location: admin-profile.php");
        exit();
    } else {
        echo "Error updating admin: " . $stmt->error;
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
    <title>Admin's Profile - UPang HK Attendance Tracker</title>
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
            margin-top: auto; 
            padding: 10px; 
            text-align: center; 
            color: white; 
            background-color: #f44336; 
            border: none; 
            cursor: pointer; 
            transition: background-color 0.3s ease; 
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
        .title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
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
            <div>
                <h1 class="title">ADMIN PROFILE</h1>
            </div>

            <h2>About</h2>
            <form id="adminProfileForm" method="POST" action="admin-profile.php">
                <div class="input-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="adminId">Admin ID:</label>
                    <input type="text" id="adminId" name="adminId" value="<?php echo $admin['admin_id']; ?>" readonly>
                </div>
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
                <button type="submit" class="save-button">Save</button>
            </form>
        </div>
    </div>
</body>
</html>
