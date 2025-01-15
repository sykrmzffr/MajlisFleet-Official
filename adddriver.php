<?php
session_start();

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Generate a new driver_id
$query = "SELECT driver_id FROM driver ORDER BY driver_id DESC LIMIT 1";
$result = $dbconn->query($query);

if ($result && $result->num_rows > 0) {
    $last_id = $result->fetch_assoc()['driver_id'];
    $numeric_part = intval(substr($last_id, 2)); // Extract numeric part of driver_id
    $new_id = 'DR' . str_pad($numeric_part + 1, 5, '0', STR_PAD_LEFT); // Increment and format
} else {
    $new_id = 'DR00001'; // Default for first driver
}

// Handle form submission for adding a driver
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_fullname = trim($_POST['driver_fullname']);

    // Capitalize each word's first letter
    $driver_fullname = ucwords(strtolower($driver_fullname));

    // Insert new driver into the database
    $insert_query = "INSERT INTO driver (driver_id, driver_fullname) VALUES (?, ?)";
    $stmt = $dbconn->prepare($insert_query);
    $stmt->bind_param("ss", $new_id, $driver_fullname);

    if ($stmt->execute()) {
        echo "<script>
                alert('Driver added successfully!');
                window.location.href = 'viewdriver.php';
              </script>";
    } else {
        echo "<script>alert('Error adding driver: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$dbconn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Driver</title>
    <link rel="stylesheet" href="adddriverstyle.css">
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
                    <input type="text" value="<?php echo htmlspecialchars($new_id); ?>" disabled>
                    <p><b>Driver Full Name</b></p>
                    <input type="text" name="driver_fullname" required>
                </div>
                <div class="button-container">
                    <button type="submit" name="add" onclick="return confirm('Are you sure you want to add this driver?');">ADD</button>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>
</body>
</html>
