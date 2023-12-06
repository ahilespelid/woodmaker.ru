<?php
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.wowonder.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: wowondersocial@gmail.com   
// +------------------------------------------------------------------------+
// | WoWonder - The Ultimate Social Networking Platform
// | Copyright (c) 2017 WoWonder. All rights reserved.
// +------------------------------------------------------------------------+
require_once('assets/init.php');
$provider = "";
$types = array(
    'Yandex',
    'Google',
    'Facebook',
    'Twitter',
    'LinkedIn',
    'Vkontakte',
    'Instagram',
    'QQ',
    'WeChat',
    'Discord',
    'Mailru',
    'OkRu',
    'TikTok'
);

if('YandexCode' == $_GET['state']){echo '<script>window.location.href = window.location.href.replace("YandexCode","Yandex").replace("#access_token","&code");</script>';}
if(!empty($_GET['state']) && !empty($_GET['code'])) {
    $_GET['provider'] = $_GET['state'];
}
//
/*/ 
if (!empty($_GET['state']) && $_GET['state'] == 'OkRu' && !empty($_GET['code'])) {
    $_GET['provider'] = 'OkRu';
} ///*/  
//pa($_REQUEST);


if (isset($_GET['provider']) && in_array($_GET['provider'], $types)) {
    $provider = Wo_Secure($_GET['provider']);                                 
}

if (!empty($provider)) {
    if (!empty($_COOKIE['provider'])) {
        $_COOKIE['provider'] = '';
        unset($_COOKIE['provider']);
        setcookie('provider', '', -1);
        setcookie('provider', '', -1, '/');
    }
    setcookie('provider', $provider, time() + (60 * 60), '/');
}
else if(!empty($_COOKIE['provider']) && in_array($_COOKIE['provider'], $types)){
    
    $provider = Wo_Secure($_COOKIE['provider']);
}

if('OkRu' == $provider){ 
    if(empty($_GET['code'])) {
        header("Location: https://connect.ok.ru/oauth/authorize?client_id=".$wo['config']['OkAppId']."&scope=VALUABLE_ACCESS&response_type=code&redirect_uri=".$wo['config']['site_url']."/login-with.php&layout=w&state=OkRu");
        exit();
    }
    require_once('assets/libraries/odnoklassniki_sdk.php');
}elseif('Vkontakte' == $provider){//pa($wo['config']); exit;
    if(empty($_GET['code'])){
        $param = [
            'client_id'     => $wo['config']['VkontakteAppId'],
            'redirect_uri'  => $wo['config']['site_url'].'/login-with.php', 
            'scope'         => 'home_phone,mobile_phone,email', 
            'response_type' => 'code', 
            'state'         => 'Vkontakte']; 
        $url = 'https://oauth.vk.com/authorize?'.urldecode(http_build_query($param));
        header("Location: $url");
        exit();
    }
}elseif('Yandex' == $provider){//pa($wo['config']); exit;
    if(empty($_GET['code'])){
        $param = [
            'response_type' => 'token', 
            'client_id'     => $wo['config']['YandexAppId']]; 
        $url = 'https://oauth.yandex.ru/authorize?'.urldecode(http_build_query($param));
        header("Location: $url");
        exit();
    }
}
if(!empty($provider)){
    require_once('assets/libraries/social-login/config.php');
    require_once('assets/libraries/social-login/vendor/autoload.php');   
}

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

