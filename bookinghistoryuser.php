<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: index.php");
    exit();
}

include('dbconn.php');

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the booking history of the logged-in user
$search_term = '';
if (isset($_GET['search-bar'])) {
    $search_term = $_GET['search-bar'];
    $sql = "
        SELECT 
            booking.*, 
            vehicles.vehicles_model AS vehicle_model, 
            vehicles.vehicles_plate AS vehicle_plate
        FROM booking
        LEFT JOIN vehicles ON booking.vehicles_id = vehicles.vehicles_id
        WHERE booking.user_id = ? AND (
            booking.booking_id LIKE ? OR 
            vehicles.vehicles_model LIKE ? OR 
            vehicles.vehicles_plate LIKE ? OR 
            booking.booking_destination LIKE ? OR 
            booking.booking_purpose LIKE ? OR 
            booking.booking_status LIKE ?)
        ORDER BY booking.booking_timecreated DESC";
    $stmt = $dbconn->prepare($sql);
    $like_term = "%$search_term%";
    $stmt->bind_param("sssssss", $user_id, $like_term, $like_term, $like_term, $like_term, $like_term, $like_term);
} else {
    $sql = "
        SELECT 
            booking.*, 
            vehicles.vehicles_model AS vehicle_model, 
            vehicles.vehicles_plate AS vehicle_plate
        FROM booking
        LEFT JOIN vehicles ON booking.vehicles_id = vehicles.vehicles_id
        WHERE booking.user_id = ?
        ORDER BY booking.booking_timecreated DESC";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("s", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking History</title>
    <link rel="stylesheet" href="bookinghistoryuserstyle.css">
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
        <a href="homepage.php"><< Back to Previous Page</a>
        <div class="view-container">
            <div class="view-btn">
                <form method="GET" action="bookinghistoryuser.php" style="display: inline;">
                    <input type="text" name="search-bar" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="list-table">
                <table>
                    <tr id="row-first">
                        <th style="width:10%">Booking ID</th>
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
                                    <?php if ($row['booking_status'] === 'Pending'): ?>
                                        <a href="editbookinguser.php?booking_id=<?php echo $row['booking_id']; ?>">
                                            <button>Edit</button>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center;">No bookings found.</td>
                        </tr>
                    <?php endif; ?>

                </table>
            </div>
        </div>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>
</body>
</html>
