<?php
session_start(); 
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

?> 

<?php
// Include connection script
require_once('connection.php');

// Rest of your script using $conn for database operations
?>

<?php

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user data based on user ID
$sql = "SELECT username FROM users WHERE uid = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc(); // Get user data as an associative array
    $fullname = $user['username']; // Extract username from data
} else {
    // Handle potential error (user ID not found)
    
    echo "User data not found";
}
$sql_rented_cars = "SELECT o.*, v.modelname, v.price, u.username AS seller_name
                    FROM orders o 
                    INNER JOIN vehicles v ON o.sid = v.hid AND o.vid = v.vid 
                    INNER JOIN users u ON v.hid = u.uid 
                    WHERE o.bid = '$user_id'";
$result_rented_cars = $conn->query($sql_rented_cars);

$sql_added_cars = "SELECT *
                   FROM vehicles
                   WHERE hid = $user_id";
$result_added_cars = $conn->query($sql_added_cars);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>USER HOME</title>
    <link rel="stylesheet" href="style.css">
    <!-- Include your stylesheets and scripts here -->
    <style>
        .table-container {
            width: 120%; /* Increase size by 20% */
            max-height: 400px;
            overflow: auto;
            margin: 20px auto;
            border: 1px solid #ccc;
            padding: 10px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th, .order-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .market-button:hover {
            background: transparent;
            color: var(--color-primary);
        }

        .add-car-button {
            position: fixed;
            right: 20px;
            bottom: 20px;
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 12px 28px;
            background: var(--color-primary);
            border: 2px solid var(--color-primary);
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            color: var(--color-white);
            font-weight: 600;
            text-decoration: none; /* Remove underlines for links */
            text-align: center;
        }

        .add-car-button:hover {
            background: transparent;
            color: var(--color-primary);
        }

        .market-button {
            position: fixed;
            left: 20px;
            bottom: 20px;
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 12px 28px;
            background: var(--color-primary);
            border: 2px solid var(--color-primary);
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            color: var(--color-white);
            font-weight: 600;
            text-decoration: none; /* Remove underlines for links */
            text-align: center;
        }

        .inbox-logo {
    position: fixed;
    background-color: #ffffff; /* White color */
    width: 90px;
    height: 90px;
    line-height: 90px;
    text-align: center;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    cursor: pointer;
    z-index: 1001; /* Ensure the inbox logo stays above the popup */
}

.inbox-logo img {
    width: 50px; /* Adjust the image size as needed */
    height: 50px;
    vertical-align: middle; /* Align the image vertically */
}

.inbox-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 20px;
    background-color: #ffffff;
    border: 2px solid #ccc;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    z-index: 1000;
}

        .inbox-popup p {
            margin-bottom: 15px;
        }

        .inbox-popup button {
            padding: 10px 20px;
            margin-right: 10px;
            cursor: pointer;
            background-color: var(--color-primary);
            color: var(--color-white);
            border: none;
            border-radius: 4px;
            font-weight: 600;
        }

        .inbox-popup button:hover {
            background-color: transparent;
            color: var(--color-primary);
        }

        .container {
            max-width: 1620px;
            width: 90%;
            margin: 0 auto;
        }

        .inbox-popup.show {
    display: block;
}

    </style>
    </style>
</head>

<body>
    <nav class="navbar slide-in">
        <div class="container">
            <h1 class="logo">USER PORTAL</h1>
            <p class="dealer-name">Welcome, <?php echo $fullname; ?></p>
            <ul class="nav-links">
                <li><a href="logout.php">Logout</a></li>
                
            </ul>
        </div>
    </nav>

    <h3 class="page-title slide-in">Rental History</h3>

    <div class="table-container slide-in">
    <table class="order-table slide-in">
    <tr>
        <th>Model Name</th>
        <th>Seller Name</th>
        <th>Rental Amount</th>
        <th>Rental Status</th>
        <th>Time Remaining/Completed</th>
    </tr>
    <?php
    // Check if any records are found
    if ($result_rented_cars->num_rows > 0) {
        // Loop through rented cars data and display in table rows
        while ($row = $result_rented_cars->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['modelname'] . "</td>";
            echo "<td>" . $row['seller_name'] . "</td>";
            echo "<td>₹" . $row['rentamnt'] . "</td>";
            
            $expiryDate = new DateTime($row['expiry']);
            $now = new DateTime();
            
            if ($row['iscomplete'] == 1) {
                echo "<td>Completed</td>";
                echo "<td>Returned on " . $expiryDate->format('Y-m-d H:i') . "</td>";
            } else {
                if ($now < $expiryDate) {
                    $interval = $now->diff($expiryDate);
                    echo "<td>Ongoing</td>";
                    echo "<td>" . $interval->format('%a days %h hours remaining') . "</td>";
                } else {
                    echo "<td>Expired</td>";
                    echo "<td>Expired on " . $expiryDate->format('Y-m-d H:i') . "</td>";
                }
            }
            echo "</tr>";
        }
    } else {
        // No records found, display a message
        echo '<tr><td style="text-align: center;" colspan="5">You have not yet rented any car</td></tr>';
    }
        ?>
    </table>
    </div>
    <h3 class="page-title slide-in">Added Cars</h3>
   
