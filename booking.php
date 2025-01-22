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

// Initialize message variables
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $vehicles_id = $_POST['vehicles_id']; // Vehicle ID from the hidden input
        $booking_datestart = $_POST['booking_datestart'];
        $booking_dateend = $_POST['booking_dateend'];
        $booking_timestart = $_POST['booking_timestart'];
        $booking_timeend = $_POST['booking_timeend'];
        $booking_destination = mysqli_real_escape_string($dbconn, $_POST['booking_destination']);
        $booking_purpose = mysqli_real_escape_string($dbconn, $_POST['booking_purpose']);

        // Combine date and time for comparison
        $start_datetime = $booking_datestart . ' ' . $booking_timestart;
        $end_datetime = $booking_dateend . ' ' . $booking_timeend;
        $current_datetime = date('Y-m-d H:i:s');

        // Ensure start date and time are not later than end date and time
        if (strtotime($start_datetime) >= strtotime($end_datetime)) {
            $error_message = "Start date and time must be earlier than end date and time.";
        } elseif (strtotime($start_datetime) < strtotime($current_datetime)) {
            $error_message = "Start date and time must be later than current date and time.";
        } else {
            // Check for booking clashes
            $clash_sql = "SELECT * FROM booking WHERE vehicles_id = ? AND 
                          ((? BETWEEN CONCAT(booking_datestart, ' ', booking_timestart) AND CONCAT(booking_dateend, ' ', booking_timeend)) OR 
                           (? BETWEEN CONCAT(booking_datestart, ' ', booking_timestart) AND CONCAT(booking_dateend, ' ', booking_timeend)) OR 
                           (CONCAT(booking_datestart, ' ', booking_timestart) BETWEEN ? AND ?) OR 
                           (CONCAT(booking_dateend, ' ', booking_timeend) BETWEEN ? AND ?))";

            $stmt = $dbconn->prepare($clash_sql);
            $stmt->bind_param("sssssss", $vehicles_id, $start_datetime, $end_datetime, $start_datetime, $end_datetime, $start_datetime, $end_datetime);
            $stmt->execute();
            $clash_result = $stmt->get_result();

            if ($clash_result && $clash_result->num_rows > 0) {
                $error_message = "Booking time clashes with an existing booking.";
            } else {
                // Booking status is set to 'pending'
                $booking_status = 'Pending';

                // Generate new booking_id
                $result = mysqli_query($dbconn, "SELECT booking_id FROM booking ORDER BY booking_id DESC LIMIT 1");
                if ($result && mysqli_num_rows($result) > 0) {
                    $last_id = mysqli_fetch_assoc($result)['booking_id'];
                    // Extract the numeric part and increment it
                    $numeric_part = (int) substr($last_id, 2);
                    $new_numeric_part = str_pad($numeric_part + 1, 7, '0', STR_PAD_LEFT);
                    $booking_id = "BK$new_numeric_part";
                } else {
                    // If no records exist, start with the first ID
                    $booking_id = "BK0000001";
                }

                // Insert data into the `booking` table
                $sql = "INSERT INTO booking (booking_id, user_id, vehicles_id, booking_datestart, booking_dateend, booking_timestart, booking_timeend, booking_destination, booking_purpose, booking_status) 
                        VALUES ('$booking_id', '$user_id', '$vehicles_id', '$booking_datestart', '$booking_dateend', '$booking_timestart', '$booking_timeend', '$booking_destination', '$booking_purpose', '$booking_status')";

                if (mysqli_query($dbconn, $sql)) {
                    $success_message = "Booking submitted successfully.";
                } else {
                    $error_message = "Error: " . mysqli_error($dbconn);
                }
            }
        }
    }
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
                showPopup('Start date and time must be earlier than end date and time.', false);
                return false;
            }

            if (startDateTime < currentDateTime) {
                showPopup('Start date and time must be later than current date and time.', false);
                return false;
            }

            return true;
        }

        // Check if there is a message from PHP
        window.onload = function() {
            const successMessage = "<?php echo $success_message; ?>";
            const errorMessage = "<?php echo $error_message; ?>";
            if (successMessage) {
                showPopup(successMessage, true);
            } else if (errorMessage) {
                showPopup(errorMessage, false);
            }
        };

        function showPopup(message, isSuccess) {
            const popup = document.getElementById(isSuccess ? 'success-popup' : 'error-popup');
            const popupMessage = document.getElementById(isSuccess ? 'success-popup-message' : 'error-popup-message');
            const overlay = document.getElementById('overlay');
            const body = document.body;
            popupMessage.textContent = message;
            popup.style.display = 'block';
            overlay.style.display = 'block';
            body.classList.add('modal-open');
        }

        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            const overlay = document.getElementById('overlay');
            const body = document.body;
            popup.style.display = 'none';
            overlay.style.display = 'none';
            body.classList.remove('modal-open');
        }

        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
    <style>
        /* Modal styles */
        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            width: 30%;
            text-align: center;
            border-radius: 10px;
        }
        .popup-message {
            margin-bottom: 20px;
        }
        .popup button {
            padding: 10px;
            margin: 5px;
            background-color: #4070f4;
            color: #fff;
            width: 40%;
            border-radius: 10px;
        }
        .popup button:hover {
            background-color: rgb(49, 70, 187);
            cursor: pointer;
        }

        /* Overlay styles */
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        /* Background blur and disable styles */
        .modal-open {
            overflow: hidden;
        }
        .modal-open #blur-background {
            filter: blur(5px);
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div id="overlay"></div>
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
        <a href="bookinglist.php"><< Back to Previous Page</a>
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
                <form action="" method="POST" onsubmit="return validateBookingForm()">
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
                    <button type="submit" name="submit">SUBMIT</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Modal for success messages -->
    <div id="success-popup" class="popup">
        <p id="success-popup-message" class="popup-message"></p>
        <button onclick="redirectTo('bookinghistoryuser.php')">View Booking History</button>
        <button onclick="redirectTo('homepage.php')">Back to Homepage</button>
    </div>

    <!-- Modal for error messages -->
    <div id="error-popup" class="popup">
        <p id="error-popup-message" class="popup-message"></p>
        <button onclick="closePopup('error-popup')" style="background: #f44336">Try Again</button>
    </div>
    
    <footer>
        <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
    </footer>
</body>
</html>