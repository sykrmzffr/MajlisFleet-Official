<?php
session_start();

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Generate next vehicle ID
$query = "SELECT MAX(vehicles_id) AS max_id FROM vehicles";
$result = $dbconn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $maxId = $row['max_id'];
    if ($maxId) {
        // Extract the numeric part and increment
        $nextId = "VH" . str_pad((int)substr($maxId, 2) + 1, 5, "0", STR_PAD_LEFT);
    } else {
        $nextId = "VH00001"; // Default starting ID
    }
} else {
    die("Error generating vehicle ID: " . $dbconn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $vehicle_id = $nextId;
    $vehicle_type = strtoupper(trim($_POST['vehicle_type']));
    $vehicle_model = strtoupper(trim($_POST['vehicle_model']));
    $vehicle_plate = strtoupper(trim($_POST['vehicle_plate']));
    $vehicle_capacity = strtoupper(trim($_POST['vehicle_capacity']));
    $driver_id = strtoupper(trim($_POST['vehicle_driver']));
    $vehicle_img = trim($_POST['vehicle_img']); // No uppercase conversion for images

    // Set default image if no image link is provided
    if (empty($vehicle_img)) {
        $vehicle_img = "https://i.ibb.co/xHMKJR8/noimageavailable.jpg";
    }

    // Insert into database
    $stmt = $dbconn->prepare("
        INSERT INTO vehicles 
        (vehicles_id, vehicles_type, vehicles_model, vehicles_plate, vehicles_capacity, driver_id, vehicles_img) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssss", 
        $vehicle_id, 
        $vehicle_type, 
        $vehicle_model, 
        $vehicle_plate, 
        $vehicle_capacity, 
        $driver_id, 
        $vehicle_img
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Vehicle added successfully!');
                window.location.href = 'viewvehicles.php';
              </script>";
    } else {
        echo "<script>alert('Error adding vehicle: " . $stmt->error . "');</script>";
    }    

    $stmt->close();
    $dbconn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle</title>
    <link rel="stylesheet" href="addvehiclestyle.css">
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
                    <div class="upper-half">
                        <div class="upper-left">
                            <p><b>Vehicle ID</b></p>
                            <input type="text" value="<?php echo $nextId; ?>" disabled>
                            <br>
                            <p><b>Model</b></p>
                            <input type="text" name="vehicle_model" required>
                            <br>
                            <p><b>Capacity</b></p>
                            <select name="vehicle_capacity" id="vehicle-capacity">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <br>
                        </div>
                        <div class="upper-right">
                            <p><b>Vehicle Type</b></p>
                            <select name="vehicle_type" id="vehicle-type">
                                <option value="car">Car</option>
                                <option value="van">Van</option>
                            </select>
                            <br>
                            <p><b>Plate Number</b></p>
                            <input type="text" name="vehicle_plate" required>
                            <br>
                            <p><b>Choose Driver</b></p>
                            <select name="vehicle_driver" id="vehicle-driver">
                                <!-- Populate driver options dynamically -->
                                <?php
                                $driver_query = "SELECT driver_id, driver_fullname FROM driver";
                                $driver_result = $dbconn->query($driver_query);
                                while ($driver_row = $driver_result->fetch_assoc()) {
                                    echo "<option value=\"" . htmlspecialchars($driver_row['driver_id']) . "\">" . htmlspecialchars($driver_row['driver_fullname']) . "</option>";
                                }
                                ?>
                            </select>
                            <br>
                        </div>
                    </div>
                    <div class="lower-half">
                        <p><b>Image Link (Optional)</b></p>
                        <input type="text" name="vehicle_img" id="img-link">
                    </div>
                </div>
                <div class="button-container">
                    <button type="submit" id="add">ADD</button>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>
</body>
</html>
