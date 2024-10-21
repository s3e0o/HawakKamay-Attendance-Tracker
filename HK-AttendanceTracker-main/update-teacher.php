<?php
session_start();

require 'db-connection.php'; // Ensure this includes a working DB connection setup


// Initialize variables
$teacherId = $name = $department = $email = "";

// Check if an ID was provided
if (isset($_GET['id'])) {
    $teacherId = $_GET['id']; // Use a consistent variable name

    // Query to get the user data
    $stmt = $conn->prepare(
        "SELECT t.id, t.name, t.department, u.email 
         FROM teachers t 
         JOIN users u ON t.user_id = u.id 
         WHERE t.id = ?"
    );
    $stmt->bind_param("i", $teacherId);
    if (!$stmt->execute()) {
        $_SESSION['error'] = "Error executing query: " . $stmt->error;
        header("Location: teacher-list.php");
        exit();
    }
    $result = $stmt->get_result();

    // Fetch the user data
    if ($result->num_rows > 0) {
        $teacher_data = $result->fetch_assoc();
        $name = $teacher_data['name'];
        $department = $teacher_data['department'];
        $email = $teacher_data['email'];
    } else {
        $_SESSION['error'] = "No data found for this teacher.";
        header("Location: teacher-list.php");
        exit();
    }
} else {
    die("User ID not provided.");
}

// Check if a teacher is being updated
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $teacherId = $_POST['teacher_id']; // Ensure this matches the hidden field
    $name = $_POST['name'];
    $department = $_POST['department'];
    $email = $_POST['email'];

    // Check if teacher exists
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $teacherId);
    if (!$stmt->execute()) {
        $_SESSION['error_message'] = "Error executing query: " . $stmt->error;
        header("Location: teacher-list.php");
        exit();
    }
    $teacherExists = $stmt->get_result();

    if ($teacherExists->num_rows === 0) {
        $_SESSION['error_message'] = "No teacher found with this ID.";
        header("Location: teacher-list.php");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE email = ? 
         AND email != (SELECT email FROM users WHERE id = (SELECT user_id FROM teachers WHERE id = ?))"
    );
    $stmt->bind_param("si", $email, $teacherId);
    if (!$stmt->execute()) {
        $_SESSION['error_message'] = "Error executing query: " . $stmt->error;
        header("Location: teacher-list.php");
        exit();
    }
    $emailExists = $stmt->get_result();

    if ($emailExists->num_rows > 0) {
        $_SESSION['success_message'] = "This email is already registered.";
        header("Location: teacher-list.php");
        exit();
    }

    // Update email in users table
    $stmt = $conn->prepare(
        "UPDATE users SET email = ? 
         WHERE id = (SELECT user_id FROM teachers WHERE id = ?)"
    );
    $stmt->bind_param("si", $email, $teacherId);
    if (!$stmt->execute()) {
        $_SESSION['error_message'] = "Error executing query: " . $stmt->error;
        header("Location: teacher-list.php");
        exit();
    }

    // Update teacher details
    $stmt = $conn->prepare(
        "UPDATE teachers SET name = ?, department = ? WHERE id = ?"
    );
    $stmt->bind_param("ssi", $name, $department, $teacherId);
    if (!$stmt->execute()) {
        $_SESSION['error_message'] = "Error executing query: " . $stmt->error;
        header("Location: teacher-list.php");
        exit();
    }

    $_SESSION['success_message'] = "Teacher information updated successfully.";
    header("Location: teacher-list.php");
    exit();
}

// Display error message if set
// if (isset($_SESSION['error'])) {
//     echo "<script>alert('" . $_SESSION['error'] . "');</script>";
//     unset($_SESSION['error']);
// }

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Handle logout
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
    <title>UPang HK Attendance Tracker - Admin</title>
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
            /*box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); */
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
            width: 58%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9; 
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #4a5d29; 
            outline: none; 
        }
        .submit-button {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px; 
        }
        .submit-button:hover {
            background-color: #BFA93B; 
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
            display: <?php echo !empty($searchResults) ? 'block' : 'none'; ?>; 
            margin-top: 20px;
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
            width: 60%;
            padding: 8px;
            border: none;
            border-radius: 4px;
            background-color: white;
            color: black;
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
            <div class="logo"></div>
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
            <h1 class="title">EDIT TEACHER INFORMATION</h1>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . htmlspecialchars($teacherId); ?>">
                <input type="hidden" name="teacher_id" value="<?php echo htmlspecialchars($teacherId); ?>">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required
                        placeholder="e.g., ab.cd.up@phinmaed.com" 
                        pattern="^[a-z.]+\.up@phinmaed\.com$" 
                        title="Please enter a valid email address ending with .up@phinmaed.com and containing only lowercase letters and dots">
                </div>
                <div class="form-group">
                    <label for="department">Department:</label>
                    <select name="department" id="department" required>
                        <option value="CITE" <?php echo ($department == 'CITE') ? 'selected' : ''; ?>>CITE</option>
                        <option value="CELA" <?php echo ($department == 'CELA') ? 'selected' : ''; ?>>CELA</option>
                        <option value="CAS" <?php echo ($department == 'CAS') ? 'selected' : ''; ?>>CAS</option>
                        <option value="CEA" <?php echo ($department == 'CEA') ? 'selected' : ''; ?>>CEA</option>
                        <option value="CAHS" <?php echo ($department == 'CAHS') ? 'selected' : ''; ?>>CAHS</option>
                        <option value="CCJE" <?php echo ($department == 'CCJE') ? 'selected' : ''; ?>>CCJE</option>
                    </select>
                </div>
                <button type="submit" name="update" class="submit-button">Update</button>
            </form>
            <?php if ($success_message): ?>
                <div style="color: yellow;"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div style="color: red;"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </main>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('main');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('sidebar-hidden');
        }
    </script>
</body>
</html>