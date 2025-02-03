<?php
// Set the default timezone to match your local timezone
date_default_timezone_set('Asia/Kuala_Lumpur'); // Change this to your timezone

// Check if session is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout duration (in seconds)
$session_timeout = 600; // 10 minutes

// Check if the session variable for last activity time is set
if (isset($_SESSION['last_activity'])) {
    // Calculate the session lifetime
    $session_lifetime = time() - $_SESSION['last_activity'];

    // Check if the session has expired
    if ($session_lifetime > $session_timeout) {
        // Session has expired, destroy the session and log the user out
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// Update the last activity time
$_SESSION['last_activity'] = time();

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
    $current_datetime = date('Y-m-d H:i:s');

    // Update the status of bookings whose end date and time have passed and are currently "Pending"
    $update_sql = "
        UPDATE booking 
        SET booking_status = 'Expired' 
        WHERE CONCAT(booking_dateend, ' ', booking_timeend) < ? AND booking_status = 'Pending'
    ";

    $stmt = $dbconn->prepare($update_sql);
    if (!$stmt) {
        die("Preparation failed: " . $dbconn->error);
    }

    $stmt->bind_param("s", $current_datetime);

    if ($stmt->execute()) {
        
    } else {
        echo "Error updating booking statuses: " . $stmt->error . "<br>";
    }

    $stmt->close();
}

// Call the function to update expired bookings
updateExpiredBookings($dbconn);
?>