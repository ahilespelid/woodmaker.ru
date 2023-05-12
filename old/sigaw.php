<?php
require_once "assets/includes/db_config.php";

if($_POST) {

    $message = mysqli_real_escape_string($conn, $_POST['firstname']);
    $username = mysqli_real_escape_string($conn, $_POST['lastname']);
    $user_id = mysqli_real_escape_string($conn, $_POST['userid']);
    $sql = "UPDATE `Wo_Users` set `first_name` = '{$message}', `last_name`= '{$username}' WHERE `user_id` = '{$user_id}'";

       }
?>