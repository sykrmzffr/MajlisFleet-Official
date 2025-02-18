<?php
session_start();

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');
include('logger.php'); // Include the logger file

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
    $vehicle_status = strtoupper(trim($_POST['vehicle_status']));
    $vehicle_remarks = trim($_POST['vehicle_remarks']);

    // Handle file upload
    $vehicle_img = "./vehiclesimages/noimageavailable.jpg"; // Default image path
    if (isset($_FILES['vehicle_img']) && $_FILES['vehicle_img']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['vehicle_img']['tmp_name'];
        $file_name = $_FILES['vehicle_img']['name'];
        $file_size = $_FILES['vehicle_img']['size'];
        $file_type = $_FILES['vehicle_img']['type'];
        $file_name_cmps = explode(".", $file_name);
        $file_extension = strtolower(end($file_name_cmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png');
        if (in_array($file_extension, $allowedfileExtensions)) {
            $new_file_name = $vehicle_id . '.' . $file_extension;
            $uploadFileDir = './vehiclesimages/';
            $dest_path = $uploadFileDir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $vehicle_img = $dest_path;
            } else {
                $error_message = "Error moving the uploaded file.";
            }
        } else {
            $error_message = "Upload failed. Allowed file types: " . implode(',', $allowedfileExtensions);
        }
    }

    // Check if the vehicle plate number already exists
    $check_plate_query = "SELECT vehicles_id FROM vehicles WHERE vehicles_plate = ?";
    $check_plate_stmt = $dbconn->prepare($check_plate_query);
    $check_plate_stmt->bind_param("s", $vehicle_plate);
    $check_plate_stmt->execute();
    $check_plate_result = $check_plate_stmt->get_result();

    if ($check_plate_result->num_rows > 0) {
        $error_message = "This vehicle plate number already exists.";
    } else {
        // Insert the new vehicle into the database
        $stmt = $dbconn->prepare("
            INSERT INTO vehicles 
            (vehicles_id, vehicles_type, vehicles_model, vehicles_plate, vehicles_capacity, driver_id, vehicles_img, vehicles_status, vehicles_remarks) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssssss", 
            $vehicle_id, 
            $vehicle_type, 
            $vehicle_model, 
            $vehicle_plate, 
            $vehicle_capacity, 
            $driver_id, 
            $vehicle_img,
            $vehicle_status,
            $vehicle_remarks
        );

        if ($stmt->execute()) {
            // Log the addition of the new vehicle
            log_event($dbconn, 'CREATE', $_SESSION['user_id'], 'Added a new vehicle', 'vehicle', $vehicle_id);

            $success_message = "Vehicle added successfully!";
        } else {
            $error_message = "Error adding vehicle: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_plate_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle</title>
    <link rel="stylesheet" href="addvehiclestyle.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
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
            width: 30%;
            text-align: center;
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
            width: 40%;
        }
        .popup button:hover {
            background-color: rgb(49, 70, 187);
            cursor: pointer;
        }
        #error-popup button {
            background-color: #f44336;
        }
        #error-popup button:hover {
            background-color: rgb(187, 49, 49);
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
        <a href="viewvehicles.php"><< Back to Previous Page</a>
            <div class="edit-container">
                <form method="POST" enctype="multipart/form-data">
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
                                <select name="vehicle_capacity" id="vehicle-capacity" style="width: 80%;" required>
                                    <option value="" disabled selected>Please Select</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <br>
                                <p><b>Status</b></p>
                                <select name="vehicle_status" id="vehicle-status" style="width: 80%;" required>
                                    <option value="" disabled selected>Please Select</option>
                                    <option value="DATO' YDP">DATO' YDP</option>
                                    <option value="SETIAUSAHA">SETIAUSAHA</option>
                                    <option value="" disabled>--- --- ---</option>
                                    <option value="JKKB">JKKB</option>
                                    <option value="BUKIT FRASER">BUKIT FRASER</option>
                                    <option value="PENGUATKUASA">PENGUATKUASA</option>
                                    <option value="LANDSKAP">LANDSKAP</option>
                                    <option value="KESIHATAN">KESIHATAN</option>
                                    <option value="PENTADBIRAN">PENTADBIRAN</option>
                                    <option value="PENILAIAN">PENILAIAN</option>
                                    <option value="TLK">TLK</option>
                                    <option value="Others (Include in remarks)">Others (Include in remarks)</option>
                                </select>
                                <br>
                            </div>
                            <div class="upper-right">
                                <p><b>Vehicle Type</b></p>
                                <select name="vehicle_type" id="vehicle-type" style="width: 80%;" required>
                                    <option value="" disabled selected>Please Select</option>
                                    <optgroup label="Passenger Vehicles">
                                        <option value="SEDAN CAR">Sedan</option>
                                        <option value="EXECUTIVE CAR">Executive Car</option>
                                        <option value="MPV">Multi-Purpose Vehicle (MPV)</option>
                                    </optgroup>
                                    <optgroup label="Light-Duty Vehicles">
                                        <option value="VAN">Van</option>
                                        <option value="PICK-UP TRUCK">Pickup Truck</option>
                                    </optgroup>
                                    <optgroup label="Heavy-Duty Vehicles">
                                        <option value="4WD">Four-Wheel Drive (4WD)</option>
                                        <option value="LORRY">Lorry</option>
                                        <option value="LORRY DOUBLE CAB">Lorry Double Cab</option>
                                        <option value="LORRY TANK">Lorry Tank</option>
                                        <option value="LORRY SKYLIFT">Lorry Skylift</option>
                                    </optgroup>
                                    <optgroup label="Agricultural & Construction Vehicles">
                                        <option value="TRACTOR">Tractor</option>
                                        <option value="BACKHOE">Excavator/Loader</option>
                                    </optgroup>
                                    <optgroup label="Two-Wheeled Vehicles">
                                        <option value="MOTORCYCLE">Motorcycle</option>
                                    </optgroup>
                                </select>
                                <br>
                                <p><b>Plate Number</b></p>
                                <input type="text" name="vehicle_plate" required>
                                <br>
                                <p><b>Choose Driver</b></p>
                                <select name="vehicle_driver" id="vehicle-driver" style="width: 80%;" required>
                                    <option value="" disabled selected>Please Select</option>
                                    <?php
                                    // Fetch drivers ordered by driver_fullname in descending order
                                    $driver_query = "SELECT driver_id, driver_fullname FROM driver ORDER BY driver_fullname ASC";
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
                            <p><b>Upload Vehicle Image (Optional)</b></p>
                            <input type="file" name="vehicle_img" id="vehicle-img">
                            <br>
                            <p><b>Remarks</b></p>
                            <textarea name="vehicle_remarks" id="vehicle-remarks" rows="4" cols="50" style="width: 80%;"></textarea>
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
        <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
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