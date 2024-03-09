<?php 
if ($f == "search_change_filters") {
    $data = array(
        'status' => 200,
        'html' => ''
    );
    if(!empty($_GET['type'])) {
        if('groups' == $_GET['type']) {
            $data['html'] .= Wo_LoadPage('search/filters/group_filters');
        } elseif('users' == $_GET['type']) {
            $data['html'] .= Wo_LoadPage('search/filters/user_filters');
        } elseif('pages' == $_GET['type']) {
            $data['html'] .= Wo_LoadPage('search/filters/page_filters');
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
