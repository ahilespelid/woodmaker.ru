<?php 
if ($f == 'load_posts') {
    if(empty($wo['user']['email'])){}
    
    $wo['page'] = 'home';
    $load = sanitize_output(Wo_LoadPage('home/load-posts'));
    echo $load;
    exit();
}
