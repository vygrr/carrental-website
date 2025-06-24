<?php
session_start(); 
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Razorpay payment success
    if (isset($_POST['razorpay_payment_id'])) {
        $user_id = $_SESSION['user_id'];
        $totalprice = $_POST['days'] * $_POST['carPrice'];
        $vid = $_POST['vid'];
        
        $sql = "SELECT hid FROM vehicles WHERE vid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $vid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $hid = $row['hid'];
        
        $currentDateTime = date('Y-m-d H:i:s');
        $rentEndDate = date('Y-m-d H:i:s', strtotime($currentDateTime . ' + ' . $_POST['days'] . ' days'));
        
        $sql1 = "INSERT INTO orders (vid, bid, sid, rentamnt, expiry) VALUES ('$vid', '$user_id', '$hid', '$totalprice', '$rentEndDate')";
        $sql2 = "UPDATE vehicles SET availability = 0 WHERE vid = '$vid'";

        if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
            $_SESSION['success_message'] = "Car rented successfully! Payment ID: " . $_POST['razorpay_payment_id'];
            header("Location: home.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars For Sale</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .car-card {
            transition: transform 0.5s, opacity 0.5s;
            display: flex;
        }

        .car-card.hidden {
            transform: translateX(100%);
            opacity: 0;
            display: none;
        }

        .car-card-image {
            flex: 1;
        }

        .car-card-specs {
            flex: 1;
            padding: 1rem;
            margin: 5px;
        }

        .car-card-description {
            flex: 1;
            padding: 1rem;
            margin: 5px;
        }

        .car-card-description p {
            font-size: 16px;
            line-height: 1.5;
        }

        .car-card {
    display: block;
    transform: translateY(100%); /* Start with the cards off-screen */
    transition: transform 0.5s ease-in-out;
    transform-origin: center bottom;
}

.car-card.show {
    transform: translateY(0); /* Show the cards with a sliding animation */
}

.car-card {
    display: block;
    transition: transform 0.25s ease-in-out;
    transform-origin: center bottom;
    transform: translateY(0);
}

.car-card.hidden {
    transform: translateY(100%);
}

.car-card {
    display: flex;
    background-color: #fff;
    border-radius: 20px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    margin: 25px;
    text-align: left;
}

.car-card img {
    max-width: 460px; /* Adjust the max-width to control image size */
    height: auto;
    margin-right: 20px; /* Add margin between image and text */
}

.car-card h3 {
    font-size: 25px;
    margin-bottom: 10px;
}

.car-card ul {
    list-style-type: none;
    padding: 0;
    margin-left: 0; /* Remove the default margin */
}

.car-card ul li {
    margin-bottom: 8px;
}



.car-card button:hover {
    background-color: #0056b3;
}


/* Add this CSS to style the "Check Availability" button */
.open-button {
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 3px;
    padding: 10px 20px; /* Adjust the padding to add space around the button */
    margin-top: 10px; /* Optionally add margin to separate the button from other elements */
    cursor: pointer;
    transition: background-color 0.3s;
}

.car-card button:hover {
    background-color: #0056b3;
}

/* Add this CSS to style the city selector */
.city-selector {
    text-align: center;
    margin: 20px;
}

#city {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 16px;
}