<div class="table-container slide-in">
    <table class="order-table slide-in">
        <tr>
            <th>Model Name</th>
            <th>City</th>
            <th>Capacity</th>
            <th>Connector</th>
            <th>Driving Range</th>
            <th>Price/Day</th>
            <th>Availability</th>
        </tr>
        <?php
        // Check if any cars are added by the user
        if ($result_added_cars->num_rows > 0) {
            // Loop through added cars data and display in table rows
            while ($row = $result_added_cars->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['modelname'] . "</td>";
                echo "<td>" . $row['city'] . "</td>";
                echo "<td>" . $row['capacity'] . "</td>";
                echo "<td>" . $row['connector'] . "</td>";
                echo "<td>" . $row['drange'] . "</td>";
                echo "<td>₹" . $row['price'] . "</td>";
                echo "<td>" . ($row['availability'] ? 'Available for Rent' : 'Rented') . "</td>"; // Assuming availability is boolean
                if ($row['availability']) {
                    echo '<td><form method="POST" action="delete_car.php">
                            <input type="hidden" name="vid" value="' . $row['vid'] . '">
                            <button type="submit" name="delete_car">Delete</button>
                          </form></td>';
                } else {
                    echo "<td></td>";
                }
                echo "</tr>";
            }
        } else {
            // No cars added by the user, display a message
            echo '<tr><td style="text-align: center;" colspan="7">You have not added any cars yet</td></tr>';
        }
        ?>
    </table>
    </div>
    
    <?php


        // Check if success message session variable is set
        if (isset($_SESSION['success_message'])) {
            echo $_SESSION['success_message'];
            
            // Unset the session variable after displaying the message
            unset($_SESSION['success_message']);
        }
    ?>

    <!-- Add a button that links to the "add car" page -->
    <a href="addcar.php" class="add-car-button slide-in">Add a Car</a>
    <a href="market.php" class="market-button slide-in">Market</a>

    <!-- Inbox logo with link to a new page -->
    <div class="inbox-logo slide-in" onclick="toggleInboxPopup()">
        <img id="inboxIcon" src="images/inbox.png" alt="">
        <span id="inboxCounter">2</span>
    </div>

    <!-- Inbox Pop-up -->
    <div class="inbox-popup" id="inboxPopup">
        <div class="inbox-message">
            <p>You have a new request!</p>
            <p><strong>Time:</strong> 2 Days</p>
            <p><strong>Name:</strong> Joel </p>
            <p><strong>User Name:</strong> joelgandu</p>
            <button onclick="acceptRequest()">Accept</button>
            <button onclick="rejectRequest()">Reject</button>
        </div>
        <br>
        <div class="inbox-message">
            <p>You have another new request!</p>
            <p><strong>Time:</strong> 1 Day</p>
            <p><strong>Name:</strong> Alice </p>
            <p><strong>User Name:</strong> alice123</p>
            <button onclick="acceptRequest()">Accept</button>
            <button onclick="rejectRequest()">Reject</button>
        </div>
        <!-- Add more inbox messages here -->
    </div>

    <script>
        let inboxMessages = 0;

        document.addEventListener("DOMContentLoaded", function () {
            const elementsToAnimate = document.querySelectorAll(".slide-in");
            elementsToAnimate.forEach((element) => element.classList.add("animated"));
        });

        function toggleInboxPopup() {
            const popup = document.getElementById("inboxPopup");
            popup.classList.toggle("show"); // Toggle the "show" class
            updateInboxCounter(0); // Reset the counter when the inbox is opened
        }

        function acceptRequest() {
            // Replace this alert with your actual logic for accepting the request
            alert("Request accepted!");
            closeInboxPopup();
            updateInboxCounter(-1); // Decrement the counter when a request is accepted
        }

        function rejectRequest() {
            // Replace this alert with your actual logic for rejecting the request
            alert("Request rejected!");
            closeInboxPopup();
            updateInboxCounter(-1); // Decrement the counter when a request is rejected
        }

        function closeInboxPopup() {
            const popup = document.getElementById("inboxPopup");
            popup.classList.remove("show"); // Remove the "show" class to hide the popup
        }

        function updateInboxCounter(change) {
            inboxMessages += change;
            const inboxCounter = document.getElementById("inboxCounter");
            const inboxIcon = document.getElementById("inboxIcon");
            inboxCounter.textContent = inboxMessages > 0 ? inboxMessages : 0;

            // Update the image source based on the number of inbox messages
            if (inboxMessages > 2) {
                inboxIcon.src = "images/inbox.png"; // Image indicating there are unread messages
            } else {
                inboxIcon.src = "images/inbox.png"; // Default image for no messages
            }
        }
    </script>
</body>

</html>
