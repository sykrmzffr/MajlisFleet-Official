<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.html");
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

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $new_fullname = $_POST['user_fullname'];
    $new_type = $_POST['user_type'];
    $new_unit = $_POST['user_unit'];

    $update_query = "UPDATE user SET user_fullname = ?, user_type = ?, user_unit = ? WHERE user_id = ?";
    $update_stmt = $dbconn->prepare($update_query);
    $update_stmt->bind_param("ssss", $new_fullname, $new_type, $new_unit, $user_id);

    if ($update_stmt->execute()) {
        echo "User updated successfully.";
        header("Location: viewuser.php");
        exit();
    } else {
        echo "Failed to update user.";
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $delete_query = "DELETE FROM user WHERE user_id = ?";
    $delete_stmt = $dbconn->prepare($delete_query);
    $delete_stmt->bind_param("s", $user_id);

    if ($delete_stmt->execute()) {
        echo "User deleted successfully.";
        header("Location: viewuser.php");
        exit();
    } else {
        echo "Failed to delete user.";
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
                    </div>
                </div>
                <div class="button-container">
                    <button type="submit" name="update">UPDATE</button>
                    <button type="submit" name="delete">DELETE</button>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>
</body>
</html>
