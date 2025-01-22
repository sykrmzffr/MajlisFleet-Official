<?php

/* php & mysqldb connection file */
$user = "root";             // mysqlusername
$pass = "";                 // mysqlpassword
$host = "localhost";        // server name or ipaddress
$dbname = "majlisfleet";    // your db name

$dbconn = mysqli_connect($host, $user, $pass, $dbname) or die(mysqli_error($dbconn));

if ($dbconn->connect_error) {
    die("Connection failed: " . $dbconn->connect_error);
}

function updateExpiredBookings($dbconn) {
    // Get the current date and time
    $current_date = date('Y-m-d H:i:s');

    // Update the status of bookings whose end date has passed and are not already marked as "Expired"
    $update_sql = "
        UPDATE booking 
        SET booking_status = 'Expired' 
        WHERE booking_dateend < ? AND booking_status != 'Expired'
    ";
    $stmt = $dbconn->prepare($update_sql);
    $stmt->bind_param("s", $current_date);

    if ($stmt->execute()) {
        return "Booking statuses updated successfully.";
    } else {
        return "Error updating booking statuses: " . $stmt->error;
    }

    $stmt->close();
}

?>