<?php
session_start();

if($_SERVER ["REQUEST_METHOD"] == "POST"){

    
    //retrieve input
    $id = $_POST['user_id'];
    $password = $_POST['password'];

    include('dbconn.php');

    //validate login
    $query = "SELECT * FROM user WHERE user_id='$id' AND user_password='$password'";

    $result = $dbconn->query($query);

    if ($result -> num_rows ==1){
        //success
        $_SESSION['user_id'] = $id;
        header("Location: homepage.php");
        exit();
    }
    else{
        //failed
        header("Location: failed.html");
        exit();
    }

}

mysqli_close($dbconn);

?>