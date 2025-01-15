<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: homepage.php");
    exit();
}

include('dbconn.php');

// Fetch user details to pre-fill the form
$user_id = $_GET['user_id'] ?? null;

if ($user_id) {
    $query = "SELECT * FROM user WHERE user_id = ?";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo "User not found.";
        exit();
    }
} else {
    echo "User ID not provided.";
    exit();
}

// Initialize message variables
$success_message = "";
$error_message = "";

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $new_fullname = strtoupper($_POST['user_fullname']);
    $new_type = $_POST['user_type'];
    $new_unit = $_POST['user_unit'];
    $new_password = $_POST['user_password'];

    // Update the user's basic details
    $update_query = "UPDATE user SET user_fullname = ?, user_type = ?, user_unit = ? WHERE user_id = ?";
    $update_stmt = $dbconn->prepare($update_query);
    $update_stmt->bind_param("ssss", $new_fullname, $new_type, $new_unit, $user_id);

    if ($update_stmt->execute()) {
        $success_message = "User details updated successfully.";
    } else {
        $error_message = "Failed to update user details.";
    }

    // If a new password is provided, update it
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $password_update_query = "UPDATE user SET user_password = ? WHERE user_id = ?";
        $password_update_stmt = $dbconn->prepare($password_update_query);
        $password_update_stmt->bind_param("ss", $hashed_password, $user_id);

        if ($password_update_stmt->execute()) {
            $success_message .= " Password updated successfully.";
        } else {
            $error_message = "Failed to update password.";
        }
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $delete_query = "DELETE FROM user WHERE user_id = ?";
    $delete_stmt = $dbconn->prepare($delete_query);
    $delete_stmt->bind_param("s", $user_id);

    if ($delete_stmt->execute()) {
        $success_message = "User deleted successfully.";
        header("Location: viewuser.php?success=" . urlencode($success_message));
        exit();
    } else {
        $error_message = "Failed to delete user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="edituserstyle.css">
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

        function confirmDeletion(event) {
            event.preventDefault();
            const deletePopup = document.getElementById('delete-popup');
            const body = document.body;
            deletePopup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function cancelDeletion() {
            const deletePopup = document.getElementById('delete-popup');
            const body = document.body;
            deletePopup.style.display = 'none';
            body.classList.remove('modal-open');
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
            border-radius: 10px;
        }
        .popup-message {
            margin-bottom: 20px;
        }
        .popup button {
            padding: 10px;
            width: 40%;
            background: #4070f4;
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
            <a href="javascript:history.back()"><< Back to Previous Page</a>
            <div class="edit-container">
                <form method="POST">
                    <div class="form-container">
                        <div class="form-upper">
                            <div class="upper-left">
                                <p><b>USER ID</b></p>
                                <input type="text" value="<?php echo htmlspecialchars($user['user_id']); ?>" disabled>
                            </div>
                            <div class="upper-right">
                                <p><b>USER TYPE</b></p>
                                <select name="user_type" id="user-type">
                                    <option value="user" <?php echo $user['user_type'] === 'user' ? 'selected' : ''; ?>>USER</option>
                                    <option value="admin" <?php echo $user['user_type'] === 'admin' ? 'selected' : ''; ?>>ADMIN</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-lower">
                            <p><b>USER FULLNAME</b></p>
                            <input type="text" name="user_fullname" value="<?php echo htmlspecialchars($user['user_fullname']); ?>">
                            <p><b>USER UNIT</b></p>
                            <select name="user_unit" id="user-unit">
                                <option value="JABATAN KEJURUTERAAN DAN KAWALAN BANGUNAN" <?php echo $user['user_unit'] === 'JABATAN KEJURUTERAAN DAN KAWALAN BANGUNAN' ? 'selected' : ''; ?>>JABATAN KEJURUTERAAN DAN KAWALAN BANGUNAN</option>
                                <option value="JABATAN PERBENDAHARAAN" <?php echo $user['user_unit'] === 'JABATAN PERBENDAHARAAN' ? 'selected' : ''; ?>>JABATAN PERBENDAHARAAN</option>
                                <option value="JABATAN PENILAIAN DAN PENGURUSAN HARTA" <?php echo $user['user_unit'] === 'JABATAN PENILAIAN DAN PENGURUSAN HARTA' ? 'selected' : ''; ?>>JABATAN PENILAIAN DAN PENGURUSAN HARTA</option>
                                <option value="JABATAN PERANCANGAN BANDAR DAN LANDSKAP" <?php echo $user['user_unit'] === 'JABATAN PERANCANGAN BANDAR DAN LANDSKAP' ? 'selected' : ''; ?>>JABATAN PERANCANGAN BANDAR DAN LANDSKAP</option>
                                <option value="JABATAN KESIHATAN AWAM DAN PERKHIDMATAN PERBANDARAN" <?php echo $user['user_unit'] === 'JABATAN KESIHATAN AWAM DAN PERKHIDMATAN PERBANDARAN' ? 'selected' : ''; ?>>JABATAN KESIHATAN AWAM DAN PERKHIDMATAN PERBANDARAN</option>
                                <option value="JABATAN PENGURUSAN" <?php echo $user['user_unit'] === 'JABATAN PENGURUSAN' ? 'selected' : ''; ?>>JABATAN PENGURUSAN</option>
                                <option value="UNIT PUSAT SETEMPAT" <?php echo $user['user_unit'] === 'UNIT PUSAT SETEMPAT' ? 'selected' : ''; ?>>UNIT PUSAT SETEMPAT</option>
                                <option value="UNIT UNDANG-UNDANG" <?php echo $user['user_unit'] === 'UNIT UNDANG-UNDANG' ? 'selected' : ''; ?>>UNIT UNDANG-UNDANG</option>
                                <option value="UNIT PERHUBUNGAN AWAM DAN PEMBANGUNAN SYARIKAT" <?php echo $user['user_unit'] === 'UNIT PERHUBUNGAN AWAM DAN PEMBANGUNAN SYARIKAT' ? 'selected' : ''; ?>>UNIT PERHUBUNGAN AWAM DAN PEMBANGUNAN SYARIKAT</option>
                                <option value="UNIT AUDIT DALAM" <?php echo $user['user_unit'] === 'UNIT AUDIT DALAM' ? 'selected' : ''; ?>>UNIT AUDIT DALAM</option>
                                <option value="BAHAGIAN LANDSKAP" <?php echo $user['user_unit'] === 'BAHAGIAN LANDSKAP' ? 'selected' : ''; ?>>BAHAGIAN LANDSKAP</option>
                                <option value="BAHAGIAN PENTADBIRAN" <?php echo $user['user_unit'] === 'BAHAGIAN PENTADBIRAN' ? 'selected' : ''; ?>>BAHAGIAN PENTADBIRAN</option>
                                <option value="BAHAGIAN PENGUATKUASAAN DAN KESELAMATAN" <?php echo $user['user_unit'] === 'BAHAGIAN PENGUATKUASAAN DAN KESELAMATAN' ? 'selected' : ''; ?>>BAHAGIAN PENGUATKUASAAN DAN KESELAMATAN</option>
                                <option value="BAHAGIAN TEKNOLOGI MAKLUMAT" <?php echo $user['user_unit'] === 'BAHAGIAN TEKNOLOGI MAKLUMAT' ? 'selected' : ''; ?>>BAHAGIAN TEKNOLOGI MAKLUMAT</option>
                                <option value="BAHAGIAN TEMPAT LETAK KERETA" <?php echo $user['user_unit'] === 'BAHAGIAN TEMPAT LETAK KERETA' ? 'selected' : ''; ?>>BAHAGIAN TEMPAT LETAK KERETA</option>
                                <!-- Add other options here -->
                            </select>
                            <p><b>NEW PASSWORD</b></p>
                            <div style="position: relative;">
                                <input type="password" id="user_password" name="user_password" placeholder="Enter new password if needs changing" style="width: calc(80% - 50px);">
                                <button type="button" id="toggle_password" onclick="togglePasswordVisibility('user_password', 'toggle_password')" style="position: absolute; right: 5; top: 0; height: 100%; width: 20%; border: none; background: none; cursor: pointer; color: black;">Show Password</button>
                            </div>
                        </div>
                    </div>
                    <div class="button-container">
                        <button type="submit" name="update" id="update">UPDATE</button>
                        <button type="button" id="delete" onclick="confirmDeletion(event)">DELETE</button>
                    </div>
                </form>
            </div>
        </main>
    
        <footer>
            <p>Footer Content</p>
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

    <!-- Modal for delete confirmation -->
    <div id="delete-popup" class="popup">
        <p class="popup-message">Are you sure you want to delete this user?</p>
        <form method="POST">
            <button type="submit" name="confirm_delete">Yes, Delete</button>
            <button type="button" onclick="cancelDeletion()">No, Cancel</button>
        </form>
    </div>
</body>
</html>