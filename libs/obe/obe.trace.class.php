<?php

function _var_dump()
{
	call_user_func_array([
		'OBE_Trace',
		'dump'
	], func_get_args());
	// 	call_user_func_array(['OBE_Trace','preDump'],func_get_args());
}

class OBE_Trace{

	public static function init()
	{
	}

	/**
	 * Vypise do streamu callstack funkci az k umisteni zavolani teto fce
	 * @param String $userMessage
	 * @return void
	 */
	static function callPoint(
		$userMessage = '',
		$offset = 1,
		$limit = null)
	{
		OBE_Error::output($userMessage, E_USER_ERROR, OBE_Error::getFormatedStack($offset, $limit));
	}

	/**
	 * Vypise do out streamu predane argumenty
	 */
	static function preDump()
	{
		self::callPoint('OBE_Trace::preDump()');
		echo "<pre>\n";
		$items = func_get_args();
		call_user_func_array('var_dump', $items);
		echo "</pre>\n";
	}

	/**
	 * Zapíse predane argumenty do log streamu vypisu (souboru napr.)
	 * @return Void
	 */
	static function dump2File()
	{
		$out = '';
		foreach(func_get_args() as $item){
			$out . print_r($item, true);
		}
		OBE_Log::log($out);
	}

	static function dump()
	{
		self::callPoint('OBE_Trace::dump()', 2, 5);
		$vaargs = func_get_args();
		call_user_func_array('var_dump', $vaargs);
	}

	/**
	 */
	static function fullDump()
	{
		ini_set('xdebug.var_display_max_children', 99);
		ini_set('xdebug.var_display_max_data', 100);
		ini_set('xdebug.var_display_max_depth', 999);
		self::callPoint('OBE_Trace::fullDump()');
		$items = func_get_args();
		call_user_func_array('var_dump', $items);
	}

	/**
	 *
	 * @param array $array multi array
	 */
	static function table(
		$array)
	{
		$table = '<table><thead>';
		$keys = array_keys(reset($array));
		foreach($keys as $k){
			$table .= '<th>' . $k . '</th>';
		}
		$table .= '</thead><tbody>';
		foreach($array as $a){
			$table .= '<tr><td>' . implode('</td><td>', $a) . '</td></tr>';
		}
		$table .= '</tbody></table>';
		echo $table;
	}
}