<?php
session_start();

// Redirect if not logged in as user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
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

// Fetch booking details based on booking ID from the query string
if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    $user_id = $_SESSION['user_id'];

    $sql = "
        SELECT 
            booking.*, 
            vehicles.vehicles_model AS vehicle_model, 
            vehicles.vehicles_plate AS vehicle_plate 
        FROM booking
        LEFT JOIN vehicles ON booking.vehicles_id = vehicles.vehicles_id
        WHERE booking.booking_id = ? AND booking.user_id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("ss", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        echo "Booking not found or access denied.";
        exit();
    }
} else {
    echo "Invalid booking ID.";
    exit();
}

// Initialize message variables
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $booking_datestart = $_POST['booking_datestart'];
        $booking_dateend = $_POST['booking_dateend'];
        $booking_timestart = $_POST['booking_timestart'];
        $booking_timeend = $_POST['booking_timeend'];
        $booking_destination = $_POST['booking_destination'];
        $booking_purpose = $_POST['booking_purpose'];

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
            $clash_sql = "SELECT * FROM booking WHERE vehicles_id = ? AND booking_id != ? AND 
                          ((? BETWEEN CONCAT(booking_datestart, ' ', booking_timestart) AND CONCAT(booking_dateend, ' ', booking_timeend)) OR 
                           (? BETWEEN CONCAT(booking_datestart, ' ', booking_timestart) AND CONCAT(booking_dateend, ' ', booking_timeend)) OR 
                           (CONCAT(booking_datestart, ' ', booking_timestart) BETWEEN ? AND ?) OR 
                           (CONCAT(booking_dateend, ' ', booking_timeend) BETWEEN ? AND ?))";

            $stmt = $dbconn->prepare($clash_sql);
            $stmt->bind_param("ssssssss", $booking['vehicles_id'], $booking_id, $start_datetime, $end_datetime, $start_datetime, $end_datetime, $start_datetime, $end_datetime);
            $stmt->execute();
            $clash_result = $stmt->get_result();

            if ($clash_result && $clash_result->num_rows > 0) {
                $error_message = "Booking time clashes with an existing booking.";
            } else {
                // Update booking details
                $update_sql = "
                    UPDATE booking 
                    SET 
                        booking_datestart = ?, 
                        booking_dateend = ?, 
                        booking_timestart = ?, 
                        booking_timeend = ?, 
                        booking_destination = ?, 
                        booking_purpose = ?
                    WHERE booking_id = ? AND user_id = ?";
                $update_stmt = $dbconn->prepare($update_sql);
                $update_stmt->bind_param(
                    "ssssssss",
                    $booking_datestart,
                    $booking_dateend,
                    $booking_timestart,
                    $booking_timeend,
                    $booking_destination,
                    $booking_purpose,
                    $booking_id,
                    $user_id
                );

                if ($update_stmt->execute()) {
                    $success_message = "Booking updated successfully.";
                } else {
                    $error_message = "Error updating booking.";
                }
            }
        }
    } elseif (isset($_POST['confirm_delete'])) {
        // Directly delete the booking without checking for clashes, date, and time errors
        $delete_sql = "DELETE FROM booking WHERE booking_id = ? AND user_id = ?";
        $delete_stmt = $dbconn->prepare($delete_sql);
        $delete_stmt->bind_param("ss", $booking_id, $user_id);

        if ($delete_stmt->execute()) {
            echo "<script>
                    alert('Booking deleted successfully.');
                    window.location.href = 'bookinghistoryuser.php';
                  </script>";
            exit();
        } else {
            $error_message = "Error deleting booking.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link rel="stylesheet" href="editbookinguserstyle.css">
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
                showErrorPopup('Start date and time must be earlier than end date and time.');
                return false;
            }

            if (startDateTime < currentDateTime) {
                showErrorPopup('Start date and time must be later than current date and time.');
                return false;
            }

            return true;
        }

        function showErrorPopup(message) {
            const popup = document.getElementById('error-popup');
            const popupMessage = document.getElementById('error-popup-message');
            const body = document.body;
            popupMessage.textContent = message;
            popup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function showSuccessPopup(message) {
            const popup = document.getElementById('success-popup');
            const popupMessage = document.getElementById('success-popup-message');
            const body = document.body;
            popupMessage.textContent = message;
            popup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function showDeletePopup() {
            const popup = document.getElementById('delete-popup');
            const body = document.body;
            popup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            const body = document.body;
            popup.style.display = 'none';
            body.classList.remove('modal-open');
        }

        function redirectTo(url) {
            window.location.href = url;
        }

        function confirmDelete() {
            const deleteForm = document.getElementById('delete-form');
            deleteForm.submit();
        }

        window.onload = function() {
            const successMessage = "<?php echo $success_message; ?>";
            const errorMessage = "<?php echo $error_message; ?>";
            if (successMessage) {
                showSuccessPopup(successMessage);
            } else if (errorMessage) {
                showErrorPopup(errorMessage);
            }
        };
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
            z-index: 1000;
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
        .cancel-button {
            background-color: #f44336; /* Red background for the cancel button */
        }
        .cancel-button:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }
        #error-popup button {
            background-color: #f44336; /* Red background for the cancel button */
        }
        #error-popup button:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }
        #delete-popup .cancel-button {
            background-color: #f44336; /* Red background for the cancel button */
        }
        #delete-popup .cancel-button:hover {
            background-color: #d32f2f; /* Darker red on hover */
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
    <div id="blur-background">
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
            <a href="bookinghistoryuser.php"><< Back to Previous Page</a>
            <div class="edit-container">
                <form action="" method="POST" class="form-container" onsubmit="return validateBookingForm()">
                    <p><b>Booking ID</b></p>
                    <input type="text" value="<?php echo htmlspecialchars($booking['booking_id']); ?>" disabled>
                    <br>
                    <p><b>Vehicle</b></p>
                    <input type="text" value="<?php echo htmlspecialchars($booking['vehicle_model'] . " (" . $booking['vehicle_plate'] . ")"); ?>" disabled>
                    <br>
                    <p><b>Date Start</b></p>
                    <input type="date" name="booking_datestart" value="<?php echo htmlspecialchars($booking['booking_datestart']); ?>" required>
                    <br>
                    <p><b>Date End</b></p>
                    <input type="date" name="booking_dateend" value="<?php echo htmlspecialchars($booking['booking_dateend']); ?>" required>
                    <br>
                    <p><b>Time Start</b></p>
                    <input type="time" name="booking_timestart" value="<?php echo htmlspecialchars($booking['booking_timestart']); ?>" required>
                    <br>
                    <p><b>Time End</b></p>
                    <input type="time" name="booking_timeend" value="<?php echo htmlspecialchars($booking['booking_timeend']); ?>" required>
                    <br>
                    <p><b>Destination</b></p>
                    <input type="text" name="booking_destination" value="<?php echo htmlspecialchars($booking['booking_destination']); ?>" required>
                    <br>
                    <p><b>Purpose</b></p>
                    <input type="text" name="booking_purpose" value="<?php echo htmlspecialchars($booking['booking_purpose']); ?>" required>
                    <br>
                    <p><b>Time Created</b></p>
                    <input type="text" value="<?php echo htmlspecialchars($booking['booking_timecreated']); ?>" disabled>
                    <br>
                    <div class="button-container">
                        <button type="submit" name="update" id="update">UPDATE</button>
                        <button type="button" name="delete" id="delete" onclick="showDeletePopup()">DELETE</button>
                    </div>
                </form>
                <form id="delete-form" action="" method="POST">
                    <input type="hidden" name="confirm_delete" value="1">
                </form>
            </div>
        </main>
    
        <footer>
            <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
        </footer>
    </div>

    <!-- Modal for success messages -->
    <div id="success-popup" class="popup">
        <p id="success-popup-message" class="popup-message"></p>
        <button onclick="redirectTo('bookinghistoryuser.php')">View Bookings History</button>
        <button onclick="redirectTo('homepage.php')">Back to Homepage</button>
    </div>

    <!-- Modal for error messages -->
    <div id="error-popup" class="popup">
        <p id="error-popup-message" class="popup-message"></p>
        <button onclick="closePopup('error-popup')">Close</button>
    </div>

    <!-- Modal for delete confirmation -->
    <div id="delete-popup" class="popup">
        <p class="popup-message">Are you sure you want to delete this booking?</p>
        <button onclick="confirmDelete()">Yes, Delete</button>
        <button class="cancel-button" onclick="closePopup('delete-popup')">No, Cancel</button>
    </div>
</body>
</html>