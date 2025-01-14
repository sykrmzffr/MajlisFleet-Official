<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: index.php");
    exit();
}

include('dbconn.php');

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type']; // Assuming user_type is stored in session

// Fetch user information from the database
$sql = "SELECT * FROM user WHERE user_id = '$user_id'";
$result = mysqli_query($dbconn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    // Error if user data is not found
    echo "Error: User data not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password === $confirm_password && !empty($new_password)) {
        // Note: Storing plain text passwords is not recommended
        $update_sql = "UPDATE user SET user_password = '$new_password' WHERE user_id = '$user_id'";
        
        if (mysqli_query($dbconn, $update_sql)) {
            // Use JavaScript to show the alert and redirect after OK is clicked
            echo "<script>
                    alert('Password updated successfully!');
                    if ('" . $user_type . "' === 'admin') {
                        window.location.href = 'homepageadmin.php';
                    } else {
                        window.location.href = 'homepage.php';
                    }
                  </script>";
            exit();
        } else {
            echo "<script>alert('Error updating password: " . mysqli_error($dbconn) . "');</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match or are empty!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="profilestyle.css">
    <script>
        function togglePasswordVisibility(id, buttonId) {
            const passwordInput = document.getElementById(id);
            const toggleButton = document.getElementById(buttonId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Show';
            }
        }
        
        function confirmUpdate() {
            return confirm('Are you sure you want to update your profile?');
        }

        function submitForm() {
            if (confirmUpdate()) {
                document.getElementById('profile-form').submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="nav-items">
                <div class="nav-logo">
                    <img src="image/logo.png" alt="">
                    <a href="<?php echo ($user_type === 'admin') ? 'homepageadmin.php' : 'homepage.php'; ?>">MajlisFleet</a>
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
            <div class="profile-container">
                <div class="profile-header">
                    <h2>My profile</h2>
                    <p>Edit your profile here</p>
                </div>
                <form id="profile-form" class="profile-form" method="POST">
                    <div class="left-side">
                        <div class="profile-id">
                            <p>ID</p>
                            <input type="text" value="<?php echo htmlspecialchars($row['user_id']); ?>" disabled>
                        </div>
                        <div class="profile-name">
                            <p>Name</p>
                            <input type="text" value="<?php echo htmlspecialchars($row['user_fullname']); ?>" disabled>
                        </div>
                        <div class="profile-password">
                            <p>Reset Password</p>
                            <div style="position: relative;">
                                <input type="password" id="new_password" name="new_password" placeholder="New password" style="width: calc(100% - 50px);">
                                <button type="button" id="toggle_new_password" onclick="togglePasswordVisibility('new_password', 'toggle_new_password')" style="position: absolute; right: 0; top: 0; height: 100%; border: none; background: none; cursor: pointer;">Show</button>
                            </div>
                        </div>
                        <div class="profile-confirm-password">
                            <p>Confirm Password</p>
                            <div style="position: relative;">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" style="width: calc(100% - 50px);">
                                <button type="button" id="toggle_confirm_password" onclick="togglePasswordVisibility('confirm_password', 'toggle_confirm_password')" style="position: absolute; right: 0; top: 0; height: 100%; border: none; background: none; cursor: pointer;">Show</button>
                            </div>
                        </div>
                    </div>
                    <div class="right-side">
                        <img src="image/dp.jpg" alt="Profile Picture">
                    </div>
                </form>
                <div class="profile-btn">
                    <button type="button" id="update" onclick="submitForm()">UPDATE</button>
                </div>
            </div>
        </main>

        <footer>
            <p>Footer Content</p>
        </footer>
    </div>
</body>
</html>