if (isset($provider) && in_array($provider, $types)) {
    try {
        if($provider == 'OkRu'){
            OdnoklassnikiSDK::SetOkInfo();
            //pa(OdnoklassnikiSDK::changeCodeToToken(OdnoklassnikiSDK::getCode()));
            //pa(OdnoklassnikiSDK::getCode());
            //pa(OdnoklassnikiSDK::makeRequest("users.getCurrentUser", null));
            //pa("SELECT `user_id` FROM " . T_USERS . " WHERE `email` = '{$user_profile->identifier}'");
            if(!is_null(OdnoklassnikiSDK::getCode())){
                if(OdnoklassnikiSDK::changeCodeToToken(OdnoklassnikiSDK::getCode())){
                    $current_user = OdnoklassnikiSDK::makeRequest("users.getCurrentUser", null);
                    if(!empty($current_user)){
                        $user_profile = ToObject($current_user);
                        $user_profile->identifier = $user_profile->uid;
                        $user_profile->firstName = $user_profile->first_name;
                        $user_profile->lastName = $user_profile->last_name;
                        $imgOk = (!empty($user_profile->pic_3)) ? $user_profile->pic_3 : ((!empty($user_profile->pic_2)) ? $user_profile->pic_2 : ((!empty($user_profile->pic_1)) ? $user_profile->pic_1 : 'https://woodmaker.ru/upload/photos/n-avatar.jpg'));
                        $user_profile->photoURL = (str_contains($imgOk, 'api.ok.ru/img/stub/user/male')) ? 'https://woodmaker.ru/upload/photos/n-avatar.jpg' : $imgOk;
                        
                        $query = mysqli_query($sqlConnect, $sql = "SELECT username FROM " . T_USERS . " WHERE `okru`='$user_profile->identifier' ORDER BY user_id DESC LIMIT 1;");
                        if(Wo_UserExists($user_id = $query->fetch_array()['username']) === true){
                            Wo_SetLoginWithSessionFromUsername($user_id); header("Location: " . $config['site_url']); exit;}
                        
                        //pa($user_profile);exit;
                        //pa((new ReflectionClass('OdnoklassnikiSDK'))->getMethods());exit;
                        //pa($user_id);exit;
                    }
                    else{
                        echo " <b><a href='" . Wo_SeoLink('index.php?link1=welcome') . "'>Try again<a></b>";
                        exit();
                    }
                }
                else{
                    echo " <b><a href='" . Wo_SeoLink('index.php?link1=welcome') . "'>Try again<a></b>";
                    exit();
                }
            }
            else{
                echo " <b><a href='" . Wo_SeoLink('index.php?link1=welcome') . "'>Try again<a></b>";
                exit();
            }
        }
        else if ($provider == 'TikTok') {
            require_once('./assets/libraries/tiktok/src/Connector.php');
            $_TK = new Connector($wo['config']['tiktok_client_key'], $wo['config']['tiktok_client_secret'], $LoginWithConfig['callback']);
            if (Connector::receivingResponse()) { 
                try {
                    $token = $_TK->verifyCode($_GET[Connector::CODE_PARAM]);
                    // Your logic to store the access token
                    $user_profile = $_TK->getUser();
                    $user_profile->identifier = $user_profile->union_id;
                    $user_profile->displayName = $user_profile->display_name;
                    $user_profile->firstName = $user_profile->display_name;
                    $user_profile->email = '';
                    $user_profile->profileURL = '';
                    $user_profile->lastName = '';
                    $user_profile->photoURL = $user_profile->avatar_larger;
                    $user_profile->description = '';
                    $user_profile->gender = '';

                    //$query = mysqli_query($sqlConnect, $sql = "SELECT username FROM " . T_USERS . " WHERE `tiktok`='$user_profile->identifier' ORDER BY user_id DESC LIMIT 1;");
                    //if(Wo_UserExists($user_id = $query->fetch_array()['username']) === true){
                    //    Wo_SetLoginWithSessionFromUsername($user_id); header("Location: " . $config['site_url']); exit;}

                    // Your logic to manage the User info
                    //$videos = $_TK->getUserVideoPages();
                    // Your logic to manage the Video info
                } catch (Exception $e) {
                    echo "Error: ".$e->getMessage();
                    echo '<br /><a href="'.$_TK->getRedirect().'">Retry</a>';
                    exit();
                }
            } else {
                header("Location: " . $_TK->getRedirect());
                exit();
            }
        }
        else if($provider == 'Vkontakte'){
            //pa($_REQUEST);
            try{
                if(!empty($_GET['code'])){
                $param = [
                    'client_id'     => $wo['config']['VkontakteAppId'], 
                    'client_secret' => $wo['config']['VkontakteAppKey'], 
                    'redirect_uri'  => $wo['config']['site_url'].'/login-with.php',
                    'code'          => $_GET['code'], 
                    'state'         => 'Vkontakte'];
                $url = 'https://oauth.vk.com/access_token?'.urldecode(http_build_query($param));
                // Получение access_token
                $data = file_get_contents($url);
                $data = json_decode($data, true);
                }
                if(!empty($data['access_token'])){
                    // Получили email
                    $email = $data['email'];
                    // Получим данные пользователя
                    $params = [
                        'v'            => '5.131',
                        'uids'         => $data['user_id'],
                        'access_token' => $data['access_token'],
                        'fields'       => 'photo_max_orig,city'];
             
                    $info = file_get_contents('https://api.vk.com/method/users.get?' . urldecode(http_build_query($params)));
                    $info = json_decode($info, true);    

                }                            
                if(!empty($info['response'][0])){
                    $user_profile = (object) [
                        'identifier'        => $info['response'][0]['id'],
                        'displayName'       => $info['response'][0]['first_name'],
                        'firstName'         => $info['response'][0]['first_name'],
                        'email'             => $email,
                        'profileURL'        => '',
                        'lastName'          => $info['response'][0]['last_name'],
                        'photoURL'          => (empty($info['response'][0]['photo_max_orig'])) ? 'https://woodmaker.ru/upload/photos/n-avatar.jpg' : $info['response'][0]['photo_max_orig'],
                        'description'       => '',
                        'gender'            => '',
                    ];

                        $query = mysqli_query($sqlConnect, $sql = "SELECT username FROM " . T_USERS . " WHERE `vk`='$user_profile->identifier' ORDER BY user_id DESC LIMIT 1;");
                        if(Wo_UserExists($user_id = $query->fetch_array()['username']) === true){
                            Wo_SetLoginWithSessionFromUsername($user_id); header("Location: " . $config['site_url']); exit;}
                 }               
            }catch(\Exception $e){
                    echo "Error: ".$e->getMessage();
                    exit();
            }
        }
        else if($provider == 'Yandex'){//pa($_REQUEST); exit;
            try{
                if(!empty($_GET['code'])){
                $param = [
                    'oauth_token'   => $_GET['code'], 
                    'format'        => 'json'];
                $url = 'https://login.yandex.ru/info?'.urldecode(http_build_query($param));
                // Получение access_token
                $data = file_get_contents($url);
                $data = json_decode($data, true);
                }//pa($data); exit;

                if(!empty($data['id'])){
                    $user_profile = (object) [
                        'identifier'        => $data['id'],
                        'displayName'       => $data['display_name'],
                        'firstName'         => $data['first_name'],
                        'email'             => (empty($data['default_email'])) ? '' : $data['default_email'],
                        'profileURL'        => '',
                        'lastName'          => $data['last_name'],
                        'photoURL'          => (1 == $data['is_avatar_empty']) ? 'https://woodmaker.ru/upload/photos/n-avatar.jpg' : 'https://avatars.yandex.net/get-yapic/'.$data['default_avatar_id'].'/islands-200',
                        'description'       => '',
                        'gender'            => '',
                    ];
                    $query = mysqli_query($sqlConnect, $sql = "SELECT username FROM " . T_USERS . " WHERE `ya`='$user_profile->identifier' ORDER BY user_id DESC LIMIT 1;");
                    if(Wo_UserExists($user_id = $query->fetch_array()['username']) === true){
                        Wo_SetLoginWithSessionFromUsername($user_id); header("Location: " . $config['site_url']); exit;}
                }
            }catch(\Exception $e){
                    echo "Error: ".$e->getMessage();
                    exit();
            }
        }
        else{
            $hybridauth = new Hybridauth( $LoginWithConfig );

            $authProvider = $hybridauth->authenticate($provider);
            $tokens = $authProvider->getAccessToken();
            $user_profile = $authProvider->getUserProfile();
        }
        //pa($user_profile);exit;    
        if ($user_profile && isset($user_profile->identifier)) {
            //$name = $user_profile->firstName;
            $user_email = (empty($user_profile->email)) ? '' : $user_profile->email;
//
/*/            
            if ($provider == 'Google') {
                $notfound_email     = 'go_';
                $notfound_email_com = '@google.com';
            } else if ($provider == 'Facebook') {
                $notfound_email     = 'fa_';
                $notfound_email_com = '@facebook.com';
            } else if ($provider == 'Twitter') {
                $notfound_email     = 'tw_';
                $notfound_email_com = '@twitter.com';
            } else if ($provider == 'LinkedIn') {
                $notfound_email     = 'li_';
                $notfound_email_com = '@linkedIn.com';
            } else if ($provider == 'Vkontakte') {
                $notfound_email     = 'vk_';
                $notfound_email_com = '@vk.com';
            } else if ($provider == 'Yandex') {
                $notfound_email     = 'ya_';
                $notfound_email_com = '@ya.ru';
            } else if ($provider == 'Instagram') {
                $notfound_email     = 'in_';
                $notfound_email_com = '@instagram.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'QQ') {
                $notfound_email     = 'qq_';
                $notfound_email_com = '@qq.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'WeChat') {
                $notfound_email     = 'wechat_';
                $notfound_email_com = '@wechat.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'Discord') {
                $notfound_email     = 'discord_';
                $notfound_email_com = '@discord.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'Mailru') {
                $notfound_email     = 'mailru_';
                $notfound_email_com = '@mailru.com';
                $name = $user_profile->displayName;
            } else if ($provider == 'OkRu') {
                $notfound_email     = 'okru_';
                $notfound_email_com = '@okru.com';
                $name = $user_profile->first_name;
            }
            $user_name  = $notfound_email . $user_profile->identifier;
            $user_email = $user_name . $notfound_email_com;
            if (!empty($user_profile->email)) {
                $user_email = $user_profile->email;
                if(empty($user_profile->emailVerified) && $provider == 'Discord') {
                    exit("Your E-mail is not verfied on Discord.");
                }
            }

            if (Wo_EmailExists($user_email) === true) {
                Wo_SetLoginWithSession($user_email);
                header("Location: " . $config['site_url']);
                exit();
            }
///*/            

                $str          = md5(microtime());
                $id           = substr($str, 0, 9);
                $user_uniq_id = (Wo_UserExists($id) === false) ? $id : 'u_' . $id;

            if (Wo_UserExists($user_uniq_id) === true) {
                //Wo_SetLoginWithSession($user_email);
                Wo_SetLoginWithSessionFromUsername($user_uniq_id);
                header("Location: " . $config['site_url']);
                exit();
            }
             else {
                $social_url   = substr($user_profile->profileURL, strrpos($user_profile->profileURL, '/') + 1);
                $imported_image = Wo_ImportImageFromLogin($user_profile->photoURL, 1);
                if (empty($imported_image)) {
                    $imported_image = $wo['userDefaultAvatar'];
                }
                $password = rand(1111, 9999);
                $re_data      = array(
                    'username' => Wo_Secure($user_uniq_id, 0),
                    'email' => Wo_Secure($user_email, 0),
                    'password' => Wo_Secure(md5($password), 0),
                    'email_code' => Wo_Secure(md5(rand(1111, 9999) . time()), 0),
                    'first_name' => Wo_Secure($user_profile->firstName),
                    'last_name' => Wo_Secure($user_profile->lastName),
                    'avatar' => Wo_Secure($imported_image),
                    'src' => Wo_Secure($provider),
                    'startup_image' => 1,
                    'lastseen' => time(),
                    'social_login' => 1,
                    'active' => '1'
                );
                if ($provider == 'Google') {
                    $re_data['about']  = Wo_Secure($user_profile->description);
                    $re_data['google'] = Wo_Secure($social_url);
                }
                if ($provider == 'Facebook') {
                    $fa_social_url       = @explode('/', $user_profile->profileURL);
                    if (!empty($fa_social_url[4])) {
                        $re_data['facebook'] = Wo_Secure($fa_social_url[4]);
                    }
                    $re_data['gender'] = 'male';
                    if (!empty($user_profile->gender)) {
                        if ($user_profile->gender == 'male') {
                            $re_data['gender'] = 'male';
                        } else if ($user_profile->gender == 'female') {
                            $re_data['gender'] = 'female';
                        }
                    }
                }
                if ($provider == 'Twitter') {
                    $re_data['twitter'] = Wo_Secure($social_url);
                }
                if ($provider == 'LinkedIn') {
                    $re_data['about']    = Wo_Secure($user_profile->description);
                    $re_data['linkedIn'] = Wo_Secure($social_url);
                }
                if ($provider == 'Vkontakte') {
                    //$re_data['about'] = Wo_Secure($user_profile->description);
                    $re_data['social_login'] = 1; $re_data['vk'] = Wo_Secure($user_profile->identifier);
                }
                if ($provider == 'Instagram') {
                    $re_data['instagram']   = Wo_Secure($user_profile->username);
                }
                if ($provider == 'QQ') {
                    $re_data['qq']   = Wo_Secure($social_url);
                }
                if ($provider == 'WeChat') {
                    $re_data['wechat']   = Wo_Secure($social_url);
                }
                if ($provider == 'Discord') {
                    $re_data['discord']   = Wo_Secure($social_url);
                }
                if ($provider == 'Mailru') {
                    $re_data['mailru']   = Wo_Secure($social_url);
                }
                if ($provider == 'OkRu') {
                    $re_data['social_login'] = 1; $re_data['okru']   = Wo_Secure($user_profile->identifier);
                }
                if ($provider == 'Yandex') {
                    $re_data['social_login'] = 1; $re_data['ya']   = Wo_Secure($user_profile->identifier);
                }
                if (!empty($_SESSION['ref']) && $wo['config']['affiliate_type'] == 0) {
                    $ref_user_id = Wo_UserIdFromUsername($_SESSION['ref']);
                    if (!empty($ref_user_id) && is_numeric($ref_user_id)) {
                        $re_data['referrer'] = Wo_Secure($ref_user_id);
                        $re_data['src']      = Wo_Secure('Referrer');
                        if ($wo['config']['affiliate_level'] < 2) {
                            $update_balance      = Wo_UpdateBalance($ref_user_id, $wo['config']['amount_ref']);
                        }
                        unset($_SESSION['ref']);
                    }
                }
                $wo['config']['user_registration'] = 1;
                if (Wo_RegisterUser($re_data) === true) {
                    Wo_SetLoginWithSessionFromUsername($user_uniq_id);
                    //$user_id = Wo_UserIdFromEmail($user_email);
                    $user_id = Wo_UserIdFromUsername($user_uniq_id);
                    if (!empty($re_data['referrer']) && is_numeric($wo['config']['affiliate_level']) && $wo['config']['affiliate_level'] > 1) {
                        AddNewRef($re_data['referrer'],$user_id,$wo['config']['amount_ref']);
                    }
                    if (!empty($wo['config']['auto_friend_users'])) {
                        $autoFollow = Wo_AutoFollow($user_id);
                    }
                    if (!empty($wo['config']['auto_page_like'])) {
                        Wo_AutoPageLike($user_id);
                    }
                    if (!empty($wo['config']['auto_group_join'])) {
                        Wo_AutoGroupJoin($user_id);
                    }
                    if (!empty($user_profile->photoURL) && $imported_image != $wo['userDefaultAvatar'] && $imported_image != $wo['userDefaultFAvatar']) {
                        $explode2  = @end(explode('.', $imported_image));
                        $explode3  = @explode('.', $imported_image);
                        $last_file = $explode3[0] . '_full.' . $explode2;
                        $compress = Wo_CompressImage($imported_image, $last_file, $wo['config']['images_quality']);
                        if ($compress) {
                            $upload_s3 = Wo_UploadToS3($last_file);
                            $query = mysqli_query($sqlConnect, "INSERT INTO " . T_POSTS . " (`user_id`, `postFile`, `time`, `postType`, `postPrivacy`) VALUES ('$user_id', '" . Wo_Secure($last_file) . "', '" . Wo_Secure(time()) . "', 'profile_picture_deleted', '0')");
                            $sql_id = mysqli_insert_id($sqlConnect);
                            $sql_id = Wo_Secure($sql_id);
                            $update_query = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `post_id` = '$sql_id' WHERE `id` = '$sql_id'");
                            Wo_Resize_Crop_Image($wo['profile_picture_width_crop'], $wo['profile_picture_height_crop'], $imported_image, $imported_image, $wo['profile_picture_image_quality']);
                            $upload_s3 = Wo_UploadToS3($imported_image);
                        }
                    }
                    $wo['user'] = $re_data;
                    $wo['pass'] = $password;
                    $body       = Wo_LoadPage('emails/login-with');
                    $headers    = "From: " . $config['siteName'] . " <" . $config['siteEmail'] . ">\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    @mail($re_data['email'], 'Thank you for your registration.', $body, $headers);
                    header("Location: " . Wo_SeoLink('index.php?link1=start-up'));
                    exit();
                }
            }
        }
    }
    catch (Exception $e) { pa($e);
        echo $e->getFile().': '.$e->getLine().'<br>';
        echo $e->getMessage();
        echo " <b><a href='" . Wo_SeoLink('index.php?link1=welcome') . "'>Try again<a></b>";
    }
} else {
    header("Location: " . Wo_SeoLink('index.php?link1=welcome'));
    exit();
}