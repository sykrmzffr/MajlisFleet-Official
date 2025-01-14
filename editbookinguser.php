<?php
session_start();

// Redirect if not logged in as user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: index.php");
    exit();
}

include('dbconn.php');

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
            echo "Booking updated successfully.";
        } else {
            echo "Error updating booking.";
        }
    } elseif (isset($_POST['delete'])) {
        $delete_sql = "DELETE FROM booking WHERE booking_id = ? AND user_id = ?";
        $delete_stmt = $dbconn->prepare($delete_sql);
        $delete_stmt->bind_param("ss", $booking_id, $user_id);

        if ($delete_stmt->execute()) {
            echo "Booking deleted successfully.";
            header("Location: bookinghistoryuser.php");
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
    <link rel="stylesheet" href="editbookinguserstyle.css">
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
        <p>Footer Content</p>
    </footer>
</body>
</html>
