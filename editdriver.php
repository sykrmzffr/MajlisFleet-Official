<?php
session_start();

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('dbconn.php');

// Get driver_id from the URL
if (!isset($_GET['driver_id'])) {
    die("Driver ID not specified.");
}

$driver_id = $_GET['driver_id'];

// Fetch driver details from the database
$query = "SELECT driver_id, driver_fullname FROM driver WHERE driver_id = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("s", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

if (!$driver) {
    die("Driver not found.");
}

$stmt->close();

// Handle form submission for updating or deleting the driver
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $driver_fullname = trim($_POST['driver_fullname']);

        // Update driver details in the database
        $update_query = "UPDATE driver SET driver_fullname = ? WHERE driver_id = ?";
        $update_stmt = $dbconn->prepare($update_query);
        $update_stmt->bind_param("ss", $driver_fullname, $driver_id);

        if ($update_stmt->execute()) {
            echo "<script>
                    alert('Driver updated successfully!');
                    window.location.href = 'viewdriver.php';
                  </script>";
        } else {
            echo "<script>alert('Error updating driver: " . $update_stmt->error . "');</script>";
        }

        $update_stmt->close();
    } elseif (isset($_POST['delete'])) {
        // Delete driver from the database
        $delete_query = "DELETE FROM driver WHERE driver_id = ?";
        $delete_stmt = $dbconn->prepare($delete_query);
        $delete_stmt->bind_param("s", $driver_id);

        if ($delete_stmt->execute()) {
            echo "<script>
                    alert('Driver deleted successfully!');
                    window.location.href = 'viewdriver.php';
                  </script>";
        } else {
            echo "<script>alert('Error deleting driver: " . $delete_stmt->error . "');</script>";
        }

        $delete_stmt->close();
    }
}

$dbconn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver</title>
    <link rel="stylesheet" href="editdriverstyle.css">
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
        <div class="edit-container">
            <form method="POST">
                <div class="form-container">
                    <p><b>Driver ID</b></p>
                    <input type="text" value="<?php echo htmlspecialchars($driver['driver_id']); ?>" disabled>
                    <p><b>Driver Full Name</b></p>
                    <input type="text" name="driver_fullname" value="<?php echo htmlspecialchars($driver['driver_fullname']); ?>" required>
                </div>
                <div class="button-container">
                    <button type="submit" name="update" id="update">UPDATE</button>
                    <button type="submit" name="delete" id="delete" onclick="return confirm('Are you sure you want to delete this driver?');">DELETE</button>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>
</body>
</html>
