<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Success</title>
    <link rel="stylesheet" href="bookingstyle.css">
    <script>
        function showSuccessPopup() {
            const popup = document.getElementById('success-popup');
            popup.style.display = 'block';
        }

        function redirectTo(url) {
            window.location.href = url;
        }

        window.onload = function() {
            showSuccessPopup();
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
    </style>
</head>
<body>
    
    <div id="success-popup">
        <p id="success-popup-message">Booking submitted successfully!</p>
        <button onclick="redirectTo('homepage.php')">Go to Homepage</button>
        <button onclick="redirectTo('bookinghistoryuser.php')">View Booking History</button>
    </div>
    
</body>
</html>