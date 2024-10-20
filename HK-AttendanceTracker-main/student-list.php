<?php
session_start();
require 'db-connection.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
// Database connection parameters
$host = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "hk-management"; 

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$searchResults = [];
$students = [];

// Clear search results
if (isset($_GET['clearSearch'])) {
    unset($_SESSION['searchResults']);
    header("Location: student-list.php");
    exit();
}

// Handle search request
if (isset($_GET['searchQuery'])) {
    $searchQuery = $conn->real_escape_string($_GET['searchQuery']);
    
    // Prepare SQL query based on search
    $sql = "SELECT user_id, name, student_id, email, course, level, hk_status, total_hours, status 
            FROM students 
            WHERE name LIKE '%$searchQuery%' OR student_id LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%' OR course LIKE '%$searchQuery%' OR hk_status LIKE '%$searchQuery%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
        $_SESSION['searchResults'] = $searchResults;
    } else {
        $_SESSION['searchResults'] = [];
    }
} else {
    // Fetch recent users if there's no search query
    $sql = "SELECT user_id, name, student_id, email, course, level, hk_status, total_hours, status 
            FROM students 
            ORDER BY user_id ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Prepare delete statement
    $delete_stmt = $conn->prepare("DELETE FROM students WHERE user_id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        echo "<script>alert('Student deleted successfully.'); window.location.href = 'student-list.php';</script>";
    } else {
        echo "<script>alert('Failed to delete student.'); window.location.href = 'student-list.php';</script>";
    }
    
    $delete_stmt->close();
    exit();
}

// Export functionality
/*if (isset($_POST['export'])) {
    require 'vendor/autoload.php'; // Load PHPSpreadsheet

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Students');

    // Set header
    $sheet->setCellValue('A1', 'User ID');
    $sheet->setCellValue('B1', 'Name');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Course');
    $sheet->setCellValue('E1', 'Level');
    $sheet->setCellValue('F1', 'HK Status');
    $sheet->setCellValue('G1', 'Total Hours');
    $sheet->setCellValue('H1', 'Status');

    // Fetch data for export
    $data = isset($_SESSION['searchResults']) ? $_SESSION['searchResults'] : $students;
    $rowNumber = 2;

    foreach ($data as $row) {
        $sheet->setCellValue('A' . $rowNumber, $row['user_id']);
        $sheet->setCellValue('B' . $rowNumber, $row['name']);
        $sheet->setCellValue('C' . $rowNumber, $row['email']);
        $sheet->setCellValue('D' . $rowNumber, $row['course']);
        $sheet->setCellValue('E' . $rowNumber, $row['level']);
        $sheet->setCellValue('F' . $rowNumber, $row['hk_status']);
        $sheet->setCellValue('G' . $rowNumber, $row['total_hours']);
        $sheet->setCellValue('H' . $rowNumber, $row['status']);
        $rowNumber++;
    }

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="students.xlsx"');
    $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit();
}*/


if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: multi-login.php");
    exit();
}

