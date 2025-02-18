<?php
session_start();

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Handle search functionality
$search_query = "1=1"; // Default query to fetch all records
if (isset($_GET['search-bar']) && !empty($_GET['search-bar'])) {
    $search_term = "%" . $dbconn->real_escape_string($_GET['search-bar']) . "%";
    $search_query .= " AND (system_logs.description LIKE '$search_term'
        OR user.user_fullname LIKE '$search_term'
        OR system_logs.object_type LIKE '$search_term'
        OR system_logs.object_id LIKE '$search_term'
        OR system_logs.event_type LIKE '$search_term')";
}

// Handle filter functionality
$filter_query = "";
if (isset($_GET['filter_event_type']) && !empty($_GET['filter_event_type'])) {
    $filter_event_type = $dbconn->real_escape_string($_GET['filter_event_type']);
    $filter_query .= " AND system_logs.event_type = '$filter_event_type'";
}
if (isset($_GET['filter_object_type']) && !empty($_GET['filter_object_type'])) {
    $filter_object_type = $dbconn->real_escape_string($_GET['filter_object_type']);
    $filter_query .= " AND system_logs.object_type = '$filter_object_type'";
}
if (isset($_GET['filter_date_from']) && !empty($_GET['filter_date_from'])) {
    $filter_date_from = $dbconn->real_escape_string($_GET['filter_date_from']);
    $filter_query .= " AND system_logs.event_datetime >= '$filter_date_from'";
}
if (isset($_GET['filter_date_to']) && !empty($_GET['filter_date_to'])) {
    $filter_date_to = $dbconn->real_escape_string($_GET['filter_date_to']);
    $filter_query .= " AND system_logs.event_datetime <= '$filter_date_to'";
}

// Handle sorting functionality
$sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'log_id';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'ASC' ? 'ASC' : 'DESC';

// Fetch log history with optional search filtering, additional filters, and sorting
$query = "
    SELECT 
        system_logs.log_id,
        system_logs.user_id,
        system_logs.event_type,
        system_logs.description,
        system_logs.object_type,
        system_logs.object_id,
        system_logs.event_datetime,
        user.user_fullname AS user_name
    FROM system_logs
    LEFT JOIN user ON system_logs.user_id = user.user_id
    WHERE $search_query
    $filter_query
    ORDER BY $sort_column $sort_order";
$result = $dbconn->query($query);

if (!$result) {
    die("Query failed: " . $dbconn->error);
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
    <title>Log History</title>
    <link rel="stylesheet" href="viewloghistorystyle.css">
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
            <div class="filter-container">
                <form method="GET" action="" style="display: flex; justify-content: space-between; width: 100%;">
                    <div>
                        <label for="search-bar">Search:</label>
                        <input type="text" name="search-bar" placeholder="Description, User, Object Type, etc." value="<?php echo isset($_GET['search-bar']) ? htmlspecialchars($_GET['search-bar']) : ''; ?>">
                    </div>
                    <div>
                        <label for="filter_event_type">Event Type:</label>
                        <select name="filter_event_type" id="filter_event_type">
                            <option value="">All</option>
                            <option value="LOGIN SUCCESS" <?php echo (isset($_GET['filter_event_type']) && $_GET['filter_event_type'] === 'LOGIN SUCCESS') ? 'selected' : ''; ?>>Login Success</option>
                            <option value="LOGOUT" <?php echo (isset($_GET['filter_event_type']) && $_GET['filter_event_type'] === 'LOGOUT') ? 'selected' : ''; ?>>Log Out</option>
                            <option value="CREATE" <?php echo (isset($_GET['filter_event_type']) && $_GET['filter_event_type'] === 'CREATE') ? 'selected' : ''; ?>>Create</option>
                            <option value="UPDATE" <?php echo (isset($_GET['filter_event_type']) && $_GET['filter_event_type'] === 'UPDATE') ? 'selected' : ''; ?>>Update</option>
                            <option value="DELETE" <?php echo (isset($_GET['filter_event_type']) && $_GET['filter_event_type'] === 'DELETE') ? 'selected' : ''; ?>>Delete</option>
                            <!-- Add more event types here if necessary -->
                        </select>
                    </div>
                    <div>
                        <label for="filter_object_type">Object Type:</label>
                        <select name="filter_object_type" id="filter_object_type">
                            <option value="">All</option>
                            <option value="user" <?php echo (isset($_GET['filter_object_type']) && $_GET['filter_object_type'] === 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="vehicle" <?php echo (isset($_GET['filter_object_type']) && $_GET['filter_object_type'] === 'vehicle') ? 'selected' : ''; ?>>Vehicle</option>
                            <option value="driver" <?php echo (isset($_GET['filter_object_type']) && $_GET['filter_object_type'] === 'driver') ? 'selected' : ''; ?>>Driver</option>
                            <option value="booking" <?php echo (isset($_GET['filter_object_type']) && $_GET['filter_object_type'] === 'booking') ? 'selected' : ''; ?>>Booking</option>
                            <!-- Add more object types here if necessary -->
                        </select>
                    </div>
                    <div>
                        <label for="filter_date_from">Date From:</label>
                        <input type="date" name="filter_date_from" id="filter_date_from" value="<?php echo isset($_GET['filter_date_from']) ? htmlspecialchars($_GET['filter_date_from']) : ''; ?>">
                    </div>
                    <div>
                        <label for="filter_date_to">Date To:</label>
                        <input type="date" name="filter_date_to" id="filter_date_to" value="<?php echo isset($_GET['filter_date_to']) ? htmlspecialchars($_GET['filter_date_to']) : ''; ?>">
                    </div>
                    <div>
                        <button type="submit">Apply</button>
                        <a href="viewloghistory.php" class="reset-btn">Reset</a>
                    </div>
                </form>
            </div>
            <div class="list-table">
                <table>
                    <tr id="row-first">
                        <th style="width:10%">
                            <a href="?sort_column=log_id&sort_order=<?php echo $next_sort_order; ?>">
                                Log ID <?php echo get_sort_icon('log_id', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:15%">
                            <a href="?sort_column=user_name&sort_order=<?php echo $next_sort_order; ?>">
                                User <?php echo get_sort_icon('user_name', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:15%">
                            <a href="?sort_column=event_type&sort_order=<?php echo $next_sort_order; ?>">
                                Event Type <?php echo get_sort_icon('event_type', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:20%">
                            <a href="?sort_column=description&sort_order=<?php echo $next_sort_order; ?>">
                                Description <?php echo get_sort_icon('description', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:10%">
                            <a href="?sort_column=object_type&sort_order=<?php echo $next_sort_order; ?>">
                                Object Type <?php echo get_sort_icon('object_type', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:10%">
                            <a href="?sort_column=object_id&sort_order=<?php echo $next_sort_order; ?>">
                                Object ID <?php echo get_sort_icon('object_id', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:20%">
                            <a href="?sort_column=event_datetime&sort_order=<?php echo $next_sort_order; ?>">
                                Event Time <?php echo get_sort_icon('event_datetime', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                    </tr>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['log_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['object_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['object_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_datetime']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No results found.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2025 Majlis Daerah Raub. All rights reserved.</p>
    </footer>
</body>
</html>