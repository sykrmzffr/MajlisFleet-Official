<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Include database connection
include('dbconn.php');

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form inputs
    $user_id = $_SESSION['user_id']; // User ID from the session
    $car_id = $_POST['car_id']; // Car ID from the hidden input
    $booking_datestart = $_POST['booking_datestart'];
    $booking_dateend = $_POST['booking_dateend'];
    $booking_timestart = $_POST['booking_timestart'];
    $booking_timeend = $_POST['booking_timeend'];
    $booking_destination = mysqli_real_escape_string($dbconn, $_POST['booking_destination']);
    $booking_purpose = mysqli_real_escape_string($dbconn, $_POST['booking_purpose']);

    // Booking status is set to 'pending approval'
    $booking_status = 'pending approval';

    // Insert data into the `booking` table
    $sql = "INSERT INTO booking (user_id, car_id, booking_datestart, booking_dateend, booking_timestart, booking_timeend, booking_destination, booking_purpose, booking_status) 
            VALUES ('$user_id', '$car_id', '$booking_datestart', '$booking_dateend', '$booking_timestart', '$booking_timeend', '$booking_destination', '$booking_purpose', '$booking_status')";

    if (mysqli_query($dbconn, $sql)) {
        // Redirect to success page or display a success message
        echo "Booking submitted successfully.";
        header("Location: bookingsuccess.php");
        exit();
    } else {
        // Display error message
        echo "Error: " . $sql . "<br>" . mysqli_error($dbconn);
    }
}

// Close database connection
mysqli_close($dbconn);
?>
