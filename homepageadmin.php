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

if ($user_type !== 'admin') {
    // Redirect to homepage if user is not an admin
    header("Location: homepage.php");
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
    <title>MajlisFleet Admin</title>
    <link rel="stylesheet" href="homepagestyle.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
</head>
<body>
    <div class="navbar">
        <div class="nav-items">
            <div class="nav-logo">
                <img src="image/logo.png" alt="">
                <a href="homepageadmin.php">MajlisFleet</a>
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
        <div class="welcome-text">
            <h1>Welcome, <?php echo $row['user_fullname']; ?>!</h1>
        </div>

        <div class="list-pages">
            <div class="card-pages">
                <div class="card">
                    <h3>View User List</h3>
                    <a href="viewuser.php">
                        <button>Click Here!</button>
                    </a>
                </div>
                <div class="card">
                    <h3>View Registered Vehicles</h3>
                    <a href="viewvehicles.php">
                        <button>Click Here!</button>
                    </a>
                </div>
                <div class="card">
                    <h3>View Registered Drivers</h3>
                    <a href="viewdriver.php">
                        <button>Click Here!</button>
                    </a>
                </div>
                <div class="card">
                    <h3>View Bookings History</h3>
                    <a href="viewbookinghistory.php">
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
