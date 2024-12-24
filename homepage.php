<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: index.html");
    exit();
}

include('dbconn.php');

$user_id = $_SESSION['user_id'];
//$user_type = $_SESSION['user_privilege'];

// User profile container
// Fetch user information from the database
$sql = "SELECT * FROM user WHERE user_id = '$user_id'";
$result = mysqli_query($dbconn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    // Error if user data is not found
    echo "Error: User data not found.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="homepagestyle.css">
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
        <a href="booking.php">Booking page</a>
        <a href="bookinglist.php">Booking list page</a>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>

</body>
</html>