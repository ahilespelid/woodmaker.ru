<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
@ini_set("max_execution_time", 0);
@ini_set("memory_limit", "-1");
@set_time_limit(0);
require_once "config.php";
require_once "assets/libraries/DB/vendor/autoload.php";

if(!function_exists('pa')){
    function pa($a,$br=0,$mes='',$t='pre'):bool{$backtrace = debug_backtrace(); $fileinfo = '';$sbr='';
        if(!empty($backtrace[0]) && is_array($backtrace[0])){$fileinfo = $backtrace[0]['file'] . ":" . $backtrace[0]['line'];}
        while($br){$sbr.=(empty($t) ? PHP_EOL : '<br>');$br--;}
        echo $fileinfo.$sbr.$mes.(empty($t) ? '' : '<'.$t.'>'); print_r($a=(!empty($a)?$a:[])); echo(empty($t) ? '' : '</'.$t.'>').PHP_EOL;
        return true;
}}



$wo           = array();
// Connect to SQL Server
$sqlConnect   = $wo["sqlConnect"] = mysqli_connect($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name, 3306);
// create new mysql connection
$mysqlMaria   = new Mysql;
// Handling Server Errors
$ServerErrors = array();
if (mysqli_connect_errno()) {
    $ServerErrors[] = "Failed to connect to MySQL: " . mysqli_connect_error();
}
if (!function_exists("curl_init")) {
    $ServerErrors[] = "PHP CURL is NOT installed on your web server !";
}
if (!extension_loaded("gd") && !function_exists("gd_info")) {
    $ServerErrors[] = "PHP GD library is NOT installed on your web server !";
}
if (!extension_loaded("zip")) {
    $ServerErrors[] = "ZipArchive extension is NOT installed on your web server !";
}
$query = mysqli_query($sqlConnect, "SET NAMES utf8mb4");
if (isset($ServerErrors) && !empty($ServerErrors)) {
    foreach ($ServerErrors as $Error) {
        echo "<h3>" . $Error . "</h3>";
    }
    die();
}
$baned_ips = Wo_GetBanned("user");
if (in_array($_SERVER["REMOTE_ADDR"], $baned_ips)) {
    exit();
}
$config    = Wo_GetConfig();
if ($config['developer_mode'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
$db        = new MysqliDb($sqlConnect);
$all_langs = Wo_LangsNamesFromDB();
$wo['iso'] = GetIso();
foreach ($all_langs as $key => $value) {
    $insert = false;
    if (!in_array($value, array_keys($config))) {
        $db->insert(T_CONFIG, array(
            "name" => $value,
            "value" => 1
        ));
        $insert = true;
    }
}
if ($insert == true) {
    $config = Wo_GetConfig();
}
if (isset($_GET["theme"]) && in_array($_GET["theme"], array(
    "default",
    "sunshine",
    "wowonder",
    "wondertag"
))) {
    $_SESSION["theme"] = $_GET["theme"];
}
if (isset($_SESSION["theme"]) && !empty($_SESSION["theme"])) {
    $config["theme"] = $_SESSION["theme"];
    if ($_SERVER["REQUEST_URI"] == "/v2/wonderful" || $_SERVER["REQUEST_URI"] == "/v2/wowonder") {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }
}
$config["withdrawal_payment_method"] = json_decode($config['withdrawal_payment_method'],true);
// Config Url
$config["theme_url"] = $site_url . "/themes/" . $config["theme"];
$config["site_url"]  = $site_url;
$wo["site_url"]      = $site_url;
$config["wasabi_site_url"]         = "https://s3.".$config["wasabi_bucket_region"].".wasabisys.com";
if (!empty($config["wasabi_bucket_name"])) {
    $config["wasabi_site_url"] = "https://s3.".$config["wasabi_bucket_region"].".wasabisys.com/".$config["wasabi_bucket_name"];
}
$s3_site_url         = "https://test.s3.amazonaws.com";
if (!empty($config["bucket_name"])) {
    $s3_site_url = "https://{bucket}.s3.amazonaws.com";
    $s3_site_url = str_replace("{bucket}", $config["bucket_name"], $s3_site_url);
}
$config["s3_site_url"] = $s3_site_url;
$s3_site_url_2         = "https://test.s3.amazonaws.com";
if (!empty($config["bucket_name_2"])) {
    $s3_site_url_2 = "https://{bucket}.s3.amazonaws.com";
    $s3_site_url_2 = str_replace("{bucket}", $config["bucket_name_2"], $s3_site_url_2);
}
$config["s3_site_url_2"]   = $s3_site_url_2;
$wo["config"]              = $config;
$ccode                     = Wo_CustomCode("g");
$ccode                     = is_array($ccode) ? $ccode : array();
$wo["config"]["header_cc"] = !empty($ccode[0]) ? $ccode[0] : "";
$wo["config"]["footer_cc"] = !empty($ccode[1]) ? $ccode[1] : "";
$wo["config"]["styles_cc"] = !empty($ccode[2]) ? $ccode[2] : "";

$wo["script_version"]      = $wo["config"]["version"];
$http_header               = "http://";
if (!empty($_SERVER["HTTPS"])) {
    $http_header = "https://";
}
$wo["actual_link"] = $http_header . $_SERVER["HTTP_HOST"] . urlencode($_SERVER["REQUEST_URI"]);
// Define Cache Vireble
$cache             = new Cache();
$cache->Wo_OpenCacheDir();
$wo["purchase_code"] = "";
if (!empty($purchase_code)) {
    $wo["purchase_code"] = $purchase_code;
}
// Login With Url
$wo["facebookLoginUrl"]   = $config["site_url"] . "/login-with.php?provider=Facebook";
$wo["twitterLoginUrl"]    = $config["site_url"] . "/login-with.php?provider=Twitter";
$wo["googleLoginUrl"]     = $config["site_url"] . "/login-with.php?provider=Google";
$wo["linkedInLoginUrl"]   = $config["site_url"] . "/login-with.php?provider=LinkedIn";
$wo["VkontakteLoginUrl"]  = $config["site_url"] . "/login-with.php?provider=Vkontakte";
$wo["instagramLoginUrl"]  = $config["site_url"] . "/login-with.php?provider=Instagram";
$wo["QQLoginUrl"]         = $config["site_url"] . "/login-with.php?provider=QQ";
$wo["WeChatLoginUrl"]     = $config["site_url"] . "/login-with.php?provider=WeChat";
$wo["DiscordLoginUrl"]    = $config["site_url"] . "/login-with.php?provider=Discord";
$wo["MailruLoginUrl"]     = $config["site_url"] . "/login-with.php?provider=Mailru";
$wo["YandexLoginUrl"]     = $config["site_url"] . "/login-with.php?provider=Yandex";
$wo["OkLoginUrl"]         = $config["site_url"] . "/login-with.php?provider=OkRu";
$wo["TikTokLoginUrl"]     = $config["site_url"] . "/login-with.php?provider=TikTok";
// Defualt User Pictures
$wo["userDefaultAvatar"]  = "upload/photos/d-avatar.jpg";
$wo["userDefaultFAvatar"] = "upload/photos/f-avatar.jpg";
$wo["userDefaultCover"]   = "upload/photos/d-cover.jpg";
$wo["pageDefaultAvatar"]  = "upload/photos/d-page.jpg";
$wo["groupDefaultAvatar"] = "upload/photos/d-group.jpg";
// Get LoggedIn User Data
$wo["loggedin"]           = false;
$langs                    = Wo_LangsNamesFromDB();
if (Wo_IsLogged() == true) {
    $session_id         = !empty($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_COOKIE["user_id"];
    $wo["user_session"] = Wo_GetUserFromSessionID($session_id);
    $wo["user"]         = Wo_UserData($wo["user_session"]);
    if (!empty($wo["user"]["language"])) {
        if (in_array($wo["user"]["language"], $langs)) {
            $_SESSION["lang"] = $wo["user"]["language"];
        }
    }
    if ($wo["user"]["user_id"] < 0 || empty($wo["user"]["user_id"]) || !is_numeric($wo["user"]["user_id"]) || Wo_UserActive($wo["user"]["username"]) === false) {
        header("Location: " . Wo_SeoLink("index.php?link1=logout"));
    }
    $wo["loggedin"] = true;
} else {
    $wo["userSession"] = getUserProfileSessionID();
}

if (!empty($_GET["c_id"]) && !empty($_GET["user_id"])) {
    $application = "windows";
    if (!empty($_GET["application"])) {
        if ($_GET["application"] == "phone") {
            $application = Wo_Secure($_GET["application"]);
        }
    }
    $c_id             = Wo_Secure($_GET["c_id"]);
    $user_id          = Wo_Secure($_GET["user_id"]);
    $check_if_session = Wo_CheckUserSessionID($user_id, $c_id, $application);
    if ($check_if_session === true) {
        $wo["user"]          = Wo_UserData($user_id);
        $session             = Wo_CreateLoginSession($user_id);
        $_SESSION["user_id"] = $session;
        setcookie("user_id", $session, time() + 10 * 365 * 24 * 60 * 60);
        if ($wo["user"]["user_id"] < 0 || empty($wo["user"]["user_id"]) || !is_numeric($wo["user"]["user_id"]) || Wo_UserActive($wo["user"]["username"]) === false) {
            header("Location: " . Wo_SeoLink("index.php?link1=logout"));
        }
        $wo["loggedin"] = true;
    }
}
if (!empty($_POST["user_id"]) && (!empty($_POST["s"]) || !empty($_POST["access_token"]))) {
    $application  = "windows";
    $access_token = !empty($_POST["s"]) ? $_POST["s"] : $_POST["access_token"];
    if (!empty($_GET["application"])) {
        if ($_GET["application"] == "phone") {
            $application = Wo_Secure($_GET["application"]);
        }
    }
    if ($application == "windows") {
        $access_token = $access_token;
    }
    $s                = Wo_Secure($access_token);
    $user_id          = Wo_Secure($_POST["user_id"]);
    $check_if_session = Wo_CheckUserSessionID($user_id, $s, $application);
    if ($check_if_session === true) {
        $wo["user"] = Wo_UserData($user_id);
        if ($wo["user"]["user_id"] < 0 || empty($wo["user"]["user_id"]) || !is_numeric($wo["user"]["user_id"]) || Wo_UserActive($wo["user"]["username"]) === false) {
            $json_error_data = array(
                "api_status" => "400",
                "api_text" => "failed",
                "errors" => array(
                    "error_id" => "7",
                    "error_text" => "User id is wrong."
                )
            );
            header("Content-type: application/json");
            echo json_encode($json_error_data, JSON_PRETTY_PRINT);
            exit();
        }
        $wo["loggedin"] = true;
    } else {
        $json_error_data = array(
            "api_status" => "400",
            "api_text" => "failed",
            "errors" => array(
                "error_id" => "6",
                "error_text" => "Session id is wrong."
            )
        );
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
}
// Language Function
if (isset($_GET["lang"]) and !empty($_GET["lang"])) {
    if (in_array($_GET["lang"], array_keys($wo["config"])) && $wo["config"][$_GET["lang"]] == 1) {
        $lang_name = Wo_Secure(strtolower($_GET["lang"]));
        if (in_array($lang_name, $langs)) {
            Wo_CleanCache();
            $_SESSION["lang"] = $lang_name;
            if ($wo["loggedin"] == true) {
                mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `language` = '" . $lang_name . "' WHERE `user_id` = " . Wo_Secure($wo["user"]["user_id"]));
                cache($wo["user"]["user_id"], 'users', 'delete');
            }
        }
    }
}
if ($wo["loggedin"] == true && $wo["config"]["cache_sidebar"] == 1) {
    if (!empty($_COOKIE["last_sidebar_update"])) {
        if ($_COOKIE["last_sidebar_update"] < time() - 120) {
            Wo_CleanCache();
        }
    } else {
        Wo_CleanCache();
    }
}
if (empty($_SESSION["lang"])) {
    $_SESSION["lang"] = $wo["config"]["defualtLang"];
}
$wo["language"]      = $_SESSION["lang"];
$wo["language_type"] = "ltr";
// Add rtl languages here.
$rtl_langs           = array(
    "arabic",
    "urdu",
    "hebrew",
    "persian"
);
if (!isset($_COOKIE["ad-con"])) {
    setcookie("ad-con", htmlentities(json_encode(array(
        "date" => date("Y-m-d"),
        "ads" => array()
    ))), time() + 10 * 365 * 24 * 60 * 60);
}
$wo["ad-con"] = array();
if (!empty($_COOKIE["ad-con"])) {
    $wo["ad-con"] = json_decode(html_entity_decode($_COOKIE["ad-con"]));
    $wo["ad-con"] = ToArray($wo["ad-con"]);
}
if (!is_array($wo["ad-con"]) || !isset($wo["ad-con"]["date"]) || !isset($wo["ad-con"]["ads"])) {
    setcookie("ad-con", htmlentities(json_encode(array(
        "date" => date("Y-m-d"),
        "ads" => array()
    ))), time() + 10 * 365 * 24 * 60 * 60);
}
if (is_array($wo["ad-con"]) && isset($wo["ad-con"]["date"]) && strtotime($wo["ad-con"]["date"]) < strtotime(date("Y-m-d"))) {
    setcookie("ad-con", htmlentities(json_encode(array(
        "date" => date("Y-m-d"),
        "ads" => array()
    ))), time() + 10 * 365 * 24 * 60 * 60);
}
if (!isset($_COOKIE["_us"])) {
    setcookie("_us", time() + 60 * 60 * 24, time() + 10 * 365 * 24 * 60 * 60);
}
if ((isset($_COOKIE["_us"]) && $_COOKIE["_us"] < time()) || 1) {
    setcookie("_us", time() + 60 * 60 * 24, time() + 10 * 365 * 24 * 60 * 60);
}
// checking if corrent language is rtl.
foreach ($rtl_langs as $lang) {
    if ($wo["language"] == strtolower($lang)) {
        $wo["language_type"] = "rtl";
    }
}
// Icons Virables
$error_icon   = '<i class="fa fa-exclamation-circle"></i> ';
$success_icon = '<i class="fa fa-check"></i> ';
// Include Language File
$wo["lang"]   = Wo_LangsFromDB($wo["language"]);
if (file_exists("assets/languages/extra/" . $wo["language"] . ".php")) {
    require "assets/languages/extra/" . $wo["language"] . ".php";
}
if (empty($wo["lang"])) {
    $wo["lang"] = Wo_LangsFromDB();
}
$wo["second_post_button_icon"] = $config["second_post_button"] == "wonder" ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="8"></line></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-thumbs-down"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"></path></svg>';
$theme_settings                = array();
$theme_settings["theme"]       = "wowonder";
if (file_exists("./themes/" . $config["theme"] . "/layout/404/dont-delete-this-file.json")) {
    $theme_settings = json_decode(file_get_contents("./themes/" . $config["theme"] . "/layout/404/dont-delete-this-file.json"), true);
}
if ($theme_settings["theme"] == "wonderful") {
    $wo["second_post_button_icon"] = $config["second_post_button"] == "wonder" ? "exclamation-circle" : "thumb-down";
}
$wo["second_post_button_text"]  = $config["second_post_button"] == "wonder" ? $wo["lang"]["wonder"] : $wo["lang"]["dislike"];
$wo["second_post_button_texts"] = $config["second_post_button"] == "wonder" ? $wo["lang"]["wonders"] : $wo["lang"]["dislikes"];
$wo["marker"]                   = "?";
if ($wo["config"]["seoLink"] == 0) {
    $wo["marker"] = "&";
}
require_once "assets/includes/data.php";
$wo["emo"]                           = $emo;
$wo["profile_picture_width_crop"]    = 150;
$wo["profile_picture_height_crop"]   = 150;
$wo["profile_picture_image_quality"] = 70;
$wo["redirect"]                      = 0;

$wo["update_cache"]                  = "";
if (!empty($wo["config"]["last_update"])) {
    $update_cache = time() - 21600;
    if ($update_cache < $wo["config"]["last_update"]) {
        $wo["update_cache"] = "?" . sha1(time());
    }
}

// night mode
if (empty($_COOKIE["mode"])) {
    setcookie("mode", "day", time() + 10 * 365 * 24 * 60 * 60, "/");
    $_COOKIE["mode"] = "day";
    $wo["mode_link"] = "night";
    $wo["mode_text"] = $wo["lang"]["night_mode"];
} else {
    if ($_COOKIE["mode"] == "day") {
        $wo["mode_link"] = "night";
        $wo["mode_text"] = $wo["lang"]["night_mode"];
    }
    if ($_COOKIE["mode"] == "night") {
        $wo["mode_link"] = "day";
        $wo["mode_text"] = $wo["lang"]["day_mode"];
    }
}
if (!empty($_GET["mode"])) {
    if ($_GET["mode"] == "day") {
        setcookie("mode", "day", time() + 10 * 365 * 24 * 60 * 60, "/");
        $_COOKIE["mode"] = "day";
        $wo["mode_link"] = "night";
        $wo["mode_text"] = $wo["lang"]["night_mode"];
    } elseif ($_GET["mode"] == "night") {
        setcookie("mode", "night", time() + 10 * 365 * 24 * 60 * 60, "/");
        $_COOKIE["mode"] = "night";
        $wo["mode_link"] = "day";
        $wo["mode_text"] = $wo["lang"]["day_mode"];
    }
}
include_once "assets/includes/onesignal_config.php";


$wo["pro_packages"]       = Wo_GetAllProInfo();
try {
    $wo["genders"]             = Wo_GetGenders($wo["language"], $langs);
//
/*/ ahilespelid ///* /    
    $wo["categories"]          = Wo_GetCategories(T_CATEGORIES);
    $wo["page_categories"]     = Wo_GetCategories(T_PAGES_CATEGORY);
    $wo["group_categories"]    = Wo_GetCategories(T_GROUPS_CATEGORY);
    $wo["blog_categories"]     = Wo_GetCategories(T_BLOGS_CATEGORY);
    $wo["products_categories"] = Wo_GetCategories(T_PRODUCTS_CATEGORY);
    $wo["job_categories"]      = Wo_GetCategories(T_JOB_CATEGORY);
/* /// ahilespelid ///*/    
    $wo["categories"]          = Wo_GetCategoriesWithSub();
    $wo["status_categories"]          = Wo_GetCategoriesWithSub('status');
    $wo["page_categories"]     = Wo_GetCategoriesWithSub('page');
    $wo["group_categories"]    = Wo_GetCategoriesWithSub('group');
    //$wo["group_sub_categories"]='';
    $wo["blog_categories"]     = Wo_GetCategoriesWithSub('blog');
//    $wo["products_categories"] = Wo_GetCategoriesWithSub('product');
//    $wo["job_categories"]      = Wo_GetCategoriesWithSub('job');
///* / ahilespelid /// */    

    $wo["reactions_types"]     = Wo_GetReactionsTypes();
}
catch (Exception $e) {
    $wo["genders"]             = array();
    $wo["page_categories"]     = array();
    $wo["group_categories"]    = array();
    $wo["blog_categories"]     = array();
    $wo["products_categories"] = array();
    $wo["job_categories"]      = array();
    $wo["reactions_types"]     = array();
}

try{
    
//
/*/ ahilespelid Старые функционал подкатегорий ///* /
Wo_GetSubCategories();
///* / ahilespelid ///*/
    
    Wo_GetSubCategoriesWithArray($wo["categories"]);
    $wo["page_sub_categories"]     = ($wo["page_sub_categories"]     ?? []);
    $wo["group_sub_categories"]    = ($wo["group_sub_categories"]    ?? []);
    $wo["products_sub_categories"] = ($wo["products_sub_categories"] ?? []);
}catch (Exception $e) {
    $wo["page_sub_categories"]     = [];
    $wo["group_sub_categories"]    = [];
    $wo["products_sub_categories"] = [];
}

// manage geo
try{
    $wo["cities"] = Wo_GetGeoObjects();
}catch(Exception $e){
    $wo['cities'] = [
    ['id' =>  '1', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Абакан',                        'area' => 'Республика Хакасия',                             'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:48'],
    ['id' =>  '2', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Анадырь',                       'area' => 'Чукотский автономный округ',                     'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:49'],
    ['id' =>  '3', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Архангельск',                   'area' => 'Архангельская область',                          'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:49'],
    ['id' =>  '4', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Астрахань',                     'area' => 'Астраханская область',                           'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:49'],
    ['id' =>  '5', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Барнаул',                       'area' => 'Алтайский край',                                 'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:49'],
    ['id' =>  '6', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Белгород',                      'area' => 'Белгородская область',                           'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:49'],
    ['id' =>  '7', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Биробиджан',                    'area' => 'Еврейская автономная область',                   'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:49'],
    ['id' =>  '8', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Благовещенск',                  'area' => 'Амурская область',                               'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:50'],
    ['id' =>  '9', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Брянск',                        'area' => 'Брянская область',                               'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:50'],
    ['id' => '10', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Великий Новгород',              'area' => 'Новгородская область',                           'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:50'],
    ['id' => '11', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Владивосток',                   'area' => 'Приморский край',                                'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:50'],
    ['id' => '12', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Владикавказ',                   'area' => 'Республика Северная Осетия - Алания',            'district' => 'Северо-Кавказский',          'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:50'],
    ['id' => '13', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Владимир',                      'area' => 'Владимирская область',                           'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:50'],
    ['id' => '14', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Волгоград',                     'area' => 'Волгоградская область',                          'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:50'],
    ['id' => '15', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Вологда',                       'area' => 'Вологодская область',                            'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:51'],
    ['id' => '16', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Воронеж',                       'area' => 'Воронежская область',                            'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:51'],
    ['id' => '17', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Горно-Алтайск',                 'area' => 'Республика Алтай',                               'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:51'],
    ['id' => '18', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Грозный',                       'area' => 'Республика Чечня',                               'district' => 'Северо-Кавказский',          'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:51'],
    ['id' => '19', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Екатеринбург',                  'area' => 'Свердловская область',                           'district' => 'Уральский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:51'],
    ['id' => '20', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Иваново',                       'area' => 'Ивановская область',                             'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:51'],
    ['id' => '21', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Ижевск',                        'area' => 'Республика Удмуртия',                            'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:52'],
    ['id' => '22', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Иркутск',                       'area' => 'Иркутская область',                              'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:52'],
    ['id' => '23', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Йошкар-Ола',                    'area' => 'Республика Марий Эл',                            'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:52'],
    ['id' => '24', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Казань',                        'area' => 'Республика Татарстан',                           'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:52'],
    ['id' => '25', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Калининград',                   'area' => 'Калининградская область',                        'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:52'],
    ['id' => '26', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Калуга',                        'area' => 'Калужская область',                              'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:52'],
    ['id' => '27', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Кемерово',                      'area' => 'Кемеровская область',                            'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:52'],
    ['id' => '28', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Киров',                         'area' => 'Кировская область',                              'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:53'],
    ['id' => '29', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Кострома',                      'area' => 'Костромская область',                            'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:53'],
    ['id' => '30', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Краснодар',                     'area' => 'Краснодарский край',                             'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:53'],
    ['id' => '31', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Красноярск',                    'area' => 'Красноярский край',                              'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:53'],
    ['id' => '32', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Курган',                        'area' => 'Курганская область',                             'district' => 'Уральский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:53'],
    ['id' => '33', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Курск',                         'area' => 'Курская область',                                'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:53'],
    ['id' => '34', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Кызыл',                         'area' => 'Республика Тыва (Тува)',                         'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:54'],
    ['id' => '35', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Липецк',                        'area' => 'Липецкая область',                               'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:54'],
    ['id' => '36', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Магадан',                       'area' => 'Магаданская область',                            'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:54'],
    ['id' => '37', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Магас',                         'area' => 'Республика Ингушетия',                           'district' => 'Северо-Кавказский',          'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:54'],
    ['id' => '38', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Майкоп',                        'area' => 'Республика Адыгея',                              'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:54'],
    ['id' => '39', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Махачкала',                     'area' => 'Республика Дагестан',                            'district' => 'Северо-Кавказский',          'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:54'],
    ['id' => '40', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Москва',                        'area' => 'Московская область',                             'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:55'],
    ['id' => '41', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Мурманск',                      'area' => 'Мурманская область',                             'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:55'],
    ['id' => '42', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Нальчик',                       'area' => 'Республика Кабардино-Балкария',                  'district' => 'Северо-Кавказский',          'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:55'],
    ['id' => '43', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Нарьян-Мар',                    'area' => 'Ненецкий автономный округ',                      'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:55'],
    ['id' => '44', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Нижний Новгород',               'area' => 'Нижегородская область',                          'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:55'],
    ['id' => '45', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Новосибирск',                   'area' => 'Новосибирская область',                          'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:55'],
    ['id' => '46', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Омск',                          'area' => 'Омская область',                                 'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:55'],
    ['id' => '47', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Оренбург',                      'area' => 'Оренбургская область',                           'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:56'],
    ['id' => '48', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Орёл',                          'area' => 'Орловская область',                              'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:56'],
    ['id' => '49', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Пенза',                         'area' => 'Пензенская область',                             'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:56'],
    ['id' => '50', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Пермь',                         'area' => 'Пермский край',                                  'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:56'],
    ['id' => '51', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Петрозаводск',                  'area' => 'Республика Карелия',                             'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:56'],
    ['id' => '52', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Петропавловск-Камчатский',      'area' => 'Камчатский край',                                'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:56'],
    ['id' => '53', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Псков',                         'area' => 'Псковская область',                              'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:57'],
    ['id' => '54', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Ростов-на-Дону',                'area' => 'Ростовская область',                             'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:57'],
    ['id' => '55', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Рязань',                        'area' => 'Рязанская область',                              'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:57'],
    ['id' => '56', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Салехард',                      'area' => 'Ямало-Ненецкий автономный округ',                'district' => 'Уральский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:57'],
    ['id' => '57', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Самара',                        'area' => 'Самарская область',                              'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:57'],
    ['id' => '58', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Санкт-Петербург',               'area' => 'Ленинградская область',                          'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:57'],
    ['id' => '59', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Саранск',                       'area' => 'Республика Мордовия',                            'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:57'],
    ['id' => '60', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Саратов',                       'area' => 'Саратовская область',                            'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:58'],
    ['id' => '61', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Севастополь',                   'area' => 'Севастополь',                                    'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:58'],
    ['id' => '62', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Симферополь',                   'area' => 'Республика Крым',                                'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:58'],
    ['id' => '63', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Смоленск',                      'area' => 'Смоленская область',                             'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:58'],
    ['id' => '64', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Ставрополь',                    'area' => 'Ставропольский край',                            'district' => 'Северо-Кавказский',          'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:58'],
    ['id' => '65', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Сыктывкар',                     'area' => 'Республика Коми',                                'district' => 'Северо-Западный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:58'],
    ['id' => '66', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Тамбов',                        'area' => 'Тамбовская область',                             'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:59'],
    ['id' => '67', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Тверь',                         'area' => 'Тверская область',                               'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:59'],
    ['id' => '68', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Томск',                         'area' => 'Томская область',                                'district' => 'Сибирский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:59'],
    ['id' => '69', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Тула',                          'area' => 'Тульская область',                               'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:59'],
    ['id' => '70', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Тюмень',                        'area' => 'Тюменская область',                              'district' => 'Уральский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:59'],
    ['id' => '71', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Улан-Удэ',                      'area' => 'Республика Бурятия',                             'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:59'],
    ['id' => '72', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Ульяновск',                     'area' => 'Ульяновская область',                            'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:25:59'],
    ['id' => '73', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Уфа',                           'area' => 'Республика Башкортостан',                        'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:00'],
    ['id' => '74', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Хабаровск',                     'area' => 'Хабаровский край',                               'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:00'],
    ['id' => '75', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Ханты-Мансийск',                'area' => 'Ханты-Мансийский автономный округ - Югра',       'district' => 'Уральский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:00'],
    ['id' => '76', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Чебоксары',                     'area' => 'Республика Чувашия',                             'district' => 'Приволжский',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:00'],
    ['id' => '77', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Челябинск',                     'area' => 'Челябинская область',                            'district' => 'Уральский',                  'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:01'],
    ['id' => '78', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Черкесск',                      'area' => 'Республика Карачаево-Черкессия',                 'district' => 'Северо-Кавказский',          'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:01'],
    ['id' => '79', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Чита',                          'area' => 'Забайкальский край',                             'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:01'],
    ['id' => '80', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Элиста',                        'area' => 'Республика Калмыкия',                            'district' => 'Южный',                      'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:01'],
    ['id' => '81', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Южно-Сахалинск',                'area' => 'Сахалинская область',                            'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:02'],
    ['id' => '82', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Якутск',                        'area' => 'Республика Саха (Якутия)',                       'district' => 'Дальневосточный',            'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:02'],
    ['id' => '83', 'lang_key' => NULL, 'sort' => '100', 'type' => 'city', 'country' => 'Россия', 'city' => 'Ярославль',                     'area' => 'Ярославская область',                            'district' => 'Центральный',                'longitude' => NULL, 'latitude' => NULL, 'created_at' => '2024-02-27 21:26:02']];    
}//pa($wo["cities"]);

$wo["config"]["currency_array"]        = (array) json_decode($wo["config"]["currency_array"]);
$wo["config"]["currency_symbol_array"] = (array) json_decode($wo["config"]["currency_symbol_array"]);
$wo["config"]["providers_array"]       = (array) json_decode($wo["config"]["providers_array"]);
if (!empty($wo["config"]["exchange"])) {
    $wo["config"]["exchange"] = (array) json_decode($wo["config"]["exchange"]);
}
$wo["currencies"] = array();
foreach ($wo["config"]["currency_symbol_array"] as $key => $value) {
    $wo["currencies"][] = array(
        "text" => $key,
        "symbol" => $value
    );
}
if (!empty($_GET["theme"])) {
    Wo_CleanCache();
}
$wo["post_colors"] = array();
if ($wo["config"]["colored_posts_system"] == 1) {
    $wo["post_colors"] = Wo_GetAllColors();
}


$wo['manage_pro_features'] = array('funding_request' => 'can_use_funding',
                                   'job_request' => 'can_use_jobs',
                                   'game_request' => 'can_use_games',
                                   'market_request' => 'can_use_market',
                                   'event_request' => 'can_use_events',
                                   'forum_request' => 'can_use_forum',
                                   'groups_request' => 'can_use_groups',
                                   'pages_request' => 'can_use_pages',
                                   'audio_call_request' => 'can_use_audio_call',
                                   'video_call_request' => 'can_use_video_call',
                                   'offer_request' => 'can_use_offer',
                                   'blog_request' => 'can_use_blog',
                                   'movies_request' => 'can_use_movies',
                                   'story_request' => 'can_use_story',
                                   'stickers_request' => 'can_use_stickers',
                                   'gif_request' => 'can_use_gif',
                                   'gift_request' => 'can_use_gift',
                                   'nearby_request' => 'can_use_nearby',
                                   'video_upload_request' => 'can_use_video_upload',
                                   'audio_upload_request' => 'can_use_audio_upload',
                                   'shout_box_request' => 'can_use_shout_box',
                                   'colored_posts_request' => 'can_use_colored_posts',
                                   'poll_request' => 'can_use_poll',
                                   'live_request' => 'can_use_live',
                                   'profile_background_request' => 'can_use_background',
                                   'affiliate_request' => 'can_use_affiliate',
                                   'chat_request' => 'can_use_chat',
                                   'ai_image_use' => 'can_use_ai_image',
                                   'ai_post_use' => 'can_use_ai_post',
                                   'ai_user_use' => 'can_use_ai_user',
                                   'ai_blog_use' => 'can_use_ai_blog',
                               );
$wo['available_pro_features'] = array();
$wo['available_verified_features'] = array();

foreach ($wo['manage_pro_features'] as $key => $value) {
    $wo['config'][$value] = true;
    if ($wo["loggedin"] && !empty($wo['user'])) {
        if ($wo['config'][$key] == 'verified' && !$wo['user']['verified']) {
            $wo['config'][$value] = false;
        }
        if ($wo['config'][$key] == 'admin' && !$wo['user']['admin']) {
            $wo['config'][$value] = false;
        }
        if ($wo['config'][$key] == 'pro' && !$wo['user']['is_pro']) {
            $wo['config'][$value] = false;
        }
        if ($wo['config'][$key] == 'pro' && $wo['user']['is_pro'] && !empty($wo["pro_packages"][$wo['user']['pro_type']]) && $wo["pro_packages"][$wo['user']['pro_type']][$value] != 1) {
            $wo['config'][$value] = false;
        }
        if ($wo['user']['admin']) {
            $wo['config'][$value] = true;
        }
    }
    if ($wo['config'][$key] == 'pro') {
        $wo['available_pro_features'][$key] = $value;
    }
    if ($wo['config'][$key] == 'verified') {
        $wo['available_verified_features'][$key] = $value;
    }
}
if (!$wo['config']['can_use_stickers']) {
    $wo['config']['stickers_system'] = 0;
}
if (!$wo['config']['can_use_gif']) {
    $wo['config']['stickers'] = 0;
}
if (!$wo['config']['can_use_gift']) {
    $wo['config']['gift_system'] = 0;
}
if (!$wo['config']['can_use_nearby']) {
    $wo['config']['find_friends'] = 0;
}
if (!$wo['config']['can_use_video_upload']) {
    $wo['config']['video_upload'] = 0;
}
if (!$wo['config']['can_use_audio_upload']) {
    $wo['config']['audio_upload'] = 0;
}
if (!$wo['config']['can_use_poll']) {
    $wo['config']['post_poll'] = 0;
}
if (!$wo['config']['can_use_background']) {
    $wo['config']['profile_back'] = 0;
}
if (!$wo['config']['can_use_chat']) {
    $wo['config']['chatSystem'] = 0;
}
if ($wo['config']['ai_image_system'] == 0 && in_array('ai_image_use',array_keys($wo['available_pro_features']))) {
    unset($wo['available_pro_features']['ai_image_use']);
}
if ($wo['config']['ai_post_system'] == 0 && in_array('ai_post_use',array_keys($wo['available_pro_features']))) {
    unset($wo['available_pro_features']['ai_post_use']);
}
if ($wo['config']['ai_user_system'] == 0 && in_array('ai_user_use',array_keys($wo['available_pro_features']))) {
    unset($wo['available_pro_features']['ai_user_use']);
}
if ($wo['config']['ai_blog_system'] == 0 && in_array('ai_blog_use',array_keys($wo['available_pro_features']))) {
    unset($wo['available_pro_features']['ai_blog_use']);
}
if (!$wo['config']['can_use_ai_image']) {
    $wo['config']['ai_image_system'] = 0;
}
if (!$wo['config']['can_use_ai_post']) {
    $wo['config']['ai_post_system'] = 0;
}
if (!$wo['config']['can_use_ai_user']) {
    $wo['config']['ai_user_system'] = 0;
}
if (!$wo['config']['can_use_ai_blog']) {
    $wo['config']['ai_blog_system'] = 0;
}

$wo['config']['report_reasons'] = json_decode($wo['config']['report_reasons'],true);


$wo['config']['filesVersion'] = "4.2.1";

if ($wo['config']['filesVersion'] != $wo['config']['version']) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}
$wo['reserved_usernames'] = array();
if (!empty($wo['config']['reserved_usernames'])) {
    $wo['reserved_usernames'] = explode(',', $wo['config']['reserved_usernames']);
}

$wo['config']['maxCharacters'] = 5000;   

//echo $sql_db_user; //exit; pa($wo['config']);
