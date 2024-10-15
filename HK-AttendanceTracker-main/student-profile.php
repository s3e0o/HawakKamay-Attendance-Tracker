<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar's Profile - UPang HK Attendance Tracker</title>
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
        .main-content {
            flex-grow: 1;
            background-image: url('https://hebbkx1anhila5yf.public.blob.vercel-storage.com/hkat-upang-erlfyVc5JfNZbsEvGCv0o5oLU8rxmv.jpg');
            background-size: cover;
            background-position: center;
            padding: 20px;
            color: white;
            position: relative;
            margin: 0%;
        }
        .main-content::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(31, 98, 60, 0.592);
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
        }
        .input-group input {
            width: 50%;
            padding: 15px;
            display: flex;
            border: none;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .save-button {
            background-color: #b8860b;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
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
            <div class="nav-item active"><a href="student-db.php">Schedule</a></div>
            <div class="nav-item"><a href="student-profile.php">Profile</a></div>
            <div class="nav-item">
                <a href="?logout=true" class="logout-btn">Log Out</a>
            </div>
        </div>
        <div class="main-content">
            <div class="content-wrapper">
                <div class="profile-header">
                    <h1>SCHOLAR'S PROFILE</h1>
                    <div class="profile-img">IMG</div>
                </div>
                <h2>ABOUT</h2>
                    <form id="adminProfileForm">
                        <div class="input-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" id="fullName" name="fullName" required>
                        </div>
                        <div class="input-group">
                            <label for="studentId">StudentID:</label>
                            <input type="text" id="studentId" name="studentId" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="input-group">
                            <label for="phone">Phone No.:</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="input-group">
                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address" required>
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