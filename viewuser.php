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

// Fetch all users or filtered users
$query = "SELECT * FROM user $search_query ORDER BY user_id DESC";
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
    <title>View Users</title>
    <link rel="stylesheet" href="viewuserstyle.css">
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
                        <th style="width:15%">User ID</th>
                        <th style="width:35%">Full Name</th>
                        
                        <th style="width:15%">User Type</th>
                        <th style="width:25%">Unit</th>
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
                            <td colspan="6">No users found.</td>
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

<?php
$result->free();
$dbconn->close();
?>