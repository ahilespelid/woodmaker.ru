<?php
// +------------------------------------------------------------------------+
// | @author Ahiles Pelid (Ltd. TRIZ)
// | @author_url 1: http://www.ahilespelid.ru
// | @author_url 2: http://t.me/ahilespelid
// | @author_email: ahilespelid@gmail.com
// +------------------------------------------------------------------------+
// | 
// | 
// +------------------------------------------------------------------------+
///*/ ahilespelid ///*/
    ///*/ Функция выбора категорий ///*/
if(!function_exists('Wo_GetCategoriesWithSub')){function Wo_GetCategoriesWithSub($type = 'all', $get_sub = true){
    global $sqlConnect, $wo; $data = [];
    $query = "SELECT * FROM " . T_CATEGORIES . (('all' != $type) ? " WHERE `type`='{$type}'" : '') . " ORDER BY `sort` ASC, `id` ASC;"; //pa($query);
    $cats = $sqlConnect->query($query);
    if($cats->num_rows > 0){ //pa($query_categories);
        foreach($cats as $cat){//pa($categorie);
            if(isset($wo['lang'][$cat['lang_key']])){$data[$cat['id']] = $cat; $data[$cat['id']]['name'] = $wo['lang'][$cat['lang_key']];}
            if($get_sub){
            $subs = $sqlConnect->query($query_sub = "SELECT * FROM " . T_CATEGORIES . " WHERE `sub`='".$cat["id"]."';");
            if($subs->num_rows > 0){
                foreach($subs as $sub){$sub['name'] = $wo["lang"][$sub["lang_key"]]; $data[$cat["id"]][] = $sub;}
            }}
    }}//pa($data); 
return $data ?? false;}}

    ///*/ Функция выбора категории по id ///*/
if(!function_exists('Wo_GetCategoriesOne')){function Wo_GetCategoriesOne($id) {
    if(empty($id) && !is_numeric($id)){return false;}
    global $sqlConnect, $wo;
    $cat = $sqlConnect->query($query = "SELECT * FROM " . T_CATEGORIES . " WHERE `id`='$id' LIMIT 1;");
return $cat->fetch_assoc() ?? false;}}

    ///*/ Функция выбора гео объектов ///*/    
if(!function_exists('Wo_GetGeoObjects')){function Wo_GetGeoObjects($type = 'all'){
    global $sqlConnect, $wo; $data = [];
    $query = "SELECT * FROM " . T_GEO . (('all' != $type) ? " WHERE `type`='{$type}'" : '') . " ORDER BY `sort` ASC, `id` ASC;"; ////pa($query);
    $cities = $sqlConnect->query($query); 
return ($cities->num_rows > 0) ? $cities->fetch_all(MYSQLI_ASSOC) : false;}}

    ///*/ Функция выбора гео объекта по id ///*/
if(!function_exists('Wo_GetGeoObjectOne')){function Wo_GetGeoObjectOne($id) {
    if(empty($id) && !is_numeric($id)){return false;}
    global $sqlConnect, $wo;
    $cat = $sqlConnect->query($query = "SELECT * FROM " . T_GEO . " WHERE `id`='$id' LIMIT 1;");
return $cat->fetch_assoc() ?? false;}}

    ///*/ Функция обновления гео объекта по id ///*/
if(!function_exists('Wo_UpdateGeoObjectOne')){function Wo_UpdateGeoObjectOne($id, $data) {
    if(empty($id) && empty($data) && !is_numeric($id) && !is_array($data)){return false;}
    global $sqlConnect, $wo; $e = [];
    foreach($data as $k=>$v){
        $e[] = (is_string($v)) ? $k."='".$sqlConnect->real_escape_string($v)."'" : 
               ((is_numeric($v)) ? $k.'='.$v : $k.'=NULL');
    }$string_e  = implode(", ", $e);
    $query = "UPDATE " . T_GEO . " SET ".$string_e." WHERE `id`= $id;";
return $sqlConnect->query($query);}}

    ///*/ Функция выбора языковой метки по id ///*/
if(!function_exists('Wo_GetLangOne')){function Wo_GetLangOne($id) {
    if(empty($id) && !is_numeric($id)){return false;}
    global $sqlConnect, $wo;
    $geo = $sqlConnect->query($query = "SELECT * FROM " . T_LANGS . " WHERE `id`='$id' LIMIT 1;");
return $geo->fetch_assoc() ?? false;}}


