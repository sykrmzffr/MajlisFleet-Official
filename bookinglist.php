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

// Fetch all vehicles along with the driver's full name
$sql = "
    SELECT vehicles.*, driver.driver_fullname 
    FROM vehicles
    LEFT JOIN driver ON vehicles.driver_id = driver.driver_id
";
$result = mysqli_query($dbconn, $sql);

$kereta = [];
$van = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['vehicles_type'] === 'CAR') {
            $kereta[] = $row;
        } elseif ($row['vehicles_type'] === 'VAN') {
            $van[] = $row;
        }
    }
} else {
    echo "No vehicle data found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles List</title>
    <link rel="stylesheet" href="bookingliststyle.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
</head>
<body>
    <div class="container">
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
        <a href="homepage.php"><< Back to Previous Page</a>
            <div class="welcome-text">
                <h1>Choose Any Vehicles to Start Booking</h1>
            </div>

            <section class="list-cars">
                <p>Cars</p>
                <div class="card-cars">
                    <?php foreach ($kereta as $car): ?>
                    <div class="card">
                        <a href="booking.php?vehicle_id=<?php echo $car['vehicles_id']; ?>">
                            <img src="<?php echo $car['vehicles_img']; ?>" alt="Car Image">
                            <p><?php echo strtoupper ($car['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                            <p><?php echo strtoupper ($car['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                            <p><?php echo strtoupper($car['driver_fullname']); ?></p> <!-- DRIVER -->
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <section class="list-vans">
                <p>Van</p>
                <div class="card-vans">
                    <?php foreach ($van as $v): ?>
                    <div class="card">
                    <a href="booking.php?vehicle_id=<?php echo $v['vehicles_id']; ?>">
                            <img src="<?php echo $v['vehicles_img']; ?>" alt="Van Image">
                            <p><?php echo strtoupper ($v['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                            <p><?php echo strtoupper ($v['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                            <p><?php echo strtoupper($v['driver_fullname']); ?></p> <!-- DRIVER -->
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
        
        <footer>
            <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>