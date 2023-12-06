<?php 
if ($f == 'mention') {
    $html_data  = array();
    $data_finel = array();
    if(!empty($s)) {
        $following  = Wo_GetFollowingSug(5, $_GET['term'], $s);
    } else {
        $following  = Wo_GetFollowingSug(5, $_GET['term']);
    }
    header("Content-type: application/json");
    echo json_encode(array(
        $following
    ));
    exit();
}
