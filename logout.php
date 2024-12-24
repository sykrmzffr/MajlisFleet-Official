<?php
session_start();
session_unset(); // Unset session variables
session_destroy(); // Destroy the session
header("Location: index.html"); // Redirect to login page
exit();
?>
