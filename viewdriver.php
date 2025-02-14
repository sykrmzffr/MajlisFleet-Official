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
    $search_query = "WHERE driver_id LIKE '$search_term' OR driver_fullname LIKE '$search_term'";
}

// Handle sorting functionality
$sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'driver_id';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'ASC' ? 'ASC' : 'DESC';

// Fetch drivers from the database with optional search filtering and sorting
$query = "SELECT driver_id, driver_fullname FROM driver $search_query ORDER BY $sort_column $sort_order";
$result = $dbconn->query($query);

if (!$result) {
    die("Error fetching drivers: " . $dbconn->error);
}

// Toggle sort order for next click
$next_sort_order = $sort_order === 'ASC' ? 'DESC' : 'ASC';

// Function to get the appropriate sort icon
function get_sort_icon($column, $sort_column, $sort_order) {
    if ($column === $sort_column) {
        return $sort_order === 'ASC' ? '▲' : '▼';
    }
    return '⇅'; // Default icon indicating sortable
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers List</title>
    <link rel="stylesheet" href="viewdriverstyle.css">
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
                <button onclick="window.location.href='adddriver.php'">Add New Driver</button>
                <form method="GET" action="" style="display: inline;">
                    <input type="text" name="search-bar" placeholder="Search by Name or ID" value="<?php echo isset($_GET['search-bar']) ? htmlspecialchars($_GET['search-bar']) : ''; ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="list-table">
                <table>
                    <tr id="row-first">
                        <th style="width:15%">
                            <a href="?sort_column=driver_id&sort_order=<?php echo $next_sort_order; ?>">
                                Driver ID <?php echo get_sort_icon('driver_id', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:20%">
                            <a href="?sort_column=driver_fullname&sort_order=<?php echo $next_sort_order; ?>">
                                Driver Name <?php echo get_sort_icon('driver_fullname', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:10%">Action</th>
                    </tr>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['driver_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['driver_fullname'] ?? 'No Name'); ?></td>
                                <td>
                                    <a href="editdriver.php?driver_id=<?php echo urlencode($row['driver_id']); ?>">
                                        <button>Edit</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No drivers found.</td>
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