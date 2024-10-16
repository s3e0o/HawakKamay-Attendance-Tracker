<?php
session_start();
include 'db-connection.php'; // Include the connection file

// PHP logout logic
if (isset($_GET['logout'])) {
    // Destroy the session
    session_destroy();
    // Redirect to the login page
    header("Location: multi-login.php");
    exit(); // Exit after header redirection
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: multi-login.php");
    exit();
}


$host = 'localhost'; // Change if needed
$username = 'root';  // Change to your database username
$password = '';      // Change to your database password
$dbname = 'hk-management'; // Your database name

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $profilePic = $_FILES['profilePic']['name'] ? $_FILES['profilePic']['name'] : $teacher['profile_pic'];

    // Handle image upload
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == 0) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["profilePic"]["name"]);
        move_uploaded_file($_FILES["profilePic"]["tmp_name"], $targetFile);
    }

    // Update teacher data
    $updateSql = "UPDATE teachers SET name = ?, email = ?, profile_pic = ?, updated_at = NOW() WHERE teacher_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssi", $fullName, $email, $profilePic, $teacher);
    $updateStmt->execute();

    // Redirect after successful update
    header("Location: teacher-profile.php");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher's Profile - UPang HK Attendance Tracker</title>
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
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
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
            <div class="nav-item"><a href="instructor-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="schedule-assign.php">Student Assign</a></div>
            <div class="nav-item"><a href="teacher-profile.php">Profile</a></div>   
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <div class="main-content" id="main-content">
            <div>
                <h1>Instructor Profile</h1>
            </div>
            <div class="profile-header">
                <div class="profile-img" id="profilePreview">IMG</div>
            </div>
            
            <h2>About</h2>
            <form id="teacherProfileForm" enctype="multipart/form-data" method="POST" action="teacher-profile.php">
    <div class="input-group">
        <label for="fullName">Full Name:</label>
        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
    </div>
    <div class="input-group">
        <label for="teacherId">Teacher ID:</label>
        <input type="text" id="teacherId" name="teacherId" value="<?php echo $teacher['teacher_Id']; ?>" readonly>
    </div>
    <div class="input-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
    </div>
    <div class="input-group">
        <label for="profileImage">Profile Picture:</label>
        <input type="file" id="profileImage" name="profileImage" accept="image/*">
    </div>
    <button type="submit" class="save-button">Save</button>
</form>

        </div>
    </div>

    <script>
        document.getElementById('profileImage').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').innerHTML = `<img src="${e.target.result}" alt="Profile Image" style="width:100%;height:100%;border-radius:50%;">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
