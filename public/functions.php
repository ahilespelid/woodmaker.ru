<?php
if(!function_exists('dd')){
    function dd($a,$br=0,$mes='',$t='pre'):bool{$backtrace = debug_backtrace(); $fileinfo = '';$sbr='';
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        foreach ($backtrace as $trace) {
            $caller = $trace;
            
            $file = $caller['file'];
            $line = $caller['line'];
            $function = $caller['function'];
            $class = isset($caller['class']) ? $caller['class'] : '';
            
            echo "File: $file" . PHP_EOL;
            echo "Line: $line" . PHP_EOL;
            echo "Function: $function" . PHP_EOL;
            echo "Class: $class" . PHP_EOL;
            echo '<br><br>';
        }
        foreach ($args as $arg) {
            var_dump($arg);
        }        
        echo $fileinfo.$sbr.$mes.(empty($t) ? '' : '<'.$t.'>'); print_r($a=(!empty($a)?$a:[])); echo(empty($t) ? '' : '</'.$t.'>').PHP_EOL;
        
        exit(1);
    }

}if(!function_exists('pa')){
    function pa($a,$br=0,$mes='',$t='pre'):bool{$backtrace = debug_backtrace(); $fileinfo = '';$sbr='';
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        foreach ($backtrace as $trace) {
			$caller = $trace;
			
			$file = $caller['file'];
			$line = $caller['line'];
			$function = $caller['function'];
			$class = isset($caller['class']) ? $caller['class'] : '';
			
			echo "File: $file" . PHP_EOL;
			echo "Line: $line" . PHP_EOL;
			echo "Function: $function" . PHP_EOL;
			echo "Class: $class" . PHP_EOL;
			echo '<br><br>';
        }
        foreach ($args as $arg) {
            var_dump($arg);
        }		
        echo $fileinfo.$sbr.$mes.(empty($t) ? '' : '<'.$t.'>'); print_r($a=(!empty($a)?$a:[])); echo(empty($t) ? '' : '</'.$t.'>').PHP_EOL;
    }

}
if(!function_exists('is_date')){
function is_date($value){ // */  проверка строки на дату  // */
    if(!$value){return null;}
    try{return $d = (new \DateTime($value));}catch(\Exception $e){return null;}}
}
?>
