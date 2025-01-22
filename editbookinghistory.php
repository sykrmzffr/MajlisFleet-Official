<?php
session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Fetch booking details based on booking ID from the query string
if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    $sql = "
        SELECT 
            booking.*, 
            user.user_fullname AS user_name, 
            vehicles.vehicles_model AS vehicle_model, 
            vehicles.vehicles_plate AS vehicle_plate 
        FROM booking
        LEFT JOIN user ON booking.user_id = user.user_id
        LEFT JOIN vehicles ON booking.vehicles_id = vehicles.vehicles_id
        WHERE booking.booking_id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("s", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        echo "Booking not found.";
        exit();
    }
} else {
    echo "Invalid booking ID.";
    exit();
}

// Initialize success message variable
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $booking_datestart = $_POST['booking_datestart'];
        $booking_dateend = $_POST['booking_dateend'];
        $booking_timestart = $_POST['booking_timestart'];
        $booking_timeend = $_POST['booking_timeend'];
        $booking_destination = $_POST['booking_destination'];
        $booking_purpose = $_POST['booking_purpose'];
        $booking_status = $_POST['booking_status'];

        $update_sql = "
            UPDATE booking 
            SET 
                booking_datestart = ?, 
                booking_dateend = ?, 
                booking_timestart = ?, 
                booking_timeend = ?, 
                booking_destination = ?, 
                booking_purpose = ?, 
                booking_status = ?
            WHERE booking_id = ?";
        $update_stmt = $dbconn->prepare($update_sql);
        $update_stmt->bind_param(
            "ssssssss",
            $booking_datestart,
            $booking_dateend,
            $booking_timestart,
            $booking_timeend,
            $booking_destination,
            $booking_purpose,
            $booking_status,
            $booking_id
        );

        if ($update_stmt->execute()) {
            $success_message = "Booking updated successfully.";
        } else {
            echo "Error updating booking.";
        }
    } elseif (isset($_POST['delete'])) {
        $delete_sql = "DELETE FROM booking WHERE booking_id = ?";
        $delete_stmt = $dbconn->prepare($delete_sql);
        $delete_stmt->bind_param("s", $booking_id);

        if ($delete_stmt->execute()) {
            $success_message = "Booking deleted successfully.";
            header("Location: viewbookinghistory.php?success=" . urlencode($success_message));
            exit();
        } else {
            echo "Error deleting booking.";
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
    <link rel="stylesheet" href="editbookinghistorystyle.css">
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

        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            const body = document.body;
            popup.style.display = 'none';
            body.classList.remove('modal-open');
        }

        function redirectTo(url) {
            window.location.href = url;
        }

        window.onload = function() {
            const successMessage = "<?php echo $success_message; ?>";
            if (successMessage) {
                showSuccessPopup(successMessage);
            }
        };
    </script>
    <style>
        /* Modal styles */
        #success-popup, #error-popup {
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
        #success-popup-message, #error-popup-message {
            margin-bottom: 20px;
        }
        #success-popup button, #error-popup button {
            padding: 10px;
            margin: 5px;
            background-color: #4070f4;
            color: #fff;
            width: 40%;
        }
        #error-popup button {
            background-color: #f44336; /* Red background for the error popup button */
        }
        #success-popup button:hover, #error-popup button:hover {
            background-color: rgb(49, 70, 187);
            cursor: pointer;
        }
        #error-popup button:hover {
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
                    <a href="homepageadmin.php">MajlisFleet</a>
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
                <form action="" method="POST" class="form-container" onsubmit="return validateBookingForm()">
                    <p><b>Booking ID</b></p>
                    <input type="text" value="<?php echo htmlspecialchars($booking['booking_id']); ?>" disabled>
                    <br>
                    <p><b>User</b></p>
                    <input type="text" value="<?php echo htmlspecialchars($booking['user_name']); ?>" disabled>
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
                    <p><b>Status</b></p>
                    <select name="booking_status" required>
                        <option value="Pending" <?php echo $booking['booking_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $booking['booking_status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $booking['booking_status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
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
    <div id="success-popup">
        <p id="success-popup-message"></p>
        <button onclick="redirectTo('homepageadmin.php')">Go to Homepage</button>
        <button onclick="redirectTo('viewbookinghistory.php')">View Booking History</button>
    </div>

    <!-- Modal for error messages -->
    <div id="error-popup">
        <p id="error-popup-message"></p>
        <button onclick="closePopup('error-popup')">Try Again</button>
    </div>
</body>
</html>