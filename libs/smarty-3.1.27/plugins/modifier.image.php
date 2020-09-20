<?php
function smarty_modifier_image($img, $resize = null, $crop = null){

    if($img === '') {
        trigger_error("obe_image: missing 'image' parameter", E_USER_NOTICE);
        return;
    }elseif(empty($img)){
        trigger_error("obe_image: 'image' is empty", E_USER_NOTICE);
        return;
    }elseif(empty($img->src)){
    	return;
    }

    if($resize){
    	$dims = explode('x', $resize);
    	$cut = OBE_Attachment::RESIZE_NORMAL;
    	if($crop == 'in'){
    		$cut = OBE_Attachment::RESIZE_INSIDE_CUT;
    	}

    	$img = clone $img;
    	$img->setOutSize($dims, $cut);
		$img->createThumbNail($cut);

    	return $img->src;
    }else{
    	return $img->src;
    }
}