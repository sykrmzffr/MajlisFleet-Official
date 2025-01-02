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

    // Combine date and time for comparison
    $start_datetime = $booking_datestart . ' ' . $booking_timestart;
    $end_datetime = $booking_dateend . ' ' . $booking_timeend;
    $current_datetime = date('Y-m-d H:i:s');

    // Ensure start date and time are not later than end date and time
    if (strtotime($start_datetime) >= strtotime($end_datetime)) {
        $error_message = urlencode("Start date and time must be earlier than end date and time.");
        header("Location: booking.php?vehicle_id=$vehicles_id&error=$error_message");
        exit();
    }

    // Ensure start date and time are not before the current date and time
    if (strtotime($start_datetime) < strtotime($current_datetime)) {
        $error_message = urlencode("Start date and time must be later than current date and time.");
        header("Location: booking.php?vehicle_id=$vehicles_id&error=$error_message");
        exit();
    }

    // Check for booking clashes
    $clash_sql = "SELECT * FROM booking WHERE vehicles_id = ? AND 
                  ((? BETWEEN booking_datestart AND booking_dateend) OR 
                   (? BETWEEN booking_datestart AND booking_dateend) OR 
                   (booking_datestart BETWEEN ? AND ?) OR 
                   (booking_dateend BETWEEN ? AND ?))";

    $stmt = $dbconn->prepare($clash_sql);
    $stmt->bind_param("sssssss", $vehicles_id, $start_datetime, $end_datetime, $start_datetime, $end_datetime, $start_datetime, $end_datetime);
    $stmt->execute();
    $clash_result = $stmt->get_result();

    if ($clash_result && $clash_result->num_rows > 0) {
        $error_message = urlencode("Booking time clashes with an existing booking.");
        header("Location: booking.php?vehicle_id=$vehicles_id&error=$error_message");
        exit();
    }

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
        echo "Booking submitted successfully.";
        header("Location: bookingsuccess.php");
        exit();
    } else {
        $error_message = urlencode("Error: " . mysqli_error($dbconn));
        header("Location: booking.php?vehicle_id=$vehicles_id&error=$error_message");
        exit();
    }
}

mysqli_close($dbconn);
?>