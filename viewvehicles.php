<?php
session_start();

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Handle search functionality
$search_query = "";
if (isset($_GET['search-bar']) && !empty($_GET['search-bar'])) {
    $search_term = "%" . $dbconn->real_escape_string($_GET['search-bar']) . "%";
    $search_query = "
        WHERE vehicles.vehicles_id LIKE '$search_term' 
        OR vehicles.vehicles_type LIKE '$search_term' 
        OR vehicles.vehicles_model LIKE '$search_term' 
        OR vehicles.vehicles_plate LIKE '$search_term' 
        OR driver.driver_fullname LIKE '$search_term'";
}

// Fetch vehicles with driver names and optional search filtering
$query = "
    SELECT 
        vehicles.vehicles_id, 
        vehicles.vehicles_type, 
        vehicles.vehicles_model, 
        vehicles.vehicles_plate, 
        vehicles.vehicles_capacity, 
        vehicles.vehicles_status,
        driver.driver_fullname AS driver_name 
    FROM vehicles 
    LEFT JOIN driver ON vehicles.driver_id = driver.driver_id 
    $search_query 
    ORDER BY vehicles.vehicles_id DESC";
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
    <title>Vehicles List</title>
    <link rel="stylesheet" href="viewvehiclestyle.css">
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
        <a href="homepageadmin.php"><< Back to Previous Page</a>
        <div class="view-container">
            <div class="view-btn">
                <button onclick="window.location.href='addvehicle.php'">Add New Vehicle</button>
                <form method="GET" action="" style="display: inline;">
                    <input type="text" name="search-bar" placeholder="Search by ID, Type, Model, Plate, or Driver" value="<?php echo isset($_GET['search-bar']) ? htmlspecialchars($_GET['search-bar']) : ''; ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="list-table">
                <table>
                    <tr id="row-first">
                        <th style="width:12%">Vehicle ID</th>
                        <th style="width:15%">Plate</th>
                        <th style="width:20%">Model</th>
                        <th style="width:10%">Type</th>
                        <th style="width:15%">Status</th>
                        <th style="width:20%">Driver Name</th>
                        <th style="width:10%">Action</th>
                    </tr>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['vehicles_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['vehicles_plate']); ?></td>
                                <td><?php echo htmlspecialchars($row['vehicles_model']); ?></td>
                                <td><?php echo htmlspecialchars($row['vehicles_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['vehicles_status']); ?></td>
                                <td><?php echo htmlspecialchars($row['driver_name'] ?? 'No Driver Assigned'); ?></td>
                                <td>
                                    <a href="editvehicle.php?vehicle_id=<?php echo urlencode($row['vehicles_id']); ?>">
                                        <button>Edit</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No vehicles found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>
    
    <footer>
        <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$result->free();
$dbconn->close();
?>
