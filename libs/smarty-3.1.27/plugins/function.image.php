<?php
function smarty_function_image($params, $template){
    require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');

    $alt = '';
    $path = '';
    /**
     * @var OBE_Attachment
     */
    $image = null;
    $title = '';
	$class = '';
    $extra = '';
    $resize = null;
    $crop = null;
    $dataUrl = false;
    $no_wh = false;

    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'image':
            case 'alt':
            case 'path':
            case 'title':
            case 'class':
            case 'resize':
            case 'crop':
            case 'dataUrl':
            case 'no_wh':
                $$_key = $_val;
                break;
            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    trigger_error("obe_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if($image === '') {
        trigger_error("obe_image: missing 'image' parameter", E_USER_NOTICE);
        return;
    }elseif(empty($image)){
        trigger_error("obe_image: 'image' is empty", E_USER_NOTICE);
        return;
    }elseif(empty($image->src)){
    	return;
    }

    if(!empty($title)){
    	$alt = $title;
    }else{
    	$alt .= $image->desc;
    }

    if(!empty($alt)){
    	$alt = ' alt="'.$alt.'"';
    }

    if(!empty($class)){
    	$class = ' class="'.$class.'"';
    }

    if($resize){
    	$dims = explode('x', $resize);

    	$cut = OBE_Attachment::RESIZE_NORMAL;
    	if($crop == 'in'){
    		$cut = OBE_Attachment::RESIZE_INSIDE_CUT;
    	}

    	if($image->fsize > 0){
	    	$image->setOutSize($dims, $cut);
			$image->createThumbNail($cut);
    	}
    }

    $src = '';
    $wh = '';

    if($dataUrl && (OBE_AppCore::$ieVersion === NULL || OBE_AppCore::$ieVersion > 7)){
    	$src = $image->GetBase64ContentMimePrefix();
    }else{
    	$src = $path . trim($image->src, './');
    }

    if(!$no_wh){
    	$wh = ' width="' . ceil($image->size['w']) . '" height="' . ceil($image->size['h']) . '"';
    }

    return '<img src="' . $src . '"' . $alt . $wh . $class . $extra . ' />';
}