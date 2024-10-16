<?php
session_start();

require 'db-connection.php';

$username = '';
$name = '';
$email = '';
$username_error = '';

// Fetch student data based on session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query to fetch student details based on username
    $sql = "SELECT s.student_id, s.name, u.email 
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            WHERE u.username = ?"; // Change to use username

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username); // Use username for binding
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the student exists
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_id = $student['student_id']; // Keep student_id for updates
        $name = $student['name'];
        $email = $student['email'];
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

    // Update the students table
    $update_student_sql = "UPDATE students SET name = ?, email = ? WHERE student_id = ?";
    $stmt = $conn->prepare($update_student_sql);
    $stmt->bind_param("ssi", $name, $email, $student_id);

    if ($stmt->execute()) {
        header("Location: student-profile.php");
        exit();
    } else {
        echo "Error updating student: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student's Profile - UPang HK Attendance Tracker</title>
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
            <div class="nav-item"><a href="student-db.php">Schedule</a></div>
            <div class="nav-item"><a href="student-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <div class="main-content" id="main-content">
            <div>
                <h1>Student Profile</h1>
            </div>
            <!-- <div class="profile-header">
                <div class="profile-img" id="profilePreview">IMG</div>
            </div> -->
            
            <h2>About</h2>
            <form id="studentProfileForm" enctype="multipart/form-data" method="POST" action="student-profile.php">
                <div class="input-group">
                    <label for="student_id">Student ID:</label>
                    <input type="text" id="student_id" name="student_id" value="<?php echo $student['student_id']; ?>" readonly>
                </div>
                <div class="input-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>
                <!-- <div class="input-group">
                    <label for="phone">Phone No.:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($student['address']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="profileImage">Profile Picture:</label>
                    <input type="file" id="profileImage" name="profileImage" accept="image/*">
                </div> -->
                <button type="submit" class="save-button">Save</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('profileImage').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').style.backgroundImage = `url(${e.target.result})`;
                document.getElementById('profilePreview').style.backgroundSize = 'cover';
                document.getElementById('profilePreview').style.backgroundPosition = 'center';
                document.getElementById('profilePreview').innerText = ''; // Clear 'IMG' text
            };
            if (file) {
                reader.readAsDataURL(file);
            }
        });

        // Sidebar toggle functionality
        // const sidebar = document.getElementById("sidebar");
        // const mainContent = document.getElementById("main-content");
        // let sidebarVisible = true;

        // function toggleSidebar() {
        //     sidebar.classList.toggle("hidden");
        //     sidebarVisible = !sidebarVisible;
        //     mainContent.style.marginLeft = sidebarVisible ? "200px" : "0";
        // }

        // // Bind toggle functionality to the toggle button
        // const toggleButton = document.createElement("button");
        // toggleButton.innerText = "Toggle Menu";
        // toggleButton.classList.add("toggle-btn");
        // toggleButton.onclick = toggleSidebar;
        // document.body.insertBefore(toggleButton, document.body.firstChild);
    </script>
</body>
</html>
