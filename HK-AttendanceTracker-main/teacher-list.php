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
        ORDER BY t.id ASC"; // Order by the latest updated record

$stmt = $conn->prepare($sql);
$searchWildcard = "%$searchTerm%"; // Adding wildcards for partial matching
$stmt->bind_param("ssss", $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard);
$stmt->execute();
$result = $stmt->get_result();
$teachers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
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
    <title>UPang HK Attendance Tracker - Admin</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
            background-image: url('hkat-upang.jpg'); /* Path to the uploaded image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: margin-left .5s; /* Animation for sidebar toggle */
        }
        .container {
            display: flex;
            height: 100%;
            transition: margin-left .5s; /* Animation for container */
        }
        .sidebar {
            width: 200px;
            background-color: #A98D00;
            color: white;
            padding: 20px;
            transition: transform 0.3s ease; /* Animation for sidebar */
            position: relative;
            z-index: 2; /* Ensure sidebar is above main content */
        }
        .sidebar.hidden {
            transform: translateX(-100%); /* Move sidebar out of view */
            width: 0; /* Remove width when hidden */
            padding: 0; /* Remove padding when hidden */
            opacity: 0; /* Make sidebar invisible */
        }
        .logo {
            width: 150px;  /* Adjust size */
            height: 150px; /* Ensure it's square */
            background-image: url('hk_logo.png'); /* Background image path */
            background-size: cover;  /* Makes sure the image covers the entire div */
            background-position: center;
            border-radius: 50%; /* Make it a circle */
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
        main {
            flex-grow: 1;
            /* background-color: #556b2f; */
            padding: 20px;
            color: white;
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
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
            display: <?php echo !empty($searchResults) ? 'block' : 'none'; ?>; /* Show if there are results */
            margin-top: 20px;
        }
        .search-results-container {
            display: <?php echo !empty($searchResults) ? 'block' : 'none'; ?>; /* Show if there are results */
            margin-top: 20px;
        }
        .search-results-container {
            display: <?php echo !empty($searchResults) ? 'block' : 'none'; ?>; /* Show if there are results */
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

            /* Submit button styles */
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


            /* Responsive design */
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

            /* Placeholder text color */
            .search-bar input[type="text"]::placeholder {
            color: #999;
            }

            /* Focus styles */
            .search-bar input[type="text"]:focus {
            box-shadow: inset 0 0 5px rgba(81, 203, 238, 0.5);
            }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item active"><a href="teacher-list.php">Instructor</a></div>
            <div class="nav-item"><a href="student-list.php">Student</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>
        </div>
        <main>
            <div class="content-box">
                <div class="header">
                    <h1>TEACHERS/COORDINATORS</h1>
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
                <h2 class="section-title">RECENT USER ACTIVITIES</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>TeacherID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['id']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td>
                                    <form action="update-teacher.php" method="GET">
                                        <input type="hidden" name="userId" value="<?php echo htmlspecialchars($teacher['id']); ?>">
                                        <input class="update-button" type="submit" value="Update">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function clearSearch() {
            // Clear the search input and reload the page
            document.querySelector("input[name='search']").value = ""; // Clear the input field
            window.location.href = "teacher-list.php"; // Redirect to the same page
        }
    </script>
</body>
</html>