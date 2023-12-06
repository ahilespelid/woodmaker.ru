<?php 
if ($f == 'crop-avatar' && Wo_CheckMainSession($hash_id) === true || $s == 'group') {
    if (Wo_IsAdmin() || $wo['user']['user_id'] == $_POST['user_id']) {
        $crop_image = Wo_CropAvatarImage($_POST['path'], array(
            'x' => $_POST['x'],
            'y' => $_POST['y'],
            'w' => $_POST['width'],
            'h' => $_POST['height']
        ));
        if($crop_image) {
            $data             = array(
                'status' => 200
            );
        }
        if ($s != 'group' && $crop_image) {
            $update_user_data = Wo_UpdateUserData($_POST['user_id'], array(
                'last_avatar_mod' => time()
            ));
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
