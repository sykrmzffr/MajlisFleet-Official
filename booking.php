<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: index.html");
    exit();
}

include('dbconn.php');

// Get the vehicle ID from the query string
if (isset($_GET['vehicle_id']) && is_numeric($_GET['vehicle_id'])) {
    $vehicle_id = $_GET['vehicle_id'];

    // Fetch vehicle details based on the vehicle ID
    $sql = "SELECT * FROM vehicles WHERE vehicles_id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $vehicle = $result->fetch_assoc();
    } else {
        echo "Vehicle not found.";
        exit();
    }
} else {
    echo "Invalid vehicle ID.";
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Layout</title>
    <link rel="stylesheet" href="bookingstyle.css">
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
            <div class="booking-container">

                <div class="vehicle-details-container">
                    <div class="img-vehicle">
                        <img src="<?php echo $vehicle['vehicles_img']; ?>" alt="Vehicle Image">
                        <p><?php echo strtoupper($vehicle['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                        <p><?php echo strtoupper($vehicle['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                        <p>Capacity: <?php echo strtoupper($vehicle['vehicles_capacity']); ?></p> <!-- PASSENGERS CAPACITY -->
                    </div>
                    <div class="img-driver">
                        <h4><b>DRIVER DETAILS</b></h4>
                        <img src="image/driver.jpg" alt="Driver Image">
                        <p><?php echo strtoupper($vehicle['vehicles_driver']); ?></p>
                    </div>
                </div>

                <div class="booking-form-container">

                    <form action="bookingprocess.php" method="POST">
                        <h2><b>Booking Details</b></h2>
                        <p>Date Start</p>
                        <input type="date" name="booking_datestart" required>
                        <p>Time Start</p>
                        <input type="time" name="booking_timestart" required>
                        <p>Date End</p>
                        <input type="date" name="booking_dateend" required>
                        <p>Time End</p>
                        <input type="time" name="booking_timeend" required>
                        <p>Destination</p>
                        <input type="text" name="booking_destination" required style="height: 50px;">
                        <p>Purpose</p>
                        <input type="text" name="booking_purpose" required style="height: 50px;">
                        <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($car_id); ?>"> <!-- Hidden car ID -->
                        <button type="submit">SUBMIT</button>
                    </form>



                </div>
            </div>
        </main>
        
        <footer>
            <p>Footer Content</p>
        </footer>
    
</body>
</html>

