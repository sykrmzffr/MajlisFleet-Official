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
    } elseif (isset($_POST['delete'])) {
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
        function showPopup(message, isSuccess) {
            const popup = document.getElementById(isSuccess ? 'success-popup' : 'error-popup');
            const popupMessage = document.getElementById(isSuccess ? 'success-popup-message' : 'error-popup-message');
            const body = document.body;
            popupMessage.textContent = message;
            popup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            const body = document.body;
            popup.style.display = 'none';
            body.classList.remove('modal-open');
        }

        function confirmDeletion(event) {
            event.preventDefault();
            const deletePopup = document.getElementById('delete-popup');
            const body = document.body;
            deletePopup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function cancelDeletion() {
            const deletePopup = document.getElementById('delete-popup');
            const body = document.body;
            deletePopup.style.display = 'none';
            body.classList.remove('modal-open');
        }

        function redirectTo(url) {
            window.location.href = url;
        }

        window.onload = function() {
            const successMessage = "<?php echo $success_message; ?>";
            const errorMessage = "<?php echo $error_message; ?>";
            if (successMessage) {
                showPopup(successMessage, true);
            } else if (errorMessage) {
                showPopup(errorMessage, false);
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
        }
        .popup button:hover {
            background-color: rgb(49, 70, 187);
            cursor: pointer;
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
            <a href="javascript:history.back()"><< Back to Previous Page</a>
            <div class="edit-container">
                <form action="" method="POST" class="form-container">
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
                        <button type="submit" name="delete" id="delete" onclick="return confirm('Are you sure you want to delete this booking?');">DELETE</button>
                    </div>
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
        <form method="POST">
            <button type="submit" name="confirm_delete">Yes, Delete</button>
            <button type="button" onclick="cancelDeletion()">No, Cancel</button>
        </form>
    </div>
</body>
</html>