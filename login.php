<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve input
    $id = $_POST['user_id'];
    $password = $_POST['password'];

    include('dbconn.php');
    include('update_booking_status.php'); // Include the file with the function
    include('logger.php'); // Include the logger file

    // Validate login for regular users
    $query = "SELECT user_id, user_password, user_type FROM user WHERE user_id = ?";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['user_password'])) { // Hashed password comparison
            echo "Password verified successfully!"; // Debugging line
            // Success: Store user details in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];

            // Update expired bookings
            $update_result = updateExpiredBookings($dbconn);
            echo $update_result; // Optional: For debugging purposes

            // Log the successful login attempt
            log_event($dbconn, 'LOGIN SUCCESS', $user['user_id'], 'User logged in successfully', 'user', $user['user_id']);

            // Redirect based on user type
            if ($user['user_type'] === 'admin') {
                header("Location: homepageadmin.php");
            } else {
                header("Location: homepage.php");
            }
            exit();
        } else {
            echo "Password verification failed!"; // Debugging line

            // Log the failed login attempt
            log_event($dbconn, 'LOGIN FAILURE', $id, 'Incorrect password', 'user', $id);

            // Incorrect password
            header("Location: index.php?error=Incorrect%20password");
            exit();
        }
    } else {
        // Log the failed login attempt
        log_event($dbconn, 'LOGIN FAILURE', $id, 'User ID not found', 'user', $id);

        // User not found
        header("Location: index.php?error=User%20ID%20not%20found");
        exit();
    }

    $stmt->close();
}

mysqli_close($dbconn);
?>