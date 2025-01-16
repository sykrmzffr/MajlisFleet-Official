<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: index.php");
    exit();
}

include('dbconn.php');

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type']; // Get user type from session

if ($user_type == 'admin') {
    // Redirect to homepage if user is not an admin
    header("Location: homepageadmin.php");
    exit();
}

// Get the vehicle ID from the query string
if (isset($_GET['vehicle_id']) && !empty($_GET['vehicle_id'])) {
    $vehicle_id = $_GET['vehicle_id'];

    // Fetch vehicle details based on the vehicle ID
    $sql = "SELECT vehicles.*, driver.driver_fullname FROM vehicles
            LEFT JOIN driver ON vehicles.driver_id = driver.driver_id
            WHERE vehicles.vehicles_id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("s", $vehicle_id); // Use "s" for string type
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $vehicle = $result->fetch_assoc();
    } else {
        echo "Vehicle not found.";
        exit();
    }
} else {
    echo "Invalid vehicle ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking</title>
    <link rel="stylesheet" href="bookingstyle.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <script>
        function validateBookingForm() {
            const bookingDateStart = new Date(document.querySelector('input[name="booking_datestart"]').value);
            const bookingTimeStart = document.querySelector('input[name="booking_timestart"]').value;
            const bookingDateEnd = new Date(document.querySelector('input[name="booking_dateend"]').value);
            const bookingTimeEnd = document.querySelector('input[name="booking_timeend"]').value;

            const startDateTime = new Date(`${bookingDateStart.toISOString().slice(0, 10)}T${bookingTimeStart}`);
            const endDateTime = new Date(`${bookingDateEnd.toISOString().slice(0, 10)}T${bookingTimeEnd}`);
            const currentDateTime = new Date();

            if (startDateTime >= endDateTime) {
                showPopup('Start date and time must be earlier than end date and time.');
                return false;
            }

            if (startDateTime < currentDateTime) {
                showPopup('Start date and time must be later than current date and time.');
                return false;
            }

            return true;
        }

        function showPopup(message) {
            const popup = document.getElementById('popup');
            const popupMessage = document.getElementById('popup-message');
            popupMessage.textContent = message;
            popup.style.display = 'block';
        }

        function closePopup() {
            const popup = document.getElementById('popup');
            popup.style.display = 'none';
        }

        // Check if there is an error message from PHP
        window.onload = function() {
            const phpError = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
            if (phpError) {
                showPopup(phpError);
            }
        };
    </script>
    <style>
        /* Modal styles */
        #popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 30%;
            text-align: center;
            border-radius: 10px;
        }
        #popup-message {
            margin-bottom: 20px;
        }
        #popup button {
            padding: 10px;
            margin: 5px;
            background-color: #4070f4;
            color: #fff;
            width: 40%;
        }
        #popup button:hover {
            background-color: rgb(49, 70, 187);
            cursor: pointer;
        }
    </style>
</head>
<body>
    
        <div class="navbar">
            <div class="nav-items">
            <div class="nav-logo">
                <img src="image/logo.png" alt="">
                <a href="homepage.php">MajlisFleet</a>
            </div>
            <div class="nav-content">
                <ul>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
        </div>
        
        <main>
            <a href="javascript:history.back()"><< Back to Previous Page</a>
            <div class="booking-container">

                <div class="vehicle-details-container">
                    <div class="img-vehicle">
                        <img src="<?php echo $vehicle['vehicles_img']; ?>" alt="Vehicle Image">
                        <p><?php echo strtoupper($vehicle['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                        <p><?php echo strtoupper($vehicle['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                        <p>Capacity: <?php echo strtoupper($vehicle['vehicles_capacity']); ?></p> <!-- PASSENGERS CAPACITY -->
                    </div>
                    <div class="img-driver">
                        <h4><b>DRIVER DETAILS</b></h4>
                        <img src="image/dp.jpg" alt="Driver Image">
                        <p><?php echo strtoupper($vehicle['driver_fullname']); ?></p>
                    </div>
                </div>

                <div class="booking-form-container">
                <form action="bookingprocess.php" method="POST" onsubmit="return validateBookingForm()">
                    <h2><b>Booking Details</b></h2>
                    <p>Date Start</p>
                    <input type="date" name="booking_datestart" required>
                    <p>Time Start</p>
                    <input type="time" name="booking_timestart" required>
                    <p>Date End</p>
                    <input type="date" name="booking_dateend" required>
                    <p>Time End</p>
                    <input type="time" name="booking_timeend" required>
                    <p>Destination</p>
                    <input type="text" name="booking_destination" required style="height: 50px;">
                    <p>Purpose</p>
                    <input type="text" name="booking_purpose" required style="height: 50px;">
                    <input type="hidden" name="vehicles_id" value="<?php echo htmlspecialchars($vehicle['vehicles_id']); ?>"> <!-- Hidden vehicle ID -->
                    <button type="submit">SUBMIT</button>
                </form>
                </div>
            </div>
        </main>

        <!-- Modal for error messages -->
        <div id="popup">
            <p id="popup-message"></p>
            <button onclick="closePopup()">Close</button>
        </div>
        
        <footer>
            <p>Footer Content</p>
        </footer>
    
</body>
</html>