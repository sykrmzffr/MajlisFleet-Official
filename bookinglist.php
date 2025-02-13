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

$passenger_vehicles = [];
$light_duty_vehicles = [];
$heavy_duty_vehicles = [];
$agricultural_vehicles = [];
$two_wheeled_vehicles = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['vehicles_type']) {
            case 'SEDAN CAR':
            case 'EXECUTIVE CAR':
            case 'MPV':
                $passenger_vehicles[] = $row;
                break;
            case 'VAN':
            case 'PICK-UP TRUCK':
                $light_duty_vehicles[] = $row;
                break;
            case '4WD':
            case 'LORRY':
            case 'LORRY DOUBLE CAB':
            case 'LORRY TANK':
            case 'LORRY SKYLIFT':
                $heavy_duty_vehicles[] = $row;
                break;
            case 'TRACTOR':
            case 'BACKHOE':
                $agricultural_vehicles[] = $row;
                break;
            case 'MOTORCYCLE':
                $two_wheeled_vehicles[] = $row;
                break;
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

            <!-- Passenger Vehicles -->
            <section class="list-vehicles">
                <h3>Passenger Vehicles</h3>
                <div class="card-container">
                    <?php if (!empty($passenger_vehicles)): ?>
                        <?php foreach ($passenger_vehicles as $vehicle): ?>
                        <div class="card">
                            <a href="booking.php?vehicle_id=<?php echo $vehicle['vehicles_id']; ?>">
                                <img src="<?php echo $vehicle['vehicles_img']; ?>" alt="Vehicle Image">
                                <p><?php echo strtoupper($vehicle['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                                <p><?php echo strtoupper($vehicle['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                                <p><?php echo strtoupper($vehicle['driver_fullname']); ?></p> <!-- DRIVER -->
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="null-text">
                            <p>No vehicle available yet!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Light-Duty Vehicles -->
            <section class="list-vehicles">
                <h3>Light-Duty Vehicles</h3>
                <div class="card-container">
                    <?php if (!empty($light_duty_vehicles)): ?>
                        <?php foreach ($light_duty_vehicles as $vehicle): ?>
                        <div class="card">
                            <a href="booking.php?vehicle_id=<?php echo $vehicle['vehicles_id']; ?>">
                                <img src="<?php echo $vehicle['vehicles_img']; ?>" alt="Vehicle Image">
                                <p><?php echo strtoupper($vehicle['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                                <p><?php echo strtoupper($vehicle['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                                <p><?php echo strtoupper($vehicle['driver_fullname']); ?></p> <!-- DRIVER -->
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="null-text">
                            <p>No vehicle available yet!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Heavy-Duty Vehicles -->
            <section class="list-vehicles">
                <h3>Heavy-Duty Vehicles</h3>
                <div class="card-container">
                    <?php if (!empty($heavy_duty_vehicles)): ?>
                        <?php foreach ($heavy_duty_vehicles as $vehicle): ?>
                        <div class="card">
                            <a href="booking.php?vehicle_id=<?php echo $vehicle['vehicles_id']; ?>">
                                <img src="<?php echo $vehicle['vehicles_img']; ?>" alt="Vehicle Image">
                                <p><?php echo strtoupper($vehicle['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                                <p><?php echo strtoupper($vehicle['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                                <p><?php echo strtoupper($vehicle['driver_fullname']); ?></p> <!-- DRIVER -->
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="null-text">
                            <p>No vehicle available yet!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Agricultural & Construction Vehicles -->
            <section class="list-vehicles">
                <h3>Agricultural & Construction Vehicles</h3>
                <div class="card-container">
                    <?php if (!empty($agricultural_vehicles)): ?>
                        <?php foreach ($agricultural_vehicles as $vehicle): ?>
                        <div class="card">
                            <a href="booking.php?vehicle_id=<?php echo $vehicle['vehicles_id']; ?>">
                                <img src="<?php echo $vehicle['vehicles_img']; ?>" alt="Vehicle Image">
                                <p><?php echo strtoupper($vehicle['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                                <p><?php echo strtoupper($vehicle['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                                <p><?php echo strtoupper($vehicle['driver_fullname']); ?></p> <!-- DRIVER -->
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="null-text">
                            <p>No vehicle available yet!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Two-Wheeled Vehicles -->
            <section class="list-vehicles">
                <h3>Two-Wheeled Vehicles</h3>
                <div class="card-container">
                    <?php if (!empty($two_wheeled_vehicles)): ?>
                        <?php foreach ($two_wheeled_vehicles as $vehicle): ?>
                        <div class="card">
                            <a href="booking.php?vehicle_id=<?php echo $vehicle['vehicles_id']; ?>">
                                <img src="<?php echo $vehicle['vehicles_img']; ?>" alt="Vehicle Image">
                                <p><?php echo strtoupper($vehicle['vehicles_model']); ?></p> <!-- VEHICLES MODEL -->
                                <p><?php echo strtoupper($vehicle['vehicles_plate']); ?></p> <!-- VEHICLES PLATE -->
                                <p><?php echo strtoupper($vehicle['driver_fullname']); ?></p> <!-- DRIVER -->
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="null-text">
                            <p>No vehicle available yet!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        
        <footer>
            <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>