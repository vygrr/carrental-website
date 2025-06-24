<?php
// Database connection details (replace with your actual credentials)
$servername = "localhost";
$username = "root"; // MySQL User Name
$password = ""; // MySQL Password
$dbname = "user"; // MySQL DB Name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Escape user input to prevent SQL injection
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']); 
$email = mysqli_real_escape_string($conn, $_POST['email']); 

// Hash the password before storing
$hashed_password = password_hash($password, PASSWORD_DEFAULT); 


// Check if user already exists
$sql1 = "SELECT * FROM users WHERE username = '$username'";
$sql2 = "SELECT * FROM users WHERE phone = '$phone'";
$sql3 = "SELECT * FROM users WHERE email = '$email'";
$result1 = $conn->query($sql1);
$result2 = $conn->query($sql2);
$result3 = $conn->query($sql3);

if ($result1->num_rows == 1) {
    // User already exists
    $error = "Username already exists";
} 
if ($result2->num_rows == 1) {
    // Phone number already exists
    $error = "Phone number already exists";
} 
if ($result3->num_rows == 1) {
    // Email number already exists
    $error = "Email already exists";
} 
if (($result1->num_rows == 0) && ($result2->num_rows == 0) && ($result3->num_rows == 0)){
    // Insert user data if not existing
    $sql = "INSERT INTO users (username, password, email, phone, fullname) VALUES ('$username', '$hashed_password', '$email', '$phone', '$fullname')";
    if ($conn->query($sql) === TRUE) {
        // Registration successful (redirect to login or success page)
        $success = "Account Registered. Go to Login Page.";  // Replace with your success page
    }
    else {
        // Registration failed
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