///*/ Функция переводда текста через Google ///*/
if(!function_exists('Wo_GoogleTanslate')){function Wo_GoogleTanslate($text, $from_lan = 'ru', $to_lan = 'en', $key = 'AIzaSyBOti4mM-6x9WDnZIjIeyEU21OpBXqWBgw'){
    $obj = json_decode(file_get_contents('https://translation.googleapis.com/language/translate/v2?q='.urlencode($text).'&source='.$from_lan.'&target='.$to_lan .'&format=text&key='.$key), true);
    $ret = trim($obj['data']['translations']['0']['translatedText']);
return (empty($ret)) ? false : $ret;}}

///*/ Функция сравнения гео объектов с языковыми метками ///*/
if(!function_exists('Wo_GeoMergeLang')){function Wo_GeoMergeLang($geo_column = 'city', $lang_column = 'russian'){
    global $sqlConnect, $wo; $data = [];
    $q = "SELECT  g.*, l.id AS lid, l.lang_key AS tag_lang_key, l.english, l.russian FROM " . T_GEO . " g LEFT JOIN " . T_LANGS . " l ON LOWER(l.{$lang_column}) LIKE LOWER(g.{$geo_column}) WHERE g.`type`='{$geo_column}' ORDER BY g.sort ASC, g.id ASC;"; //pa($q);
    $geo = $sqlConnect->query($q);
return ($geo->num_rows > 0) ? $geo->fetch_all(MYSQLI_ASSOC) : false;}}

///*/ Функция обновления гео объектов и создания языковых меток под гео объекты если таковых нет ///*/
if(!function_exists('Wo_GeoUpdateOrInsertLang')){function Wo_GeoUpdateOrInsertLang($translate_geo_column = 'city'){
    global $sqlConnect;
    foreach(Wo_GeoMergeLang($translate_geo_column) as $geo){ //pa($geo);
        if($tag = Wo_GoogleTanslate($geo[$translate_geo_column])){
            if(empty($russian = $geo[$translate_geo_column])){continue;}
            $res = $sqlConnect->query("SELECT l.id, l.russian FROM Wo_Langs l WHERE LOWER(l.russian) LIKE LOWER('".mysqli_real_escape_string($sqlConnect, $russian)."') LIMIT 1;");
            if($res->num_rows > 0 && !empty($lang_id = $res->fetch_assoc()['id'])){
                $geo['lang_key'] = $lang_id;
            }else{
                $id = $geo['id'];
                $tag_lang_key = 'geo_'.mb_strtolower($geo['type']).'_'.str_replace(' ', '-', mb_strtolower(str_replace(["'",'"','`'],'',$tag)));
                $russian = mysqli_real_escape_string($sqlConnect, $russian);
                $english = mysqli_real_escape_string($sqlConnect, $tag);
                
                if($sqlConnect->query("INSERT INTO " . T_LANGS . " (lang_key, type, english, russian) VALUES('{$tag_lang_key}', 'geo', '{$english}', '{$russian}') ON DUPLICATE KEY UPDATE lang_key='{$tag_lang_key}', type='geo', english='{$english}', russian='{$russian}';")){
                    $get_last_id = $sqlConnect->query("SELECT MAX(id) as id FROM " . T_LANGS . ";");
                    if($get_last_id->num_rows > 0 && !empty($lang_id = $get_last_id->fetch_assoc()['id'])){
                        $geo['lang_key'] = $lang_id;
        }}}} unset($geo['lid'], $geo['tag_lang_key'], $geo['english'], $geo['russian']);
    if(!empty($lang_key = $geo['lang_key']) && !empty($geo_id = $geo['id']) && is_numeric($lang_key) && is_numeric($geo_id) && $sqlConnect->query("UPDATE " . T_GEO . " SET lang_key='{$lang_key}' WHERE id='{$geo_id}';")){
        $ret[] = $geo;
    }} 
return (empty($ret)) ? false: $ret;}}

///*/ Функция создания нового пользовательского имени ///*/
if(!function_exists('Wo_NewUsername')){function Wo_NewUsername($pre='id'){
    global $sqlConnect;
    
    $show = $sqlConnect->query($q = "SHOW TABLE STATUS WHERE `Name` = '" . T_USERS . "';");
    $ret = (array) $show->fetch_assoc(); $ret = $ret['Auto_increment'];
    
    $new_username = (is_numeric($ret)) ? $pre.$ret : false;
return ($new_username && (!Wo_IsNameExist($new_username)[0])) ? $new_username : false;}}

