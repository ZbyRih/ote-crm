<?php
/**
* Smarty {html_select} plugin
*
* Type: function<br>
* Name: html_selectbr>
* Purpose: Prints the dropdowns for a <select> input box. Supports multiple
* selections as well.
*
* @link (none)
* @version 1.0 (12/31/05)
* @author Jason Stark
* @param array
* @param Smarty
* @return string
*
* ----------------
*
* Parameters: Those without default values should be required.
* name = The name attribute of the select tag: <select name="$name">
* size = The number of visible rows to show: <select size="$size">
* default: is not included
* blank = Whether to include a blank/null option at the beginning
* default: false
* extra = Any "extra" html attributes to include in the select tag.
* example: class="selectclass" style="width: 100%"
* default: is not included
* multiple = Whether this selection box allows multiple items to be
* selected. i.e.: <select multiple>
* default: false
* options = An array of keys and values which will be listed as options
* selected = The array key of the option that should be selected initially.
* If multiple=true, this can be an array of values, which
* would cause multiple values to be initially selected
* default: no option is listed as selected
*
* ----------------
*
* Examples:
*
* Assuming $a = array( [0] => 'zero',
* [1] => 'one',
* [2] => 'two',
* ['other'] => 'other' );
* $s = 1;
*
* {html_select name="varname" options=$a selected=$s}
*
* OUTPUT: <select name="varname">
* <option value="0">zero</option>
* <option value="1" selected>one</option>
* <option value="2">two</option>
* <option value="other">other</option>
* </select>
*
* $s = array(1,2);
*
* {html_select name="varname" options=$a selected=$s multiple="true" blank="true" extra="class=\"red\"" size="4"}
*
* OUTPUT: <select name="varname" multiple class="red" size="4">
* <option value="null"></option>
* <option value="0">zero</option>
* <option value="1" selected>one</option>
* <option value="2" selected>two</option>
* <option value="other">other</option>
* </select>
*
* You get the idea...
*/
function smarty_function_html_select($params){
    require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');

/* Default values. */
/* */
$name = "select_box";
/* <select size> of the <select> tag.
If not set, uses default dropdown.
Note that this is the number of rows the select will include. */
$size = null;
/* Whether to include a blank option (none) */
$blank = false;
$blank_name = '';
/* Unparsed attributes for the <select> tag.
An example might be in the template: extra ='class ="foo"'. */
$extra = null;
/* Whether the select allows multiple items to be selected. */
$multiple = false;
/* Array of options in the select menu
* <option value="$key">$options[$key]</option> */
$options = null;
/* Array key of the option to be initially selected
* If multiple=true, this can be an array */
$selected = null;
$extra_attrs = '';
foreach ($params as $_key => $_value) {
	switch ($_key) {
	case 'name':
	case 'size':
	case 'extra':
	case 'blank_name':
		$$_key = (string)$_value;
		break;
	case 'multiple':
	case 'blank':
		$$_key = (bool)$_value;
		break;
	case 'options':
	case 'selected':
		$$_key = $_value;
		break;
	default:
		if(!is_array($_value)) {
			$extra_attrs .= ' '.$_key.'="'.smarty_function_escape_special_chars($_value).'"';
		} else {
			trigger_error("select: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
		}
		break;
	}
}
$result = '<select name="'.$name.'"';
if ($multiple) {
	$result .= ' multiple';
}

if (isset($size)) {
	$result .= ' size="'.$size.'"';
}

if (isset($extra)) {
	$result .= ' '.$extra;
}

$result .= $extra_attrs . '>';

if ($blank) {
	$result .= '<option value="null"';
	if ($selected == null) {
		$result .= ' selected';
	}
	$result .= '>' . $blank_name . '</option>';
}
if(!empty($options)){
	foreach ($options AS $value => $text) {
		$result .= '<option value="'.$value.'"';
		if ( (is_array($selected) && array_search($value, $selected) !== FALSE) || (!is_array($selected) && $value == $selected) ) {
			$result .= ' selected';
		}
		$result .= '>'.$text.'</option>';
	}
}else{
	trigger_error("html_select: empty 'options'");
}
$result .= '</select>';

return $result;
}
/* vim: set expandtab: */
?>