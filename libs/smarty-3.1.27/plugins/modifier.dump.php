<?php
function smarty_modifier_dump($var){
	echo  '<pre>' . var_export($var, true) . '</pre>';
}
?>