<?php
if($f == 'update_user_avatar_picture'){
    //$images = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30'];
    //pa($_REQUEST); pa($_COOKIE); pa($_FILES); exit;
    if(isset($_FILES['avatar']['name'])){
        $ai_post = 0;
        if($wo['config']['ai_user_system'] == 1 && !empty($_POST['ai_post']) && $_POST['ai_post'] == 'on'){$ai_post = 1;}
        
        $upload = Wo_UploadImage($_FILES["avatar"]["tmp_name"], $_FILES['avatar']['name'], 'avatar', $_FILES['avatar']['type'], $_POST['user_id'],'',$ai_post);
        
        if($upload === true){
            $img  = Wo_UserData($_POST['user_id']);
            $data = array(
                'status' => 200,
                'img' => $img['avatar'] . '?cache=' . rand(11, 22),
                'img_or' => $img['avatar_org'],
                'avatar_full' => Wo_GetMedia($img['avatar_full']) . '?cache=' . rand(11, 22),
                'avatar_full_or' => $img['avatar_full'],
                'big_text' => $wo['lang']['looks_good'],
                'small_text' => $wo['lang']['looks_good_des']
            );
            
            ///* / xhr/crop-avatar.php ///*/

            $crop_image = Wo_CropAvatarImage($data['avatar_full_or'], array(
                'x' => $_POST['x'],
                'y' => $_POST['y'],
                'w' => $_POST['width'],
                'h' => $_POST['height']
            ));
            if($crop_image){
                $update_user_data = Wo_UpdateUserData($_POST['user_id'], array(
                    'last_avatar_mod' => time()
                ));
                $data['crop-avatar'] = array(
                    'status' => 200
                );
            }
            //
            /*/ pa($_POST); exit;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $_POST['url']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);;
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendCropAvatar));
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                'X-Requested-With: XMLHttpRequest'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                       
            $server_output = curl_exec($ch); pa($server_output);
            curl_close($ch);
            ///*/
        }else{$data = $upload;}
    }
    Wo_CleanCache();
  
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
