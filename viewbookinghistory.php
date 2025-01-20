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
        WHERE booking.booking_id LIKE '$search_term'
        OR user.user_fullname LIKE '$search_term'
        OR vehicles.vehicles_model LIKE '$search_term'
        OR vehicles.vehicles_plate LIKE '$search_term'
        OR booking.booking_destination LIKE '$search_term'
        OR booking.booking_purpose LIKE '$search_term'
        OR booking.booking_status LIKE '$search_term'
    ";
}

// Fetch booking history with optional search filtering
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
    $search_query
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
    <title>Booking History</title>
    <link rel="stylesheet" href="viewbookinghistorystyle.css">
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
                <form method="GET" action="" style="display: inline;">
                    <input type="text" name="search-bar" placeholder="Search" value="<?php echo isset($_GET['search-bar']) ? htmlspecialchars($_GET['search-bar']) : ''; ?>">
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
                        <th style="width:15%">Time Created</th>
                        <th style="width:10%">Action</th>
                    </tr>
                    <?php if ($result->num_rows > 0): ?>
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
                                <td style="color: 
                                    <?php 
                                        if ($row['booking_status'] === 'Rejected') {
                                            echo '#f44040'; // Red for Rejected
                                        } elseif ($row['booking_status'] === 'Approved') {
                                            echo '#097969'; // Green for Approved
                                        } else {
                                            echo 'grey'; // Grey for Pending
                                        }
                                    ?>; font-weight: bold;">
                                    <?php echo htmlspecialchars($row['booking_status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['booking_timecreated']); ?></td>
                                <td>
                                    <a href="editbookinghistory.php?booking_id=<?php echo $row['booking_id']; ?>">
                                        <button>Edit</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12">No results found.</td>
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
