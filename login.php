<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve input
    $id = $_POST['user_id'];
    $password = $_POST['password'];

    include('dbconn.php');

    // Validate login
    $query = "SELECT user_id, user_password, user_type FROM user WHERE user_id = ?";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['user_password'] === $password) { // Plain text comparison for now
            // Success: Store user details in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect based on user type
            if ($user['user_type'] === 'admin') {
                header("Location: homepageadmin.php");
            } else {
                header("Location: homepage.php");
            }
            exit();
        } else {
            // Incorrect password
            header("Location: failed.html");
            exit();
        }
    } else {
        // User not found
        header("Location: failed.html");
        exit();
    }

    $stmt->close();
}

mysqli_close($dbconn);
?>
