<?php
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET, POST');

if(isset($_POST['file'])){
    $img = $_POST['file'];
    saveImage($img); 
}
else{
    echo "nothing to do.";
}
function saveImage($url){
    define('UPLOAD_DIR', '../upload/Image/');
    $image_parts = explode(";base64,", $url);
	$image_type_aux = explode("image/", $image_parts[0]);
	$image_type = $image_type_aux[1];
	$image_base64 = base64_decode($image_parts[1]);
	$file = UPLOAD_DIR . uniqid() . '.png';
    //meta data image filename
    //$file eto yung code na isasave sa database

    //eto yung mag sasave na sa folder na image/
	file_put_contents($file, $image_base64);
}
    
?>
    