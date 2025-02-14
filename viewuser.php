<?php
session_start();

// Ensure only admin has access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Handle search functionality
$search_query = "";
if (isset($_GET['search-bar']) && !empty($_GET['search-bar'])) {
    $search_term = "%" . $dbconn->real_escape_string($_GET['search-bar']) . "%";
    $search_query = "
        WHERE user_id LIKE '$search_term' 
        OR user_fullname LIKE '$search_term' 
        OR user_type LIKE '$search_term' 
        OR user_unit LIKE '$search_term'";
}

// Handle sorting functionality
$sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'user_id';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'ASC' ? 'ASC' : 'DESC';

// Fetch all users or filtered users with sorting
$query = "SELECT * FROM user $search_query ORDER BY $sort_column $sort_order";
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
    <title>Users List</title>
    <link rel="stylesheet" href="viewuserstyle.css">
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
                <a href="adduser.php">
                    <button>Add New User</button>
                </a>
                <form method="GET" action="" style="display: inline;">
                    <input type="text" name="search-bar" placeholder="Search by ID, Name, Type, or Unit" value="<?php echo isset($_GET['search-bar']) ? htmlspecialchars($_GET['search-bar']) : ''; ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="list-table">
                <table>
                    <tr id="row-first">
                        <th style="width:15%">
                            <a href="?sort_column=user_id&sort_order=<?php echo $next_sort_order; ?>">
                                User ID <?php echo get_sort_icon('user_id', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:35%">
                            <a href="?sort_column=user_fullname&sort_order=<?php echo $next_sort_order; ?>">
                                Full Name <?php echo get_sort_icon('user_fullname', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:15%">
                            <a href="?sort_column=user_type&sort_order=<?php echo $next_sort_order; ?>">
                                User Type <?php echo get_sort_icon('user_type', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:25%">
                            <a href="?sort_column=user_unit&sort_order=<?php echo $next_sort_order; ?>">
                                Unit <?php echo get_sort_icon('user_unit', $sort_column, $sort_order); ?>
                            </a>
                        </th>
                        <th style="width:10%">Action</th>
                    </tr>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_unit']); ?></td>
                                <td>
                                    <a href="edituser.php?user_id=<?php echo urlencode($row['user_id']); ?>">
                                        <button>Edit</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No users found.</td>
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

<?php
$result->free();
$dbconn->close();
?>