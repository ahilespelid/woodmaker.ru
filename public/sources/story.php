<?php
$wo['content']     = '';
$wo['description'] = '';
$wo['keywords']    = '';
$wo['page']        = 'story';
$wo['title']       = '';
$wo['title'] .= ' | ' . $wo['config']['siteName'];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $placement = '';
    if ($wo['loggedin'] == true) {
        if (Wo_IsAdmin()) {
            $placement = 'admin';
        }
    }
    $id = Wo_GetPostIdFromUrl($_GET['id']);
    if (empty($id) || !is_numeric($id)) {
        header("Location: " . $wo['config']['site_url']);
        exit();
    }
    if (!empty($_GET['ref']) && is_numeric($_GET['ref']) && $_GET['ref'] > 0) {
        $wo['story'] = Wo_PostData($id, $placement, 'not_limited', Wo_Secure($_GET['ref']));
    } else {
        $wo['story'] = Wo_PostData($id, $placement, 50);
    }
    if (empty($wo['story'])) {
        header("Location: " . $wo['config']['site_url']);
        exit();
    } else if (empty($wo['story']['post_id'])) {
        header("Location: " . $wo['config']['site_url']);
        exit();
    } else if (Wo_PostExists($wo['story']['post_id']) === false) {
        header("Location: " . $wo['config']['site_url']);
        exit();
    }
    //Муса: Закомментировал строку снизу т.к она добавляла просмотры каждый раз когда человек открывал пост, пока не привело ни к каким ошибкам но на всякий случай оставлю комментарий
	// $sql_query         = mysqli_query($sqlConnect, "UPDATE `Wo_Posts` SET `post_views` = `post_views` + 1 WHERE `id` = '$id'");
    $wo['story']['page'] = 1;
    $wo['content']       = Wo_LoadPage('story-content/content');
    $wo['description']   = Wo_Secure(mb_substr($wo['story']['Orginaltext'], 0, 156, "utf-8"));
    $wo['description']   = str_replace('<br>', "", $wo['description']);
    $wo['description']   = str_replace('</ br>', "", $wo['description']);
    $wo['description']   = str_replace('<br />', "", $wo['description']);
    $wo['keywords']      = '';
} else {
    header("Location: " . $wo['config']['site_url']);
    exit();
}
if (!empty($wo['description'])) {
    $wo['title'] =  FilterStripTags(Wo_Secure(Wo_GetShortTitle($wo['story']['Orginaltext'], false, 50)));
} else {
    $wo['title'] = $wo['config']['siteTitle'];
}
if (empty($wo['description'])) {
    $wo['description'] = $wo['config']['siteDesc'];
}
if (!empty($wo['story']['album_name'])) {
    $wo['title'] = FilterStripTags(Wo_Secure($wo['story']['album_name']));
}
if (!empty($wo['story']['product_id'])) {
    $wo['description'] = FilterStripTags(strip_tags(Wo_Secure($wo['story']['product']['description'])));
    $wo['title']       = FilterStripTags(Wo_Secure($wo['story']['product']['name']));
}
?>
