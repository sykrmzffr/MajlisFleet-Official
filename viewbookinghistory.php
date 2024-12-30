<?php
session_start();

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Fetch booking history with user and vehicle details
$query = "
    SELECT 
        booking.booking_id,
        booking.user_id,
        booking.vehicles_id,
        booking.booking_datestart,
        booking.booking_dateend,
        booking.booking_timestart,
        booking.booking_timeend,
        booking.booking_destination,
        booking.booking_purpose,
        booking.booking_status,
        booking.booking_timecreated,
        user.user_fullname AS user_name,
        vehicles.vehicles_model AS vehicle_model,
        vehicles.vehicles_plate AS vehicle_plate
    FROM booking
    LEFT JOIN user ON booking.user_id = user.user_id
    LEFT JOIN vehicles ON booking.vehicles_id = vehicles.vehicles_id
    ORDER BY booking.booking_timecreated DESC";
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
    <title>View Booking History</title>
    <link rel="stylesheet" href="viewbookinghistorystyle.css">
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
                <form method="GET" action="searchbookings.php" style="display: inline;">
                    <input type="text" name="search-bar" placeholder="Search">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="list-table">
                <table>
                    <tr id="row-first">
                        <th style="width:10%">Booking ID</th>
                        <th style="width:15%">User</th>
                        <th style="width:15%">Vehicle</th>
                        <th style="width:10%">Date Start</th>
                        <th style="width:10%">Date End</th>
                        <th style="width:10%">Time Start</th>
                        <th style="width:10%">Time End</th>
                        <th style="width:15%">Destination</th>
                        <th style="width:15%">Purpose</th>
                        <th style="width:10%">Status</th>
                        <th style="width:10%">Action</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['vehicle_model'] . " (" . $row['vehicle_plate'] . ")"); ?></td>
                            <td><?php echo htmlspecialchars($row['booking_datestart']); ?></td>
                            <td><?php echo htmlspecialchars($row['booking_dateend']); ?></td>
                            <td><?php echo htmlspecialchars($row['booking_timestart']); ?></td>
                            <td><?php echo htmlspecialchars($row['booking_timeend']); ?></td>
                            <td><?php echo htmlspecialchars($row['booking_destination']); ?></td>
                            <td><?php echo htmlspecialchars($row['booking_purpose']); ?></td>
                            <td><?php echo htmlspecialchars($row['booking_status']); ?></td>
                            <td>
                                <a href="editbookinghistory.php?booking_id=<?php echo $row['booking_id']; ?>">
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
