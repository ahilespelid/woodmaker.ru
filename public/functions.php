<?php
if(!function_exists('pa')){
    function pa($a,$br=0,$mes='',$t='pre'):bool{$backtrace = debug_backtrace(); $fileinfo = '';$sbr='';
        if(!empty($backtrace[0]) && is_array($backtrace[0])){$fileinfo = $backtrace[0]['file'] . ":" . $backtrace[0]['line'];}
        while($br){$sbr.=(empty($t) ? PHP_EOL : '<br>');$br--;}
        echo $fileinfo.$sbr.$mes.(empty($t) ? '' : '<'.$t.'>'); print_r($a=(!empty($a)?$a:[])); echo(empty($t) ? '' : '</'.$t.'>').PHP_EOL;
        return true;
}}
if(!function_exists('is_date')){
function is_date($value){ // */  проверка строки на дату  // */
    if(!$value){return null;}
    try{return $d = (new \DateTime($value));}catch(\Exception $e){return null;}}
}
?>