<?php
session_start();

include 'session-check.php';
require 'db-connection.php';

$host = "localhost"; // Define $host
$username = "root"; // Define $username
$password = ""; // Define $password
$dbname = "hk-management"; // Define $dbname

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students from the database
$sql = "SELECT s.user_id, s.student_id, s.name, s.course, s.level, s.hk_status, u.email 
        FROM students s
        JOIN users u ON s.user_id = u.id
        ORDER BY s.user_id DESC"; // Order by the latest updated record

$result = $conn->query($sql);
$students = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Initialize a variable for storing search results
$searchResults = [];

// Handle search request
if (isset($_GET['searchQuery'])) {
    $searchQuery = $conn->real_escape_string($_GET['searchQuery']);
    
    // Prepare SQL query based on search
    $sql = "SELECT user_id, name, email, course, level, hk_status, total_hours, status FROM students WHERE name LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%'";
    $result = $conn->query($sql);

    // Check if there are records in the result
    if ($result->num_rows > 0) {
        // Store search results in an array
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
        $_SESSION['searchResults'] = $searchResults; // Store results in session
    } else {
        $_SESSION['searchResults'] = []; // Clear session if no results
    }
} else {
    // Check if there are existing search results in the session
    $searchResults = isset($_SESSION['searchResults']) ? $_SESSION['searchResults'] : [];
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: multi-login.php"); // Redirect to login page or your desired page
    exit();
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPang HK Attendance Tracker - Admin</title>
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
            background-color: #4a5d29;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Instructor</a></div>
            <div class="nav-item active"><a href="student-list.php">Student</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>
        </div>
        <main>
            <div class="content-box">
                <div class="header">
                    <h2>STUDENTS</h2>
                    <button class="add-new" onclick="location.href='student-add.php';">Add New</button>
                </div>
                <!-- Search Bar -->
            <form class="search-bar" action="student-list.php" method="GET" onsubmit="showResults()">
                <input type="text" name="searchQuery" placeholder="Search by name or email" required>
                <input type="submit" value="Search">
            </form>

            <!-- Display Search Results -->
            <div class="search-results-container" id="searchResults">
                <button class="close-results" onclick="hideResults()">X</button>
                <?php if (!empty($searchResults)): ?>
    <h2 class="section-title">SEARCH RESULTS</h2>
    <table>
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Year Level</th>
            <th>HK Number & Total Hours</th> <!-- Updated header -->
            <th>Status</th>
            <th>Action</th> <!-- Column for Update Button -->
        </tr>
        <?php foreach ($searchResults as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["user_id"]); ?></td>
                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                <td><?php echo htmlspecialchars($row["course"]); ?></td>
                <td><?php echo htmlspecialchars($row["level"]); ?></td>
                <td><?php echo htmlspecialchars($row["status"]) . " (" . htmlspecialchars($row["total_hours"]) . " hours)"; ?></td> <!-- Combined HK Number and Total Hours -->
                <td><?php echo htmlspecialchars($row["status"]); ?></td>
                <td>
                    <form action="update-student.php" method="GET">
                        <input type="hidden" name="userId" value="<?php echo htmlspecialchars($row['user_id']); ?>">
                        <input class="update-button" type="submit" value="Update">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <div class="no-results">No results found for your search.</div>
<?php endif; ?>
            </div>
            <h2 class="section-title">RECENT USER ACTIVITIES</h2>
            <table>
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Email</th>
        <th>Course</th>
        <th>Year Level</th>
        <th>HK Number & Total Hours</th> <!-- Updated header -->
        <th>Status</th>
        <th>Action</th> <!-- Column for Update Button -->
    </tr>
    <?php
    // Reconnect to the database for recent user activities
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Query for recent users
    $sql = "SELECT user_id, name, email, course, level, hk_status, total_hours, status FROM students";
    $result = $conn->query($sql);

    // Check if there are records in the result
    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row["user_id"]) . "</td>
                    <td>" . htmlspecialchars($row["name"]) . "</td>
                    <td>" . htmlspecialchars($row["email"]) . "</td>
                    <td>" . htmlspecialchars($row["course"]) . "</td>
                    <td>" . htmlspecialchars($row["level"]) . "</td>
                    <td>" . htmlspecialchars($row["hk_status"]) . " (" . htmlspecialchars($row["total_hours"]) . " hours)</td> <!-- Combined HK Number and Total Hours -->
                    <td>" . htmlspecialchars($row["status"]) . "</td> 
                    <td>
                        <form action='update-student.php' method='GET'>
                            <input type='hidden' name='userId' value='" . htmlspecialchars($row['user_id']) . "'>
                            <input class='update-button' type='submit' value='Update'>
                        </form>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No users found.</td></tr>";
    }
    ?>
</table>
<script>
    var hours = 0;

            if (selectedHK === 'HK25') {
                hours = 45;
            } else if (selectedHK === 'HK50') {
                hours = 90;
            } else if (selectedHK === 'HK75') {
                hours = 120;
            } else if (selectedHK === 'HK100') {
                hours = 150;
            }
            total_hours=
</script>


        </div>
            </div>
        </main>
    </div>
</body>
</html>