.top-buttons {
            position: absolute;
            top: 25px;
            left: 5%;
            z-index: 9999;
        }

        .top-buttons .button {
            margin-right: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .top-buttons .button:hover {
            background-color: #0056b3;
        }
      

/* The popup form - hidden by default */
.form-popup {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  border: 3px solid #f1f1f1;
  z-index: 9;
}

/* Add styles to the form container */
.form-container {
  max-width: 300px;
  width: 90%;
  padding: 10px;
  background-color: white;
}

/* Full-width input fields */
.form-container input[type=number]{
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  border: none;
  background: #f1f1f1;
}

/* When the inputs get focus, do something */
.form-container input[type=text]:focus, .form-container input[type=password]:focus {
  background-color: #ddd;
  outline: none;
}

/* Set a style for the submit/login button */
.form-container .btn {
  background-color: #0056b3;
  color: white;
  padding: 16px 20px;
  border: none;
  cursor: pointer;
  width: 100%;
  margin-bottom:10px;
  opacity: 0.8;
}

/* Add a red background color to the cancel button */
.form-container .cancel {
  background-color: red;
}

/* Add some hover effects to buttons */
.form-container .btn:hover, .open-button:hover {
  opacity: 1;
}


    </style>
</head>
<body>
<div class="city-selector">
        <label for="city">Select a city: </label>
        <select id="city">
            <option value="Belapur">Belapur</option>
            <option value="Nerul">Nerul</option>
            <option value="Sanpada">Sanpada</option>
            <option value="Vashi">Vashi</option>
            <option value="Kopar Kharaine">Kopar Kharaine</option>
            <option value="Ghansoli">Ghansoli</option>
            <option value="Airoli">Airoli</option>
        </select>
    </div>
<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch car data from the database
$sql = "SELECT vid, modelname, city, capacity, drange, price, connector, descr, file FROM vehicles WHERE hid != " . $_SESSION['user_id'] . " AND availability = 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="car-card" data-city="' . $row['city'] . '">';
        if (!empty($row['file'])) {
            echo '<div class="car-card-image">';
            echo '<img src="images/' . $row['file'] . '" alt="' . $row['modelname'] . '">';
            echo '</div>';
        }
        echo '<div class="car-card-specs">';
        echo '<h3>' . $row['modelname'] . '</h3>';
        echo '<p>Driving Range: ' . $row['drange'] . '</p>';
        echo '<p>Seats: ' . $row['capacity'] . '</p>';
        echo '<p>Connector Type: ' . $row['connector'] . '</p>';
        echo '<p>Price/Day: ' . $row['price'] . '</p>';
        echo '<button class="open-button" data-vid="' . $row['vid'] . '" data-price="' . $row['price'] . '" onclick="openForm(' . $row['vid'] . ', ' . $row['price'] . ', \'' . $row['modelname'] . '\')" name="submit1">Check Availability</button>';
        echo '</div>';
        echo '<div class="car-card-description"> <p>Description: ' . $row['descr'] . '</p> </div>';
        echo '</div>';
    }
} else {
    echo "No cars available";
}

$conn->close();
?>

<div class="form-popup" id="myForm">
    <form class="form-container" id="rentForm" method="post">
        <h1 id="carNameHeader"></h1>
        <br>
        <input type="hidden" id="carPrice" name="carPrice"> 
        <input type="hidden" id="vidInput" name="vid">
        <label for="days"><b>Days</b></label>
        <input type="number" id="daysInput" name="days" placeholder="Enter no of days" oninput="calculateTotal(this.value)">
        <br><p>=</p>
        <p id="result"> </p>
        <button type="button" class="btn" onclick="processPayment()">Proceed to Payment</button>
        <button type="button" class="btn cancel" onclick="closeForm()">Close</button>
    </form>
</div>

<script>
    const citySelector = document.getElementById("city");
    const carCards = document.querySelectorAll(".car-card");
    
    citySelector.addEventListener("change", () => {
        const selectedCity = citySelector.value;
        carCards.forEach(card => {
            if (selectedCity === "all" || card.getAttribute("data-city") === selectedCity) {
                card.classList.remove("hidden");
            } else {
                card.classList.add("hidden");
            }
        });
    });

    let currentCarName = '';
    
    function openForm(vid, price, carName) {
        document.getElementById("myForm").style.display = "block";
        document.getElementById("carPrice").value = price;
        document.getElementById("daysInput").placeholder = "Enter no of days (Price/Day: " + price + ")";
        document.getElementById("vidInput").value = vid;
        document.getElementById("carNameHeader").textContent = carName;
        currentCarName = carName;
    }

    function closeForm() {
        document.getElementById("daysInput").value = "";
        document.getElementById("result").innerText = "";
        document.getElementById("myForm").style.display = "none";
    }

    function calculateTotal(days) {
        const carPrice = document.getElementById("carPrice").value;
        const totalPrice = days * carPrice || 0;
        document.getElementById("result").innerText = "₹" + totalPrice;
    }
    
    function processPayment() {
        const days = document.getElementById("daysInput").value;
        const carPrice = document.getElementById("carPrice").value;
        const vid = document.getElementById("vidInput").value;
        
        if (!days || days <= 0) {
            alert("Please enter a valid number of days");
            return;
        }
        
        const amount = days * carPrice * 100; // Razorpay uses paise (multiply by 100)
        
        const options = {
            "key": "rzp_test_es0KPgPxomh8Lr",
            "amount": amount,
            "currency": "INR",
            "name": "Car Rental Service",
            "description": "Rental for " + currentCarName + " for " + days + " days",
            "handler": function (response) {
                // Create a form dynamically and submit it
                const form = document.getElementById("rentForm");
                const paymentIdInput = document.createElement("input");
                paymentIdInput.type = "hidden";
                paymentIdInput.name = "razorpay_payment_id";
                paymentIdInput.value = response.razorpay_payment_id;
                form.appendChild(paymentIdInput);
                form.submit();
            },
            "prefill": {
                "name": "Customer Name",
                "email": "customer@example.com",
                "contact": "9999999999"
            },
            "theme": {
                "color": "#3399cc"
            }
        };
        
        const rzp = new Razorpay(options);
        rzp.open();
    }
</script>
</body>
</html>