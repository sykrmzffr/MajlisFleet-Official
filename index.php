<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style1.css">
    <script>
        function showPopup(message) {
            const popup = document.getElementById('error-popup');
            const popupMessage = document.getElementById('error-popup-message');
            const body = document.body;
            popupMessage.textContent = message;
            popup.style.display = 'block';
            body.classList.add('modal-open');
        }

        function closePopup() {
            const popup = document.getElementById('error-popup');
            const body = document.body;
            popup.style.display = 'none';
            body.classList.remove('modal-open');
        }

        window.onload = function() {
            const errorMessage = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
            if (errorMessage) {
                showPopup(errorMessage);
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
            border-radius: 10px;
            text-align: center;
        }
        .popup-message {
            margin-bottom: 20px;
        }
        .popup button {
            padding: 10px;
            margin: 5px;
            background-color: #f44040;
            color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        <div class="login">
            <div class="login-container">
                <div class="login-container-left">
                    <div class="login-container-header">
                        <h1>Sign In</h1>
                    </div>
                    <form action="login.php" method="POST"> 
                        <div class="login-container-form">
                            <div class="login-container-input">
                                <input type="text" name="user_id" placeholder="User ID" required autofocus>
                            </div>
                            <div class="login-container-input">
                                <input type="password" name="password" placeholder="Password" required autofocus>
                            </div>
                        </div>
                        <div class="login-container-submit">
                            <button type="submit">Log In</button>
                        </div>
                    </form>
                </div>
                <div class="login-container-right">
                    <div class="login-container-logo">
                        <img src="image/logo.png">
                        <h1>MajlisFleet</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for error messages -->
    <div id="error-popup" class="popup">
        <p id="error-popup-message" class="popup-message"></p>
        <button onclick="closePopup()">Close</button>
    </div>
</body>
</html>