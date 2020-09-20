<?php

OBE_Http::init();

class OBE_Http{

	const POST = 'post';

	const GET = 'get';

	public static $bMagicQuotes = false;

	public static $bHttps = false;

	public static $supportRCode = [
		'300',
		'301',
		'302',
		'303',
		'304',
		'307'
	];

	public static function init(){
		self::$bMagicQuotes = get_magic_quotes_gpc();
		if(isset($_SERVER['HTTPS'])){
			self::$bHttps = true;
		}
	}

	/**
	 *
	 * @param $key, [$key], ...
	 */
	public static function issetGet(/* $key */){
		$keys = func_get_args();
		if(array_keys_exist($keys, $_GET)){
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param $key, [$key], ...
	 */
	public static function issetPost(/* $key */){
		$keys = func_get_args();
		if(array_keys_exist($keys, $_POST)){
			return true;
		}
		return false;
	}

	public static function issetByType($key, $type){
		if($type == self::GET){
			return self::issetGet($key);
		}elseif($type == self::POST){
			return self::issetPost($key);
		}
		return NULL;
	}

	public static function getGet($key){
		if(isset($_GET[$key])){
			return self::sanitize($_GET[$key]);
		}
		return NULL;
	}

	public static function getGetDef($key, $def){
		if(isset($_GET[$key])){
			return self::sanitize($_GET[$key]);
		}
		return $def;
	}

	public static function getPost($key){
		if(isset($_POST[$key])){
			return self::sanitize($_POST[$key]);
		}
		return NULL;
	}

	public static function getByType($key, $type){
		if($type == self::GET){
			return self::getGet($key);
		}elseif($type == self::POST){
			return self::getPost($key);
		}
		return NULL;
	}

	/**
	 * vraci jen pozitivni hodnoty
	 * @param String $key
	 */
	public static function getNumGet($key){
		if(isset($_GET[$key])){
			$sant = self::sanitize($_GET[$key]);
			if(is_numeric($sant)){
				return $sant;
			}
		}
		return NULL;
	}

	/**
	 * vraci jen pozitivni hodnoty
	 * @param String $key
	 */
	public static function getNumPost($key){
		if(isset($_POST[$key]) && is_numeric($_POST[$key])){
			$sant = self::sanitize($_POST[$key]);
			if(is_numeric($sant)){
				return $sant;
			}
		}
		return NULL;
	}

	public static function getRequest($key){
		$val = NULL;
		if(($val = self::getGet($key)) !== NULL){
			return $val;
		}elseif(($val = self::getPost($key)) !== NULL){
			return $val;
		}
		return $val;
	}

	/**
	 * vraci jen positivni hodnoty
	 * @param String $key
	 */
	public static function getNumRequest($key){
		$val = NULL;
		if(($val = self::getNumGet($key)) !== NULL){
			return $val;
		}elseif(($val = self::getNumPost($key)) !== NULL){
			return $val;
		}
		return $val;
	}

	public static function isGetIs($key, $value){
		if(self::issetGet($key) && OBE_Http::getGet($key) == $value){
			return $value;
		}
		return NULL;
	}

	public static function isGetIsIs($key, $value){
		if(self::issetGet($key) && OBE_Http::getGet($key) == $value){
			return true;
		}
		return false;
	}

	public static function isPostIs($key, $value){
		if(self::issetPost($key) && OBE_Http::getPost($key) == $value){
			return $value;
		}
		return NULL;
	}

	public static function isGetNotIs($key, $value){
		if(self::issetGet($key) && OBE_Http::getGet($key) != $value){
			return true;
		}
		return false;
	}

	public static function isPostNotIs($key, $value){
		if(self::issetPost($key) && OBE_Http::getPost($key) != $value){
			return true;
		}
		return false;
	}

	public static function emptyGet($key){
		if(self::issetGet($key) && empty($_GET[$key])){
			return true;
		}
		return false;
	}

	public static function notEmptyGet($key){
		if(self::issetGet($key) && !empty($_GET[$key])){
			return true;
		}
		return false;
	}

	public static function emptyPost($key){
		if(self::issetPost($key) && empty($_POST[$key])){
			return true;
		}
		return false;
	}

	public static function notEmptyPost($key){
		if(self::issetPost($key) && !empty($_POST[$key])){
			return true;
		}
		return false;
	}

	public static function setPost($key, $value){
		$_POST[$key] = $value;
	}

	public static function setGet($key, $value = NULL){
		if(is_array($key)){
			foreach($key as $k => $v){
				$_GET[$k] = $v;
			}
		}else{
			$_GET[$key] = $value;
		}
	}

	public static function dropRequest($key){
		self::dropGet($key);
		self::dropPost($key);
	}

	public static function dropGet($key){
		unset($_GET[$key]);
	}

	public static function dropPost($key){
		unset($_POST[$key]);
	}

	/**
	 *
	 * @param $key, [$key], ...
	 */
	public static function issetServer(){
		$keys = func_get_args();
		if(array_keys_exist($keys, $_SERVER)){
			return true;
		}
		return false;
	}

	public static function getServer($key, $dbescape = false){
		if(isset($_SERVER[$key])){
			if($dbescape){
				return OBE_DB::escape_string($_SERVER[$key]);
			}else{
				return $_SERVER[$key];
			}
		}
		return NULL;
	}

	public static function sanitize($value){
		if(self::$bMagicQuotes){
			if(is_string($value)){
				return stripslashes($value);
			}elseif(is_array($value)){
				foreach($value as &$item){
					if(is_string($item)){
						$item = stripslashes($item);
					}
				}
			}
			return $value;
		}
		return $value;
	}

	public static function correctProtocol($url){
		if(self::$bHttps){
			return str_replace('http://', 'https://', $url);
		}
		return $url;
	}

	public static function headerRedirect($absoluteUrl, $code = '303'){
		if(!in_array($code, self::$supportRCode)){
			Die("Nepodporovaný typ přesměrování.");
		}
		header('Location: ' . self::correctProtocol($absoluteUrl), true, $code);
	}

	public static function headerError($code = '404'){
		switch($code){
			case '404':
				self::respondCode('404 Not Found');
				break;
			default:
				Die('Nepodporovaný typ chybové hlavičky.');
				break;
		}
	}

	public static function respond($respond, $message = NULL){
		self::headerContent('text/html; charset=utf-8');
		self::respondCode($respond);
		echo $message;
		exit();
	}

	public static function respondCode($response){
		$codes = explode(' ', $response);
		self::header('HTTP/1.1 ' . $response, true, $codes[0]);
	}

	public static function headForForceDownload($fileName, $fileSize, $contentType = 'application/force-download'){
		ob_start();
		header('Expires: 0');
		header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Description: Přenos souboru');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $fileSize . '');
		header('Content-Type: ' . $contentType . '');
		header('Status-Code: 200');
		header("HTTP/1.0 200 OK");
	}

	public static function headForFileWMimeType($fileName, $fileSize, $contentType = 'text/xml; charset=utf-8'){
		ob_start();
		header('Expires: 0');
		header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Description: File Transfer');
		if(!empty($fileName)){
			header('Content-Disposition: attachment; filename="' . $fileName . '"');
		}
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $fileSize . '');
		header('Content-Type: ' . $contentType . '');
		header('Connection: close');
		header('Status-Code: 200');
		header("HTTP/1.0 200 OK");
	}

	public static function headerContent($type = 'text/html'){
		self::header('Content-type: ' . $type);
	}

	public static function flushAndClose(){
		if(ob_get_level() > 0){
			ob_end_flush();
		}
		flush();
		session_write_close();
	}

	private static function replace_unicode_escape_sequence($match){
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}

	public static function replace_js_escape($str){
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
	}

	/**
	 * osetri retezec fci urlencode (oprava mezer a atd na html entity) a pote nektere z nich zase prevede na znaky
	 * @param String $link
	 */
	public static function linkSanitize($link){
		return str_replace([
			'%26',
			'%3D',
			'%3F',
			'%3B'
		], [
			'&',
			'=',
			'?',
			';'
		], urlencode($link));
	}

	public static function header($str){
		if(!OBE_Core::$cli){
			header($str);
		}
	}
}