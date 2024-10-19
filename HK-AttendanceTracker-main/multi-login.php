<?php 
session_start(); // Start the session

// Include your database connection file
include 'db-connection.php'; // Adjust the path if necessary

// Create a connection
$conn = new mysqli('localhost', 'root', '', 'hk-management');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is already logged in
if (isset($_SESSION['username'])) {
    // Redirect based on role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin-db.php");
        exit();
    } elseif ($_SESSION['role'] == 'teacher') {
        header("Location: instructor-db.php");
        exit();
    } else {
        header("Location: student-db.php");
        exit();
    }
}

// Initialize error message
$error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Trim whitespace
    $password = $_POST['password'];

    // Prepare SQL query to fetch user with the provided username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['username'] = htmlspecialchars($user['username']);
                $_SESSION['role'] = $user['role'];
                $_SESSION['id'] = $user['id'];
                $_SESSION['user_id'] = $user['user_id']; // Assign the user ID to the session
                
                // Redirect based on role
                if ($_SESSION['role'] == 'admin') {
                    header("Location: admin-db.php");
                } elseif ($_SESSION['role'] == 'teacher') {
                    header("Location: instructor-db.php");
                } else {
                    header("Location: student-db.php");
                }
                exit(); // Ensure no further code is executed
            } else {
                $error_message = "Password is incorrect!";
            }
        } else {
            $error_message = "No user found with this username.";
        }
    } else {
        $error_message = "Error in SQL query execution: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HKAT Login Page</title>
    <link rel="icon" type="image" href="hk_logo.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('hkat-upang.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            border-radius: 10px;
            display: flex;
            max-width: 800px;
            width: 100%;
        }
        .logo-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: rgba(204, 153, 0, 0.9);
            padding: 40px 10px;
            border-radius: 10px 10px 10px 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }
        .logo {
            width: 75%;
        }
        .form-container {
            flex: 1;
            padding: 65px 20px;
        }
        h1 {
            color: #a98d00;
            text-align: left;
            font-style: italic;
            text-shadow: 1px 1px 2px black;
        }
        h2 {
            color: #3b4f26;
            text-align: center;
        }
        hr {
            border: 1px solid rgba(204, 153, 0, 0.9);
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.9);
        }
        input[type="submit"] {
            text-align: center;
            display: flex;
            width: 50%;
            margin: auto;
            padding: 5px;
            background-color: #a98d00;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <script>
        // Prevent going back to the login page after logging in
        history.pushState(null, '', location.href);
        window.onpopstate = function (event) {
            history.pushState(null, '', location.href);
        };
    </script>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="hk_logo.png" alt="PHINMA Logo" class="logo">
            <h2>UPang HK<br>Attendance Tracker</h2>
        </div>
        <div class="form-container">
            <h1>Login to continue.</h1>
            <hr>
            <form action="" method="POST" autocomplete="off" id="loginForm">                
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="LOGIN">
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div> <!-- Display error message -->
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        // This will remove previously entered form data after page reload
        window.onload = function() {
            document.getElementById('loginForm').reset();
        };
    </script>
</body>
</html>
