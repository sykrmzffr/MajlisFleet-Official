<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="editvehiclestyle.css">
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
            <div class="form-container">
                <div class="upper-half">
                    <div class="upper-left">
                        <p><b>Vehicle ID</b></p>
                        <input type="text" disabled>
                        <br>
                        <p><b>Model</b></p>
                        <input type="text">
                        <br>
                        <p><b>Capacity</b></p>
                        <select name="vehicle_capacity" id="vehicle-capacity">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                        <br>
                    </div>
                    <div class="upper-right">
                        <p><b>Vehicle Type</b></p>
                        <select name="vehicle_type" id="vehicle-type">
                            <option value="car">Car</option>
                            <option value="van">Van</option>
                        </select>
                        <br>
                        <p><b>Plate Number</b></p>
                        <input type="text">
                        <br>
                        <p><b>Choose Driver</b></p>
                        <select name="vehicle_driver" id="vehicle-driver">
                            <option value="driver_id">test</option>
                            <option value="driver_id">test</option>
                        </select>
                        <br>
                    </div>
                </div>
                <div class="lower-half">
                    <p><b>Image Link (Optional)</b></p>
                    <input type="text" id="img-link">
                </div>
            </div>
            <div class="button-container">
                <button id="update">UPDATE</button>
                <button id="delete">DELETE</button>
            </div>
        </div>
    </main>
    
    <footer>
        <p>Footer Content</p>
    </footer>
</body>
</html>