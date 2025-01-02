<?php
session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.html");
    exit();
}

include('dbconn.php');

// Fetch vehicle details based on vehicle ID from the query string
if (isset($_GET['vehicle_id'])) { // Ensure this matches the parameter name in the URL
    $vehicle_id = $_GET['vehicle_id'];

    $sql = "SELECT vehicles.*, driver.driver_fullname FROM vehicles
            LEFT JOIN driver ON vehicles.driver_id = driver.driver_id
            WHERE vehicles.vehicles_id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("s", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $vehicle = $result->fetch_assoc();
    } else {
        echo "Vehicle not found.";
        exit();
    }
} else {
    echo "Invalid vehicle ID.";
    exit();
}

// Initialize success message variable
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $vehicle_type = $_POST['vehicle_type'];
        $vehicle_model = $_POST['vehicle_model'];
        $vehicle_plate = $_POST['vehicle_plate'];
        $vehicle_capacity = $_POST['vehicle_capacity'];
        $vehicle_img = $_POST['vehicle_img'];
        $driver_id = $_POST['vehicle_driver'];

        $update_sql = "UPDATE vehicles SET vehicles_type = ?, vehicles_model = ?, vehicles_plate = ?, 
                       vehicles_capacity = ?, vehicles_img = ?, driver_id = ? WHERE vehicles_id = ?";
        $update_stmt = $dbconn->prepare($update_sql);
        $update_stmt->bind_param(
            "sssssss",
            $vehicle_type,
            $vehicle_model,
            $vehicle_plate,
            $vehicle_capacity,
            $vehicle_img,
            $driver_id,
            $vehicle_id
        );

        if ($update_stmt->execute()) {
            $success_message = "Vehicle updated successfully.";
        } else {
            echo "Error updating vehicle.";
        }
    } elseif (isset($_POST['delete'])) {
        $delete_sql = "DELETE FROM vehicles WHERE vehicles_id = ?";
        $delete_stmt = $dbconn->prepare($delete_sql);
        $delete_stmt->bind_param("s", $vehicle_id);

        if ($delete_stmt->execute()) {
            $success_message = "Vehicle deleted successfully.";
            header("Location: viewvehicles.php?success=" . urlencode($success_message));
            exit();
        } else {
            echo "Error deleting vehicle.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle</title>
    <link rel="stylesheet" href="editvehiclestyle.css">
    <script>
        function showPopup(message) {
            const popup = document.getElementById('success-popup');
            const popupMessage = document.getElementById('success-popup-message');
            const body = document.body;
            popupMessage.textContent = message;
            popup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function closePopup() {
            const popup = document.getElementById('success-popup');
            const body = document.body;
            popup.style.display = 'none';
            body.classList.remove('modal-open');
        }

        function redirectTo(url) {
            window.location.href = url;
        }

        window.onload = function() {
            const successMessage = "<?php echo $success_message; ?>";
            if (successMessage) {
                showPopup(successMessage);
            }
        };
    </script>
    <style>
        /* Modal styles */
        #success-popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        #success-popup-message {
            margin-bottom: 20px;
        }
        #success-popup button {
            padding: 10px;
            margin: 5px;
        }
        /* Background blur and disable styles */
        .modal-open {
            overflow: hidden;
        }
        .modal-open #blur-background {
            filter: blur(5px);
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div id="blur-background">
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
                <form action="" method="POST" class="form-container">
                    <div class="upper-half">
                        <div class="upper-left">
                            <p><b>Vehicle ID</b></p>
                            <input type="text" value="<?php echo htmlspecialchars($vehicle['vehicles_id']); ?>" disabled>
                            <br>
                            <p><b>Model</b></p>
                            <input type="text" name="vehicle_model" value="<?php echo htmlspecialchars($vehicle['vehicles_model']); ?>" required>
                            <br>
                            <p><b>Capacity</b></p>
                            <select name="vehicle_capacity" id="vehicle-capacity">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $vehicle['vehicles_capacity'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <br>
                        </div>
                        <div class="upper-right">
                            <p><b>Vehicle Type</b></p>
                            <select name="vehicle_type" id="vehicle-type">
                                <option value="CAR" <?php echo $vehicle['vehicles_type'] === 'CAR' ? 'selected' : ''; ?>>Car</option>
                                <option value="VAN" <?php echo $vehicle['vehicles_type'] === 'VAN' ? 'selected' : ''; ?>>Van</option>
                            </select>
                            <br>
                            <p><b>Plate Number</b></p>
                            <input type="text" name="vehicle_plate" value="<?php echo htmlspecialchars($vehicle['vehicles_plate']); ?>" required>
                            <br>
                            <p><b>Choose Driver</b></p>
                            <select name="vehicle_driver" id="vehicle-driver">
                                <?php
                                $driver_sql = "SELECT driver_id, driver_fullname FROM driver";
                                $driver_result = mysqli_query($dbconn, $driver_sql);
                                while ($driver = mysqli_fetch_assoc($driver_result)):
                                ?>
                                    <option value="<?php echo $driver['driver_id']; ?>" <?php echo $vehicle['driver_id'] === $driver['driver_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($driver['driver_fullname']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <br>
                        </div>
                    </div>
                    <div class="lower-half">
                        <p><b>Image Link (Optional)</b></p>
                        <input type="text" name="vehicle_img" id="img-link" value="<?php echo htmlspecialchars($vehicle['vehicles_img']); ?>">
                    </div>
                    <div class="button-container">
                        <button type="submit" name="update">UPDATE</button>
                        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this vehicle?');">DELETE</button>
                    </div>
                </form>
            </div>
        </main>
    
        <footer>
            <p>Footer Content</p>
        </footer>
    </div>

    <!-- Modal for success messages -->
    <div id="success-popup">
        <p id="success-popup-message"></p>
        <button onclick="redirectTo('homepageadmin.php')">Go to Homepage</button>
        <button onclick="redirectTo('viewvehicles.php')">View Vehicles</button>
    </div>
</body>
</html>