$conn->close();
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
            /*border-radius: 25px;*/
        }
        input[type="submit"] {
            padding: 10px;
            border-radius: 0 0 0 0;
        }
        /* button[type="button"]{
            padding: 10px;
            border-radius: 0 0 20px;
        } */
        .actions {
            display: flex;
            gap: 10px;
            cursor: pointer;
        }
        .actions button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 0 20% 0;
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
        .export{
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
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
            table-layout: fixed;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #6b8e23;
        }
        th {
            background-color: #3e4d22;
        }
        
        th:nth-child(1) { width: 5%; }  /* Adjust these widths as needed */
        th:nth-child(2) { width: 10%; }
        th:nth-child(3) { width: 20%; }
        th:nth-child(4) { width: 20%; }
        th:nth-child(5) { width: 10%; }
        th:nth-child(6) { width: 10%; }
        th:nth-child(7) { width: 10%; }
        th:nth-child(8) { width: 10%; }
        th:nth-child(9) { width: 10%; }
        th:nth-child(10) { width: 10%; }
        th:nth-child(11) { width: 10%; }
        th:nth-child(12) { width: 10%; }
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
            background-color: #4a5d29;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-bar input[type="submit"]:hover {
        background-color: #45a049;
        }

        @media (max-width: 600px) {
        .search-bar {
            flex-direction: column;
            border-radius: 15px;
        }

        .search-bar input[type="text"] {
            border-bottom: 1px solid #e0e0e0;
            border-radius: 15px 15px 0 0;
        }

        .search-bar input[type="submit"] {
            border-radius: 0 0 15px 15px;
        }
        }

        .search-bar input[type="text"]::placeholder {
        color: #999;
        }

        .search-bar input[type="text"]:focus {
        box-shadow: inset 0 0 5px rgba(81, 203, 238, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Faculty</a></div>
            <div class="nav-item active"><a href="student-list.php">Scholar</a></div>
            <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>  
        </div>
        <div class="main-content">
            <!-- <div class="content-box"> -->
            <h1 class="title">STUDENTS</h1>

                <div class="header">
                    <button class="add-new" onclick="location.href='student-add.php';">Add New</button>
                </div>

                <!-- Search Bar -->
                <form class="search-bar" action="student-list.php" method="GET">
                    <input type="text" name="searchQuery" placeholder="Search by Name, Email, Course, or HK Percent" required>
                    <input type="submit" value="Search">
                    <button class="btn-clear" type="button" onclick="clearSearch()">Clear</button>
                </form>

                <?php if (!empty($students)): ?>
                    <h2 class="section-title">RECENT USER ACTIVITIES</h2>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Student No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>HK Status</th>
                            <th>Required Hours</th>
                            <th>Total Hours Rendered</th>
                            <th>Total Hours Remaining</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($students as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["user_id"]) ?></td>
                                <td><?= htmlspecialchars($row["student_id"]) ?></td>
                                <td><?= htmlspecialchars($row["name"]) ?></td>
                                <td><?= htmlspecialchars($row["email"]) ?></td>
                                <td><?= htmlspecialchars($row["course"]) ?></td>
                                <td><?= htmlspecialchars($row["level"]) ?></td>
                                <td><?= htmlspecialchars($row["hk_status"]) ?></td>
                                <td><?= htmlspecialchars($row["total_hours"]) ?> hours</td>
                                <td>10</td>
                                <td>80</td>
                                <td><?= htmlspecialchars($row["status"]) ?></td>
                                <td class="actions">
                                    <!-- <form action="update-student.php" method="GET">
                                        <input type="hidden" name="userId" value="<?= htmlspecialchars($row['user_id']) ?>">
                                        <input class="update-button" type="submit" value="Update">
                                    </form> -->
                                    <button class="edit" onclick="location.href='update-student.php?user_id=<?php echo urlencode($row['user_id']); ?>'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="edit-icon">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    <button class="delete" onclick="confirmDelete('<?= $row['user_id'] ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="delete-icon">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                <?php elseif (isset($_SESSION['searchResults']) && !empty($_SESSION['searchResults'])): ?>
                    <h2 class="section-title">SEARCH RESULTS</h2>
                    <table>
                        <tr>
                        <th>Id</th>
                            <th>Student No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>HK Status</th>
                            <th>Required Hours</th>
                            <th>Total Hours Rendered</th>
                            <th>Total Hours Remaining</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($_SESSION['searchResults'] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["user_id"]) ?></td>
                                <td><?= htmlspecialchars($row["student_id"]) ?></td>
                                <td><?= htmlspecialchars($row["name"]) ?></td>
                                <td><?= htmlspecialchars($row["email"]) ?></td>
                                <td><?= htmlspecialchars($row["course"]) ?></td>
                                <td><?= htmlspecialchars($row["level"]) ?></td>
                                <td><?= htmlspecialchars($row["hk_status"]) ?></td>
                                <td><?= htmlspecialchars($row["total_hours"]) ?> hours</td>
                                <td>10</td>
                                <td>80</td>
                                <td><?= htmlspecialchars($row["status"]) ?></td>
                                <td class="actions">
                                    <button class="edit" onclick="location.href='update-student.php?user_id=<?= urlencode($row['user_id']) ?>'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="edit-icon">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="delete" onclick="confirmDelete('<?= $row['user_id'] ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="delete-icon">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                <?php else: ?>
                    <div class="no-results">No students found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function clearSearch() {
            document.querySelector("input[name='searchQuery']").value = "";
            window.location.href = "student-list.php";
        }

        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this student?')) {
                window.location.href = '?delete_id=' + encodeURIComponent(userId);
            }
        }
    </script>
</body>
</html>
