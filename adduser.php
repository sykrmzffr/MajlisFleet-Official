<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Generate the next USER ID
$query = "SELECT MAX(user_id) AS max_id FROM user";
$result = $dbconn->query($query);
$row = $result->fetch_assoc();
$max_id = $row['max_id'];

if ($max_id) {
    $new_id_number = (int)substr($max_id, 2) + 1; // Extract number, increment
    $new_user_id = 'UR' . str_pad($new_id_number, 5, '0', STR_PAD_LEFT); // Format: UR00002
} else {
    $new_user_id = 'UR00002'; // Default first user ID
}

// Initialize message variables
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Convert user_fullname to uppercase
    $user_fullname = strtoupper($_POST['user_fullname']);
    $user_password = $_POST['user_password'];
    $user_type = $_POST['user_type'];
    $user_unit = $_POST['user_unit'];

    // Validation
    if ($user_type === 'default' || $user_unit === 'default') {
        $error_message = "Please select valid options for User Type and User Unit.";
    } else {
        // Hash the password
        $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);

        // Insert user into the database
        $insert_query = "INSERT INTO user (user_id, user_password, user_type, user_fullname, user_unit) VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbconn->prepare($insert_query);
        $stmt->bind_param("sssss", $new_user_id, $hashed_password, $user_type, $user_fullname, $user_unit);

        if ($stmt->execute()) {
            $success_message = "User added successfully.";
        } else {
            $error_message = "Failed to add user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Users</title>
    <link rel="stylesheet" href="adduserstyle.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
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
        }
        function togglePasswordVisibility(id, buttonId) {
            const passwordInput = document.getElementById(id);
            const toggleButton = document.getElementById(buttonId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Hide Password';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Show Password';
            }
        };
    </script>
    <style>
        /* Modal styles */
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
        <a href="viewuser.php"><< Back to Previous Page</a>
            <div class="edit-container">
                <form method="POST">
                    <div class="form-container">
                        <div class="form-upper">
                            <div class="upper-left">
                                <p><b>USER ID</b></p>
                                <input type="text" value="<?php echo $new_user_id; ?>" disabled>
                            </div>
                            <div class="upper-right">
                                <p><b>USER TYPE</b></p>
                                <select name="user_type" id="user-type" required>
                                    <option value="default">CLICK TO SELECT</option>
                                    <option value="user">USER</option>
                                    <option value="admin">ADMIN</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-lower">
                            <p><b>USER FULLNAME</b></p>
                            <input type="text" name="user_fullname" placeholder="User Fullname" required>
                            <p><b>USER UNIT</b></p>
                            <select name="user_unit" id="user-unit" style="width: 80%;" required>
                                <option value="default">CLICK TO SELECT</option>
                                <option value="JABATAN KEJURUTERAAN DAN KAWALAN BANGUNAN">JABATAN KEJURUTERAAN DAN KAWALAN BANGUNAN</option>
                                <option value="JABATAN PERBENDAHARAAN">JABATAN PERBENDAHARAAN</option>
                                <option value="JABATAN PENILAIAN DAN PENGURUSAN HARTA">JABATAN PENILAIAN DAN PENGURUSAN HARTA</option>
                                <option value="JABATAN PERANCANGAN BANDAR DAN LANDSKAP">JABATAN PERANCANGAN BANDAR DAN LANDSKAP</option>
                                <option value="JABATAN KESIHATAN AWAM DAN PERKHIDMATAN PERBANDARAN">JABATAN KESIHATAN AWAM DAN PERKHIDMATAN PERBANDARAN</option>
                                <option value="JABATAN PENGURUSAN">JABATAN PENGURUSAN</option>
                                <option value="UNIT PUSAT SETEMPAT">UNIT PUSAT SETEMPAT</option>
                                <option value="UNIT UNDANG-UNDANG">UNIT UNDANG-UNDANG</option>
                                <option value="UNIT PERHUBUNGAN AWAM DAN PEMBANGUNAN SYARIKAT">UNIT PERHUBUNGAN AWAM DAN PEMBANGUNAN SYARIKAT</option>
                                <option value="UNIT AUDIT DALAM">UNIT AUDIT DALAM</option>
                                <option value="BAHAGIAN LANDSKAP">BAHAGIAN LANDSKAP</option>
                                <option value="BAHAGIAN PENTADBIRAN">BAHAGIAN PENTADBIRAN</option>
                                <option value="BAHAGIAN PENGUATKUASAAN DAN KESELAMATAN">BAHAGIAN PENGUATKUASAAN DAN KESELAMATAN</option>
                                <option value="BAHAGIAN TEKNOLOGI MAKLUMAT">BAHAGIAN TEKNOLOGI MAKLUMAT</option>
                                <option value="BAHAGIAN TEMPAT LETAK KERETA">BAHAGIAN TEMPAT LETAK KERETA</option>
                            </select>
                            <p><b>USER PASSWORD</b></p>
                            <div style="position: relative;">
                                <input type="password" id="user_password" name="user_password" placeholder="Enter new password if needs changing" style="width: calc(80% - 50px);">
                                <button type="button" id="toggle_password" onclick="togglePasswordVisibility('user_password', 'toggle_password')" style="position: absolute; top: 0; height: 100%; width: 15%; border: none; background: none; color: black; cursor: pointer;">Show Password</button>
                            </div>
                        </div>
                    </div>
                    <div class="button-container">
                        <button type="submit" id="add">ADD</button>
                    </div>
                </form>
            </div>
        </main>
    
        <footer>
            <p>Copyright © 2025 Majlis Daerah Raub. All rights reserved.</p>
        </footer>
    </div>

    <!-- Modal for success messages -->
    <div id="success-popup" class="popup">
        <p id="success-popup-message" class="popup-message"></p>
        <button onclick="redirectTo('viewuser.php')">View Users</button>
        <button onclick="redirectTo('homepageadmin.php')">Back to Homepage</button>
    </div>

    <!-- Modal for error messages -->
    <div id="error-popup" class="popup">
        <p id="error-popup-message" class="popup-message"></p>
        <button onclick="closePopup('error-popup')">Close</button>
    </div>
</body>
</html>