<?php
session_start();
require 'db-connection.php'; // Your database connection
require 'vendor/autoload.php'; // Ensure PHPSpreadsheet is available

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// PHP logout logic
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: multi-login.php"); // Redirect to login page
    exit(); // Exit after redirection
}

// Initialize the $result variable
$result = null;

// Fetch all schedules along with student names
$sql = "
    SELECT 
        s.schedule_id, 
        st.name AS student_name, 
        s.date, 
        s.start_time, 
        s.end_time, 
        s.subject, 
        s.classroom, 
        s.attendance_status, 
        s.assigned_by 
    FROM schedule s
    INNER JOIN students st ON s.user_id = st.user_id
    ORDER BY s.date, s.start_time";

// Execute the query
$result = $conn->query($sql);

// Check if the export button was clicked
if (isset($_POST['export'])) {
    // If the export button was clicked, check if the query was successful
    if ($result) {
        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the header row
        $sheet->setCellValue('A1', 'Schedule ID');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->setCellValue('C1', 'Date');
        $sheet->setCellValue('D1', 'Start Time');
        $sheet->setCellValue('E1', 'End Time');
        $sheet->setCellValue('F1', 'Subject');
        $sheet->setCellValue('G1', 'Classroom');
        $sheet->setCellValue('H1', 'Attendance Status');
        $sheet->setCellValue('I1', 'Assigned By');

        // Populate the spreadsheet with data
        $row = 2; // Start from the second row
        while ($schedule = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $row, $schedule['schedule_id']);
            $sheet->setCellValue('B' . $row, $schedule['student_name']);
            $sheet->setCellValue('C' . $row, $schedule['date']);
            $sheet->setCellValue('D' . $row, $schedule['start_time']);
            $sheet->setCellValue('E' . $row, $schedule['end_time']);
            $sheet->setCellValue('F' . $row, $schedule['subject']);
            $sheet->setCellValue('G' . $row, $schedule['classroom']);
            $sheet->setCellValue('H' . $row, $schedule['attendance_status']);
            $sheet->setCellValue('I' . $row, $schedule['assigned_by']);
            $row++;
        }

        // Set the filename for the export
        $filename = 'schedules_export_' . date('YmdHis') . '.xlsx';

        // Create a writer and save the spreadsheet to the output
        $writer = new Xlsx($spreadsheet);
        
        // Set the appropriate headers for the download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Save the file to the output stream
        $writer->save('php://output');
        exit(); // Exit to prevent further output
    } else {
        // Handle query error
        echo "Error executing query: " . $conn->error;
        exit();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
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
        table {
            margin-top: 10px;
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
        button {
            background-color: #b8860b;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
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
    <div class="container">
    <div class="sidebar" id="sidebar">
            <div alt="PHINMA Logo" class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item active"><a href="admin-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="teacher-list.php">Faculty</a></div>
            <div class="nav-item"><a href="student-list.php">Scholar</a></div>
            <div class="nav-item"><a href="schedules-list.php">Schedule</a></div>
            <div class="nav-item"><a href="admin-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>
            </div> 
        <div class="main-content">
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
            <div class="title">Instructor Dashboard: Schedules List</div>
            <form method="post">
                <button type="submit" name="export">Export as Excel</button>
            </form>
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Schedule ID</th>
                        <th>Student Name</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Subject</th>
                        <th>Classroom</th>
                        <th>Attendance Status</th>
                        <th>Assigned By</th>
                    </tr>
                    <?php while ($schedule = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $schedule['schedule_id']; ?></td>
                            <td><?php echo $schedule['student_name']; ?></td>
                            <td><?php echo $schedule['date']; ?></td>
                            <td><?php echo $schedule['start_time']; ?></td>
                            <td><?php echo $schedule['end_time']; ?></td>
                            <td><?php echo $schedule['subject']; ?></td>
                            <td><?php echo $schedule['classroom']; ?></td>
                            <td><?php echo $schedule['attendance_status']; ?></td>
                            <td><?php echo $schedule['assigned_by']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No schedules available.</p>
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
