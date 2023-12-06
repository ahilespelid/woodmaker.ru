<?php
    if($f == "get_user") {
        if (empty($_GET["user_id"])) {
            $error           = true;
            $data = array(
                'status' => 500,
                'message' => $wo['lang']['empty']
            );
        } else {
            $html = "";
            $user = Wo_GetUserById($_GET["user_id"]);
            foreach ($user as $wo['part']) {
                $html .= Wo_LoadPage('chat/chat-part-list-preview');
            }
            $data = array(
                'status' => 200,
            );
            $data['html'] = $html;
        }
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
?>