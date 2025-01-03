<?php
session_start();

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include('dbconn.php');

// Generate next vehicle ID
$query = "SELECT MAX(vehicles_id) AS max_id FROM vehicles";
$result = $dbconn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $maxId = $row['max_id'];
    if ($maxId) {
        $nextId = "VH" . str_pad((int)substr($maxId, 2) + 1, 5, "0", STR_PAD_LEFT);
    } else {
        $nextId = "VH00001";
    }
} else {
    die("Error generating vehicle ID: " . $dbconn->error);
}

// Initialize message variables
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = $nextId;
    $vehicle_type = strtoupper(trim($_POST['vehicle_type']));
    $vehicle_model = strtoupper(trim($_POST['vehicle_model']));
    $vehicle_plate = strtoupper(trim($_POST['vehicle_plate']));
    $vehicle_capacity = strtoupper(trim($_POST['vehicle_capacity']));
    $driver_id = strtoupper(trim($_POST['vehicle_driver']));
    $vehicle_img = trim($_POST['vehicle_img']);

    if (empty($vehicle_img)) {
        $vehicle_img = "https://i.ibb.co/xHMKJR8/noimageavailable.jpg";
    }

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
        $success_message = "Vehicle added successfully!";
    } else {
        $error_message = "Error adding vehicle: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle</title>
    <link rel="stylesheet" href="addvehiclestyle.css">
    <style>
        .popup {
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
            border-radius: 10px;
        }
        .popup-message {
            margin-bottom: 20px;
        }
        .popup button {
            padding: 10px;
            margin: 5px;
            background-color: #4070f4;
            color: #fff;
            border-radius: 5px;
        }
        .popup button:hover {
            background-color: rgb(49, 70, 187);
            cursor: pointer;
        }
        .modal-open {
            overflow: hidden;
        }
        .modal-open #blur-background {
            filter: blur(5px);
            pointer-events: none;
        }
    </style>
    <script>
        function showPopup(message, isSuccess) {
            const popup = document.getElementById(isSuccess ? 'success-popup' : 'error-popup');
            const popupMessage = document.getElementById(isSuccess ? 'success-popup-message' : 'error-popup-message');
            const body = document.body;
            popupMessage.textContent = message;
            popup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            const body = document.body;
            popup.style.display = 'none';
            body.classList.remove('modal-open');
        }

        function redirectTo(url) {
            window.location.href = url;
        }

        window.onload = function() {
            const successMessage = "<?php echo $success_message; ?>";
            const errorMessage = "<?php echo $error_message; ?>";
            if (successMessage) {
                showPopup(successMessage, true);
            } else if (errorMessage) {
                showPopup(errorMessage, false);
            }
        };
    </script>
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
                                    <option value="CAR">Car</option>
                                    <option value="VAN">Van</option>
                                </select>
                                <br>
                                <p><b>Plate Number</b></p>
                                <input type="text" name="vehicle_plate" required>
                                <br>
                                <p><b>Choose Driver</b></p>
                                <select name="vehicle_driver" id="vehicle-driver">
                                    <?php
                                    $driver_query = "SELECT driver_id, driver_fullname FROM driver";
                                    $driver_result = $dbconn->query($driver_query);
                                    while ($driver_row = $driver_result->fetch_assoc()) {
                                        echo "<option value=\"" . htmlspecialchars($driver_row['driver_id']) . "\">" . 
                                             htmlspecialchars($driver_row['driver_fullname']) . "</option>";
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
                        <div class="button-container">
                            <button type="submit" id="add">ADD</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <footer>
        <p>Footer Content</p>
    </footer>

    <!-- Popup modals outside blur-background -->
    <div id="success-popup" class="popup">
        <p id="success-popup-message" class="popup-message"></p>
        <button onclick="redirectTo('viewvehicles.php')">View Vehicles</button>
        <button onclick="redirectTo('homepageadmin.php')">Back to Homepage</button>
    </div>

    <div id="error-popup" class="popup">
        <p id="error-popup-message" class="popup-message"></p>
        <button onclick="closePopup('error-popup')">Close</button>
    </div>
</body>
</html>