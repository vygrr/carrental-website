<?php
// Database connection details (replace with your actual credentials)
$servername = "localhost";
$username = "your_username"; // MySQL User Name
$password = "your_password"; // MySQL Password
$dbname = "user"; // MySQL DB Name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to close the connection (optional)
function closeConnection($conn) {
  $conn->close();
}
?>
