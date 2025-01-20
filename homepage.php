<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: index.php");
    exit();
}

include('dbconn.php');

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type']; // Get user type from session

if ($user_type == 'admin') {
    // Redirect to homepage if user is not an admin
    header("Location: homepageadmin.php");
    exit();
}

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
    <title>MajlisFleet</title>
    <link rel="stylesheet" href="homepagestyle.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
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

        <div class="list-pages">
            <div class="card-pages">
                <div class="card">
                    <h3>View Vehicles List</h3>
                    <a href="bookinglist.php">
                        <button>Click Here!</button>
                    </a>
                </div>
                <div class="card">
                    <h3>View Booking History</h3>
                    <a href="bookinghistoryuser.php">
                        <button>Click Here!</button>
                    </a>
                </div>
            </div>
        </div>
        
    </main>
    
    <footer>
        <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
    </footer>
</body>
</html>
