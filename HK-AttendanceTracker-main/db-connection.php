<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "hk-management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; // For testing purposes, you can remove this later
?>
