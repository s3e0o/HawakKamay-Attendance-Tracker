<?php
session_start();

require 'db-connection.php';

$conn = new mysqli("localhost", "root", "", "hk-management");

// Handle search
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// Fetch teachers from the database with search functionality
$sql = "SELECT t.id, t.teacher_id, t.name, t.department, u.email 
        FROM teachers t
        JOIN users u ON t.user_id = u.id
        WHERE t.name LIKE ? OR t.teacher_id LIKE ? OR t.department LIKE ? OR u.email LIKE ?
        ORDER BY t.id ASC";

$stmt = $conn->prepare($sql);
$searchWildcard = "%$searchTerm%";
$stmt->bind_param("ssss", $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard);
$stmt->execute();
$result = $stmt->get_result();
$teachers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Prepare to get the user_id associated with the teacher
    $getUserIdStmt = $conn->prepare("SELECT user_id FROM teachers WHERE id = ?");
    $getUserIdStmt->bind_param("i", $delete_id);
    $getUserIdStmt->execute();
    $userIdResult = $getUserIdStmt->get_result();

    if ($userIdResult->num_rows > 0) {
        $userIdRow = $userIdResult->fetch_assoc();
        $user_id = $userIdRow['user_id'];

        // Prepare delete statement for the teacher
        $delete_teacher_stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
        $delete_teacher_stmt->bind_param("i", $delete_id);
        $teacherDeleted = $delete_teacher_stmt->execute();

        // Prepare delete statement for the user
        $delete_user_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_user_stmt->bind_param("i", $user_id);
        $userDeleted = $delete_user_stmt->execute();

        if ($teacherDeleted && $userDeleted) {
            $_SESSION['success_message'] = "Teacher and associated user deleted successfully.";
            header("Location: teacher-list.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to delete teacher or associated user.";
            header("Location: teacher-list.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Teacher not found.";
        header("Location: teacher-list.php");
        exit();
    }
}

// Check for success/error message
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

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
    <title>Admin Faculty List - UPang HK Attendance Tracker</title>
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
            background-attachment: fixed;
            background-repeat: no-repeat;
            overflow: hidden;
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
            transition: transform 0.3s ease, opacity 0.3s ease;
            position: relative;
            z-index: 2; 
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
        .content-box {
            border-radius: 10px;
            padding: 20px;
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
        input[type="text"] {
            width: 50%;
            padding: 10px;
        }
        input[type="submit"] {
            padding: 10px;
            border-radius: 0 0 0 0;
        }
        button[type="button"]{
            padding: 10px;
            border-radius: 0 0 20px;
        }
        .add-new {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-new:hover {
            background-color: #8b6914;
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
        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        th {
            background-color: #3e4d22;
        }
        .actions {
            display: flex;
            gap: 10px;
            cursor: pointer;
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
        .edit-icon:hover {
            color: #88bffd;
            transform: scale(1.2);
        }
        .delete-icon {
            color: #ff0000;
        }
        .delete-icon:hover {
            color: #ff8077;
            transform: scale(1.2);
        }
        .search-results-container {
            display: <?php echo !empty($searchResults) ? 'block' : 'none'; ?>; 
            margin-top: 20px;
        }
        .btn-clear{
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-clear:hover{
            background-color: #ad0c00;
        }
        input[type="submit"].update-button {
            border-radius: 25px;
            cursor: pointer;
        }
        .search-bar {
            display: flex;
            max-width: 100%;
            margin: 5 auto;
            height: 45px;
            background-color: #ffffff;
            border-radius: 25px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .search-bar input[type="text"] {
            flex-grow: 1;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            outline: none;
        }
            .search-bar input[type="submit"] {
                background-color: #338a41;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .search-bar input[type="submit"]:hover {
            background-color: #1c551e;
            }

            .search-bar input[type="text"]::placeholder {
            color: #999;
            }
            .search-bar input[type="text"]:focus {
            box-shadow: inset 0 0 5px rgba(81, 203, 238, 0.5);
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
            @media (max-width: 575px) {
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
        <div class="main-content">
            <!-- <div class="content-box"> -->
            <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
            <h1 class="title">FACULTIES</h1>

                <div class="header">
                    <button class="add-new" onclick="location.href='teacher-add.php';">Add New</button>
                </div>
                <form class="search-bar" method="post" action="teacher-list.php">
                    <input type="text" name="search" placeholder="Search by Name, ID, Department, or Email" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <input type="submit" value="Search">
                    <button class="btn-clear" type="button" onclick="clearSearch()">Clear</button>
                </form>
                
                <?php if (empty($teachers)): ?>
                    <div class="no-results">No results found for your search.</div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div style="color: yellow;"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div style="color: red;"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <h3 class="section-title">RECENT USER ACTIVITIES</h3>
                <table>
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                            <th>FacultyID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <!-- <td><?php echo htmlspecialchars($teacher['id']); ?></td> -->
                                <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td class="actions">
                                    <!-- <form action="update-teacher.php" method="GET">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($teacher['id']); ?>">
                                        <input class="update-button" type="submit" value="Update">
                                    </form> -->

                                    <button class="edit" onclick="location.href='update-teacher.php?id=<?php echo urlencode($teacher['id']); ?>'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="edit-icon">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    <button class="delete" onclick="confirmDelete('<?php echo $teacher['id']; ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="delete-icon">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function clearSearch() {
            // Clear the search input and reload the page
            document.querySelector("input[name='search']").value = ""; 
            window.location.href = "teacher-list.php"; 
        }

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this teacher?')) {
                // Redirect to the same page with the delete_id parameter
                window.location.href = '?delete_id=' + encodeURIComponent(id);
            }
        }
    </script>
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