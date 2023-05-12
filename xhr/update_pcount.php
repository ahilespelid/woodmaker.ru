<?php
require_once "assets/includes/db_config.php";

if($_POST) {

    $user_id = mysqli_real_escape_string($conn, $_POST['userid']);
    $multi = mysqli_real_escape_string($conn, $_POST['multi']);
    if ($multi == 0){
    $sql = "UPDATE `Wo_Posts` SET `post_views` = `post_views` + 1 WHERE `post_id` = {$user_id}";

       }else{
    $sql = "UPDATE `Wo_Posts` SET `multi_views` = `multi_views` + 1 WHERE `multi_id` = {$user_id}";
    
       }
}
if($conn->query($sql) === TRUE) {
               echo 'pakyu';
           }

?>