///*/ Функция выбора всех пользовательских полей по id ///*/
if(!function_exists('Wo_UserFieldsAll')){function Wo_UserFieldsAll($id){
    global $sqlConnect; 
    
    $show = $sqlConnect->query($q = "SELECT * FROM " . T_USERS_FIELDS . " u WHERE u.user_id={$id};");
    $ret = (array) $show->fetch_assoc();

return (empty($ret)) ? false : $ret;}}

///*/ Функция выбора наиболее подходящего слова из массива слов ///*/
if(!function_exists('Wo_Levenshtein')){function Wo_Levenshtein($input = '', $words = []){
    // введённое слово $input = 'carrrot'; массив сверяемых слов $words  = ['orange','radish','carrot'];   
    $shortest = -1;
    foreach($words as $word){
        $lev = levenshtein($input, $word);
        if($lev == 0){
            $ret = $word;
            $shortest = 0;
        break;}

        if($lev <= $shortest || $shortest < 0){
            $ret  = $word;
            $shortest = $lev;
    }}
return (empty($ret)) ? false : $ret;}}

///*/ Функция выбора активности пользователя по user_id ///*/
if(!function_exists('Wo_GetActivitiesUser')){function Wo_GetActivitiesUser($user_id, $period_ot = '', $period_do = ''){
    if(empty($user_id)){return false;}
    global $sqlConnect;
    
    $period_ot = (empty($period_ot)) ? false : is_date($period_ot); 
    $period_do = (empty($period_do)) ? false : is_date($period_do);

    $AND = ($period_ot) ? ' AND `time`<='.$period_ot->getTimestamp() : '';
    $AND.= ($period_do) ? ' AND `time`>='.$period_do->getTimestamp() : '';
    
    $query = $sqlConnect->query($q = "SELECT * FROM " . T_ACTIVITIES . " u WHERE u.user_id={$user_id}{$AND};"); //pa($q);
    $ret = (array) $query->fetch_all(MYSQLI_ASSOC);

return (empty($ret)) ? false : $ret;}}

///*/ Функция выбора друзей user_id ///*/
if(!function_exists('Wo_GetFriends')){function Wo_GetFriends($user_id){
    if(empty($user_id)){return false;}
    global $sqlConnect; 
    
    $user_id = Wo_Secure($user_id);

    $q = "SELECT f1.*
                FROM " . T_FOLLOWERS . " f1 INNER JOIN " . T_FOLLOWERS . " f2 ON f1.following_id = f2.following_id
                WHERE f1.follower_id = {$user_id} AND 
                f2.follower_id = {$user_id} AND
                f1.`following_id` NOT IN (SELECT `blocked` FROM " . T_BLOCKS . " WHERE `blocker` = '{$user_id}') AND
                f1.`following_id` NOT IN (SELECT `blocker` FROM " . T_BLOCKS . " WHERE `blocked` = '{$user_id}') AND
                f1.active = 1 GROUP BY following_id;";
        
    $query = $sqlConnect->query($q);
    $ret = (array) $query->fetch_all(MYSQLI_ASSOC);

return (empty($ret)) ? false : $ret;}}

///*/ Функция выбора активности пользователя по user_id ///*/
if(!function_exists('Wo_GetUserER')){function Wo_GetUserER($user_id){
    if(empty($user_id)){return false;}
    global $sqlConnect;
    
    $period_ot = date('d.m.Y'); $period_do = date('d.m.Y', strtotime($period_ot. " - 1080 day"));
    $activity = Wo_GetActivitiesUser($user_id, $period_ot, $period_do);
    $friends = Wo_GetFriends($user_id);
    
    $ret = (empty($activity)) ? 0 : (count($friends) / count($activity)) * 100;  //pa((count($friends) / count($activity))); pa(count($activity)); pa(count($friends));

return (float) $ret;}}


///*/ ahilespelid ///*/
///*/ Musa функция транслирует дату в зависимости от выбранного языка ///*/
function Wo_SetDateLangBlog() {
    global $wo;

    $ru_month = array( 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' );
    $en_month = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
    if ($_SESSION["lang"] == "russian"){ 
        echo date("d",$wo['blog']['posted'])." " .str_replace($en_month, $ru_month, date('F',$wo['blog']['posted']))." ". date("Y",$wo['blog']['posted']); 
    } else {
        echo date("d M Y",$wo['blog']['posted']);
    } 
    return false;
}
///*/ Musa ///*/
?>