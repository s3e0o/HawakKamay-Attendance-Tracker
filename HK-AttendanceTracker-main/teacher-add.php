<?php
session_start();

require 'db-connection.php';
// Initialize variables
$teacherId = $name = $department = $email = "";
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
        $_SESSION['error_message'] = "A teacher with this ID or name already exists.";
        header("Location: teacher-add.php");
        exit();
    } elseif ($result_email->num_rows > 0) {
        $_SESSION['error_message'] = "This email is already registered.";
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
        $_SESSION['success_message'] = "Teacher added successfully.";
        header("Location: teacher-list.php");
        exit();
    }

}

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Add Faculty - UPang HK Attendance Tracker</title>
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
        main {
            flex-grow: 1;
            padding: 20px;
            color: white;
            overflow-y: auto;
            height: 100%;
            transition: margin-left 0.3s ease;
        }
        main.sidebar-hidden {
            margin-left: -220px; /* When sidebar is hidden, extend content to full width */
        }
        main:not(.sidebar-hidden) {
            margin-left: 10px; /* When sidebar is visible, keep content shifted */
        }
        .title {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 10px;
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
            color: gray;
        }
        select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: white;
            color: black;
        }
        .submit-button {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            justify-self: flex-start;
            font-size: 16px;
        }
        .submit-button:hover{
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
            main {
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
    <div class="container">
        <div class="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item active"><a href="teacher-list.php">Faculty</a></div>
            <div class="nav-item"><a href="student-list.php">Scholar</a></div>
            <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <main>
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
        <h1 class="title">ADD FACULTY</h1>

            <!-- <div class="content-box">
                <div class="header">
                    <h2>ADMIN</h2>
                </div> -->
                <div class="content-box">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="teacher_id">FacultyID:</label>
                            <input type="text" id="teacher_id" name="teacher_id" required
                            placeholder="Enter Teacher/Instructor ID (e.g., 03-20211)"
                            pattern="^\d{2}-\d{4,6}$" 
                            title="Only numbers and dashes are allowed, with no spaces.">
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" required
                            placeholder="Enter Teacher/Instructor Name"
                            pattern="^[A-Za-z\s]+$" 
                            title="Please enter letters only." 
                            maxlength="30" required
                            oninput="capitalizeName(this)">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required
                                placeholder="e.g., ab.cd.up@phinmaed.com" 
                                pattern="^[a-z.]+\.up@phinmaed\.com$" 
                                title="Please enter a valid email address ending with .up@phinmaed.com and containing only lowercase letters and dots">
                        </div>
                        <div class="form-group">
                            <label for="department">Department:</label>
                            <select name="department" id="department" required>
                                <option value="CITE" <?php echo ($department == 'CITE') ? 'selected' : ''; ?>>CITE</option>
                                <option value="CELA" <?php  echo ($department == 'CELA') ? 'selected' : ''; ?>>CELA</option>
                                <option value="CAS" <?php echo ($department == 'CAS') ? 'selected' : ''; ?>>CAS</option>
                                <option value="CEA" <?php echo ($department == 'CEA') ? 'selected' : ''; ?>>CEA</option>
                                <option value="CAHS" <?php echo ($department == 'CAHS') ? 'selected' : ''; ?>>CAHS</option>
                                <option value="CCJE" <?php echo ($department == 'CCJE') ? 'selected' : ''; ?>>CCJE</option>
                            </select>
                        </div>
                        <button type="submit" name="save" class="submit-button">Add</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function capitalizeName(input) {
            // Split the input value by spaces, capitalize each word, and join them back together
            input.value = input.value
                .toLowerCase() // Convert entire string to lowercase first
                .split(' ') // Split into words
                .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize the first letter
                .join(' '); // Join words back into a string
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('main');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('sidebar-hidden');
        }
    </script>
</body>
</html>