<?php
session_start();

include('dbconn.php'); // Include the database connection
include('logger.php'); // Include the logger file

// Capture the user ID before unsetting session variables
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

session_unset(); // Unset session variables
session_destroy(); // Destroy the session

if ($user_id) {
    // Log the logout event
    log_event($dbconn, 'LOGOUT', $user_id, 'User logged out successfully', 'user', $user_id);
}

header("Location: index.php"); // Redirect to login page
exit();
?>