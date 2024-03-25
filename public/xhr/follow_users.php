<?php 
if ($f == 'follow_users') {
    if (!empty($_POST['user'])) {
        $continue = false;
        $ids      = @explode(',', $_POST['user']);
        foreach ($ids as $id) {
            if (Wo_RegisterFollow($id, $wo['user']['user_id']) === true) {
                $continue = true;
            }
        }
        pa(Wo_UserData($wo['user']['user_id']));
        Wo_UpdateUserData($wo['user']['user_id'], array(
            'startup_follow' => '1',
            'start_up' => '1'
        ));
        pa(Wo_UserData($wo['user']['user_id']));
        $user_data = Wo_UpdateUserDetails($wo['user']['user_id'], false, false, true);
        pa(Wo_UserData($wo['user']['user_id']));
        $data = array(
            'status' => 200
        );
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
