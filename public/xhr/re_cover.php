<?php
//ini_set('display_errors', '1'); ini_set('display_startup_errors', '1'); error_reporting(-1); 
if ($f == 're_cover') { 
    function initial($function, $type,$user = false) {
        if(Wo_IsAdmin() && !empty($_POST['user_id']) && is_numeric($_POST['user_id'])){
            $wo[$type] = $function(Wo_Secure($_POST['user_id']));
        }
        $from_top             = abs($_POST['pos']);
        $cover_image          = $wo[$type]['cover_org'];
        $full_url_image       = $wo[$type]['cover'];
        $default_image        = explode('.', $wo[$type]['cover_org']);
        $default_image        = $default_image[0] . '_full.' . $default_image[1];
        // if(is_readable($default_image)) {
        //     pa(ini_get('allow_url_fopen'));
        //     pa(ini_get('allow_url_open'));
        //     pa(file_get_contents(Wo_GetMedia($default_image)));
        //     pa(Wo_GetMedia($default_image));
        //     pa(pathinfo($default_image));
        //     pa(filesize($default_image));
        // } exit;
        $get_default_image    = file_put_contents($default_image, fetchDataFromURL(Wo_GetMedia($default_image))); 
        $image_type           = $_POST['image_type'];
        $default_cover_width  = 1120;
        $default_cover_height = 276;
        if($type === 'blog') {
            $default_cover_width  = 1260;
            $default_cover_height = 573;
        }
        require_once("assets/libraries/thumbncrop.inc.php");
        $tb = new ThumbAndCrop();
        $tb->openImg($default_image);
        $newHeight = $tb->getRightHeight($default_cover_width); 
        $tb->creaThumb($default_cover_width, $newHeight);
        $tb->setThumbAsOriginal();
        $tb->cropThumb($default_cover_width, 366, 0, $from_top);
        $tb->saveThumb($cover_image);
        $tb->resetOriginal();
        $tb->closeImg();
        $upload_s3        = Wo_UploadToS3($cover_image);
        if($user) {
            $update_user_data = Wo_UpdateUserData($wo[$type]['user_id'], array(
                'last_cover_mod' => time()
            ));
        }
        return $full_url_image;
    }
   if (isset($_POST['pos'])) {
       if ($_POST['type'] == 'page_cover') {
           if(initial('Wo_PageData', 'page')) {
               $full_url_image = initial('Wo_PageData', 'page');
           }
       } elseif ($_POST['type'] == 'group_cover') {
           if(initial('Wo_GroupData', 'group')) {
               $full_url_image = initial('Wo_GroupData', 'group');
           }
       } elseif ($_POST['type'] == 'event_cover') {
           if(initial('Wo_EventData', 'event')) {
               $full_url_image = initial('Wo_EventData', 'event');
           }
        } elseif ($_POST['type'] == 'blog_cover') {
            if(initial('Wo_GetArticle', 'blog')) {
                $full_url_image = initial('Wo_BlogData', 'blog');
            }
       } elseif ($_POST['type'] == 'user_cover') { 
           if (($_POST['cover_image'] != $wo['userDefaultCover']) && ($_POST['cover_image'] == $wo['user']['cover_org'] || Wo_IsAdmin()) && (Wo_GetMedia($wo['user']['cover_full']) == $_POST['real_image']) || Wo_IsAdmin()) {
               if(initial('Wo_UserData', 'user', true)) {
                   $full_url_image = initial('Wo_UserData', 'user', true);
               }
           }
       }
       if (empty($full_url_image)) {
           $full_url_image = Wo_GetMedia($wo['userDefaultCover']);
       }
       $data = array(
           'status' => 200,
           'url' => $full_url_image . '?timestamp=' . md5(time())
       );
   }
   Wo_CleanCache();
   header("Content-type: application/json");
   echo json_encode($data);
   exit();
}
