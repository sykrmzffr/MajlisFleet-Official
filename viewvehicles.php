<?php
session_start();

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Fetch vehicles with driver names
$query = "
    SELECT 
        vehicles.vehicles_id, 
        vehicles.vehicles_type, 
        vehicles.vehicles_model, 
        vehicles.vehicles_plate, 
        vehicles.vehicles_capacity, 
        driver.driver_fullname AS driver_name 
    FROM vehicles 
    LEFT JOIN driver ON vehicles.driver_id = driver.driver_id";
$result = $dbconn->query($query);

if (!$result) {
    die("Query failed: " . $dbconn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Vehicles</title>
    <link rel="stylesheet" href="viewvehiclestyle.css">
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
        <div class="view-container">
            <div class="view-btn">
                <button onclick="window.location.href='addvehicle.php'">Add New Vehicle</button>
                <form method="GET" action="searchvehicles.php" style="display: inline;">
                    <input type="text" name="search-bar" placeholder="Search">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="list-table">
                <table>
                    <tr id="row-first">
                        <th style="width:15%">Vehicle ID</th>
                        <th style="width:20%">Type</th>
                        <th style="width:20%">Model</th>
                        <th style="width:15%">Plate</th>
                        <th style="width:10%">Capacity</th>
                        <th style="width:20%">Driver Name</th>
                        <th style="width:10%">Action</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['vehicles_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['vehicles_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['vehicles_model']); ?></td>
                            <td><?php echo htmlspecialchars($row['vehicles_plate']); ?></td>
                            <td><?php echo htmlspecialchars($row['vehicles_capacity']); ?></td>
                            <td><?php echo htmlspecialchars($row['driver_name'] ?? 'No Driver Assigned'); ?></td>
                            <td>
                                <a href="editvehicle.php?vehicle_id=<?php echo $row['vehicles_id']; ?>">
                                    <button>Edit</button>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>
</body>
</html>
