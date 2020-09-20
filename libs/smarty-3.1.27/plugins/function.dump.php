<?php

function smarty_function_dump($params, $template){
	$result = '';

	$dd = function_exists('dd');

	foreach($params as $key => $item){
		if($dd){
			dd($item);
		}
		$result .= $key . ":\n" . print_r($item, true);
	}

	if(!empty($result)){
		$result = '<pre>' . $result . '</pre>';
	}
	return $result;
}
?>