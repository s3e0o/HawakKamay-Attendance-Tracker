<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher's Profile - UPang HK Attendance Tracker</title>
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
        }
        .container {
            display: flex;
            height: 100%;
        }
        .sidebar {
            width: 200px;
            background-color: #b8860b;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            text-align: center;
            color: #4a5d29;
            font-size: 20px;
            margin-top: 50px;
            margin-bottom: 50px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgb(38, 95, 63);
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
            cursor: pointer;
        }
        .nav-item.active {
            background-color: #8b6914;
        }
        .main-content {
            flex-grow: 1;
            /* background-color: #556b2f; */
            padding: 20px;
            color: white;
        }
        .main-content::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: 4a5d29;
            z-index: 1;
        }
        .content-wrapper {
            position: relative;
            z-index: 2;
        }
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            background-color: #b8860b;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            margin: 15px;
        }
        .input-group input {
            width: 60%;
            padding: 15px;
            display: flex;
            border: none;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.481);
            color: white;
            margin: 10px;
        }
        .save-button {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 15px;
        }
        .save-button:hover {
            background-color: #8b6914;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo"></div>
            <h2>UPang HK <br> Attendance Tracker</h2>
            <div class="nav-item"><a href="instructor-db.php">Dashboard</a></div>
            <div class="nav-item"><a href="student-list.php"></a>Scholars</div>
            <div class="nav-item active">Profile</div>
        </div>
        <div class="main-content">
            <div class="content-wrapper">
                <div class="profile-header">
                    <h1>FACULTY'S PROFILE</h1>
                    <div class="profile-img">IMG</div>
                </div>
                <h2>ABOUT</h2>
                    <form id="teacherProfileForm">
                        <div class="input-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" id="fullName" name="fullName" required>
                        </div>
                        <div class="input-group">
                            <label for="facultyId">FacultyID:</label>
                            <input type="text" id="facultyId" name="facultyId" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="input-group">
                            <label for="phone">Phone No.:</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <button type="submit" class="save-button">Save</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('adminProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Profile saved successfully!');
        });
    </script>
</body>
</html>