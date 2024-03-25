<?php 
if ($f == "search") {
    $data = array(
        'status' => 200,
        'html' => ''
    );
    if ($s == 'recipients' AND $wo['loggedin'] == true && isset($_GET['query'])) {
        foreach (Wo_GetMessagesUsers($wo['user']['user_id'], $_GET['query']) as $wo['recipient']) {
            $data['html'] .= Wo_LoadPage('messages/messages-recipients-list');
        }
    }
    if($s == 'search_page') {
        $offset = 0;
        if(!empty($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if($_GET['type'] == 'users') {
            $search_query = Wo_GetSearchFilter($_GET, 30);
            if (count($search_query) != 0) {
                foreach ($search_query as $wo['result']) {
                    if (!empty($wo['result']['avatar_full'])) {
                        $wo['result']['avatar'] = Wo_GetMedia($wo['result']['avatar_full']);
                    }
                    $data['html'] .= Wo_LoadPage('search/user-result');
                }
            }
        } else {
            $response = Wo_SearchForSearchPage($_GET['type'], $_GET['query'], 30, $offset);
            if (count($response) != 0) {
                foreach ($response as $wo['result']) {
                    if('page' == $wo['result']['type']) {
                        $data['html'] .= Wo_LoadPage('search/page-result');
                    } else {
                        $data['html'] .= Wo_LoadPage('search/group-result');
                    }
                }
            }
        }
    }
    if ($s == 'normal' && isset($_GET['query']) || isset($_GET['name'])) {
        if($_GET["p"] == "chat") {
            foreach (Wo_GetSearch($_GET['query'], true) as $wo['part']) {
                $data['html'] .= Wo_LoadPage('chat/chat-part-list');
            }
        } else {
            foreach (Wo_GetSearch($_GET['query']) as $wo['result']) {
                $data['html'] .= Wo_LoadPage('header/search');
            }
        }
    }
    if ($s == 'hash' && isset($_GET['query'])) {
        foreach (Wo_GetSerachHash($_GET['query']) as $wo['result']) {
            $data['html'] .= Wo_LoadPage('header/hashtags-result');
        }
    }
    if ($s == 'recent' && $wo['loggedin'] == true) {
        foreach (Wo_GetRecentSerachs() as $wo['result']) {
            if (!empty($wo['result'])) {
                $data['html'] .= Wo_LoadPage('header/search');
            }
            
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
