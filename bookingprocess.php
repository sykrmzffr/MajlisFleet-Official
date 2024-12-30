<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include('dbconn.php');

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $user_id = $_SESSION['user_id']; // User ID from the session
    $vehicles_id = $_POST['vehicles_id']; // Vehicle ID from the hidden input
    $booking_datestart = $_POST['booking_datestart'];
    $booking_dateend = $_POST['booking_dateend'];
    $booking_timestart = $_POST['booking_timestart'];
    $booking_timeend = $_POST['booking_timeend'];
    $booking_destination = mysqli_real_escape_string($dbconn, $_POST['booking_destination']);
    $booking_purpose = mysqli_real_escape_string($dbconn, $_POST['booking_purpose']);

    // Booking status is set to 'pending approval'
    $booking_status = 'Pending Approval';

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
        echo "Booking submitted successfully.";
        header("Location: bookingsuccess.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($dbconn);
    }
}

mysqli_close($dbconn);
?>
