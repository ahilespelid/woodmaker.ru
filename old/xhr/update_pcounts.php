<?php
require_once "assets/includes/db_config.php";

if($_POST) {

    $user_id = mysqli_real_escape_string($conn, $_POST['postid']);
    
    $sql = "UPDATE `Wo_Posts` SET `multi_views` = `multi_views` + 1 WHERE `post_id` = {$user_id}";

      
}
if($conn->query($sql) === TRUE) {
               echo 'pakyu';
           }

?>