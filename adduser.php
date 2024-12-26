<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.html");
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_fullname = $_POST['user_fullname'];
    $user_password = $_POST['user_password'];
    $user_type = $_POST['user_type'];
    $user_unit = $_POST['user_unit'];

    // Validation
    if ($user_type === 'default' || $user_unit === 'default') {
        echo "Please select valid options for User Type and User Unit.";
    } else {
        // Insert user into the database
        $insert_query = "INSERT INTO user (user_id, user_password, user_type, user_fullname, user_unit) VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbconn->prepare($insert_query);
        $stmt->bind_param("sssss", $new_user_id, $user_password, $user_type, $user_fullname, $user_unit);

        if ($stmt->execute()) {
            echo "User added successfully.";
            header("Location: viewuser.php");
            exit();
        } else {
            echo "Failed to add user.";
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
                        <select name="user_unit" id="user-unit" required>
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
                        <input type="text" name="user_password" placeholder="User Password" required>
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
