<?php 
if ($f == 'open_lightbox') {
    $html = '';
    if (!empty($_GET['post_id'])) {
        if($s != 'pinched') {
            $wo['story'] = Wo_PostData($_GET['post_id'], "", "5");
        } else {
            $wo['story'] = Wo_PostData($_GET['post_id'], "", "5", "", true);
        }
        if (!empty($wo['story'])) {
            $html = Wo_LoadPage('lightbox/content');
        }
    }
    $data = array(
        'status' => 200,
        'html' => $html
    );
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
