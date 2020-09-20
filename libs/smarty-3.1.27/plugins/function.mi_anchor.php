<?php/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 */
/**
 * Smarty {mi_anchor} function plugin
 * Type:     function<br>
 * Name:     html_image<br>
 * Date:     Feb 24, 2003<br>
 * Purpose:  format HTML tags for the image<br>
 * Examples: {html_image file="/images/masthead.gif"}<br>
 * Output:   <img src="/images/masthead.gif" width=400 height=23><br>
 * Params:
 * <pre>
 * - link        - (required) - file (and path) of image
 * - coolurl     - (optional) - image height (default actual height)
 * - cachedForPage  - (optional) - image width (default actual width)
 * - mi     - (optional) - base directory for absolute paths, default is environment variable DOCUMENT_ROOT
 * </pre>
 *
 * @link    http://www.smarty.net/manual/en/language.function.html.image.php {html_image}
 *          (Smarty online manual)
 * @author  Monte Ohrt <monte at ohrt dot com>
 * @author  credits to Duda <duda@big.hu>
 * @version 1.0
 *
 * @param array                    $params   parameters
 * @param Smarty_Internal_Template $template template object
 *
 * @throws SmartyException
 * @return string
 * @uses    smarty_function_escape_special_chars()
 */
function smarty_function_mi_anchor($params, $template){
	$mi = NULL;

	$link = NULL;
	$coolurl = NULL;
	$cachedForPage = true;
	$href = '';

	foreach($params as $key => $val){
		switch($key){
			case 'link':
			case 'coolurl':
			case 'cachedForPage':
			case 'mi':
				$$key = $val;
				break;
		}
	}

	if($mi === NULL) {
        trigger_error("mi_anchor: missing 'mi' parameter", E_USER_NOTICE);
        return;
    }elseif(empty($mi)){
        trigger_error("mi_anchor: 'mi' is empty", E_USER_NOTICE);
        return;
    }

    if(!empty($link)){
		$href = $link;
    }else{
    	if(!empty($mi['httplink'])){
    		if(!preg_match('~^https?://~', $mi['httplink'])){
    			$href = $template->smarty->_tpl_vars['http_base'];
    		}
    		$href .= $mi['httplink'];
    	}else{
    		$template->smarty->loadPlugin('smarty_function_link');
	    	if(empty($mi['coolurl'])){
	    		OBE_Trace::dump($mi);
	    		trigger_error("mi_anchor: 'mi.coolurl' is empty", E_USER_NOTICE);
	    		return;
	    	}
	    	$href = smarty_function_link(['pg' => $mi['coolurl'], 'cachedForPage' => $cachedForPage], $template);
    	}
    }

	$ret = ' href="' . $href . '"';
	if($mi['popup']){
		$ret .= ' target="_blank"';
	}
	if($mi['nofollow']){
		$ret .= ' rel="nofollow"';
	}
	return $ret;
}