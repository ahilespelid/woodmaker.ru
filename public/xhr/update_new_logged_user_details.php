<?php 
if ($f == 'update_new_logged_user_details') {
    if (empty($_POST['new_password']) || empty($_POST['username']) || empty($_POST['repeat_new_password']) || Wo_CheckSession($hash_id) === false) {
        $errors[] = $error_icon . $wo['lang']['please_check_details'];
    } else {
        if ($_POST['new_password'] != $_POST['repeat_new_password']) {
            $errors[] = $error_icon . $wo['lang']['password_mismatch'];
        }
        if (strlen($_POST['new_password']) < 6) {
            $errors[] = $error_icon . $wo['lang']['password_short'];
        }
        if (strlen($_POST['username']) > 32) {
            $errors[] = $error_icon . $wo['lang']['username_characters_length'];
        }
        if (strlen($_POST['username']) < 5) {
            $errors[] = $error_icon . $wo['lang']['username_characters_length'];
        }
        if (!preg_match('/^[\w]+$/', $_POST['username'])) {
            $errors[] = $error_icon . $wo['lang']['username_invalid_characters'];
        }
        if ($wo['user']['username'] !== $_POST['username']){ if (Wo_UserExists($_POST['username']) === true) {
            $errors[] = $error_icon . $wo['lang']['username_exists'];
        }}
        if(!$_POST['send_notify']){ if(empty($_POST['email']) && $wo['user']['user_id'] !== Wo_UserIdFromEmail($_POST['email'])){
            $errors[] = $error_icon . $wo['lang']['email_exists'];            
        }}
        if (empty($errors)) {
            $Update_data = array(
                'password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT),
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'social_login' => 0
            ); 
            if(Wo_UpdateUserData($_POST['user_id'], $Update_data)){
                $get_user = Wo_UserData($_POST['user_id']);
                $data     = array(
                    'status' => 200,
                    'message' => $success_icon . $wo['lang']['setting_updated'],
                    'url' => $get_user['url']
                );
                if($_POST['send_notify']){
                    //pa(T_MESSAGES);exit;
                    //$url = 'https://woodmaker.ru/'. $_POST['username'];
                    //$sql = "INSERT INTO " . T_NOTIFICATION . " SET `type`='admin_notification', `text`='" . $wo['lang']['you_pass'] . " " . $_POST['new_password'] . "', `recipient_id`=" . $_POST['user_id'] . ", `time`=" . time() . ", `url`='" . $url . "';";
                    $sql = "INSERT INTO " . T_FOLLOWERS . " SET `following_id`=1, `follower_id`=" . $_POST['user_id'] . ", `active`=1, `time`=" . time() . ";";
                    if(mysqli_query($sqlConnect, $sql)){mysqli_query($sqlConnect, "INSERT INTO " . T_MESSAGES . " SET `from_id`=1, `to_id`=" . $_POST['user_id'] . ", `text`='" . $wo['lang']['you_pass'] . " " . $_POST['new_password'] . "', `time`=" . time() . ";");}
                    //pa($sql);exit;
                }
            }else{
                //pa($Update_data); 
                //pa($_POST); pa($_GET);
            }
        }
    }
    header("Content-type: application/json");
    if (isset($errors)) {
        echo json_encode(array(
            'errors' => $errors
        ));
    } else {
        echo json_encode($data);
    }
    exit();
}
