<?php

class OBE_FieldValid{
	const NOEMPTY = 'no_empty';
	/* udani presneho vyssiho formatu */
	const DATE = 'date';
	const EMAIL = 'email';
	const LINK = 'link';
	const PASS = 'pass';
	const CAPTCHA = 'captcha';
	/* udani nizssiho formatu */
	const LEN = 'len';
	const NUM = 'num';
	const NUM_WSC = 'num_wsc';
	const ALNUM = 'alnum';
	const AL = 'al';
	const AL_WSC = 'al_wsc';
	const ALNUM_WSC = 'alnum_wsc';
	const MINLEN = 'minlen';
	const MAXLEN = 'maxlen';
	const BETWEEN = 'between';
	const MUSTTRUE = 'must_true';
	const SQLBOOL = 'sqlbool';
	const VARIABLEBOOL = 'vbool';
	const BOOLTONUM = 'boolToNum';
	const CHECKBOX = 'checkbox';
	const FILE = 'file';
	const FILE_TYPE = 'file_type';
	const MYSQL = 'mysql';
}

class OBE_FormClass{
	static private $domains = '';

	var $errorReport = [];
	var $data;

	function __construct(){
		self::$domains = 'net|com|gov|mil|org|edu|int|info';
		if(OBE_Core::$debug){
			self::$domains = 'net|com|gov|mil|org|edu|int|info|local';
		}
	}

	function Validate($form = [], $muttationExceptionKey = null){
		$data = null;
		$this->bError = false;
		if(!empty($form) && is_array($form)){
			foreach($form as $key => $params){
				$data[$key] = $this->ElementValidate($key, $params);
			}
			$this->data = $data;
			if(!empty($this->errorReport)){
				if($muttationExceptionKey){
					throw new OBE_Exception($muttationExceptionKey);
				}else{
					throw new OBE_Exception('Formulář nebyl správně vyplněn');
				}
			}
		}
		return $data;
	}

	function ElementValidate($key, $params){
		$value = null;
		if(!OBE_Http::issetPost($key) || isset($_FILES[$key])){
			if(!isset($_FILES[$key])){
				$value = null;
			}else{
				$value = $_FILES[$key];
			}
		}else{
			$value = OBE_Http::getPost($key);
		}

		if(!is_array($params)){
			$params = [$params];
		}

		if(in_array(OBE_FieldValid::EMAIL, $params) || isset($params[OBE_FieldValid::EMAIL])){
			$value = trim($value);
		}

		if(!((is_string($value) && mb_strlen($value) == 0) && !(in_array(OBE_FieldValid::NOEMPTY, $params) || isset($params[OBE_FieldValid::NOEMPTY])))){
			foreach($params as $val_key => $val_params){
				$message = '';
				$rules = [];
				if(is_numeric($val_key)){
					$fce = $val_params;
					$val_params = null;
				}else{
					$fce = $val_key;
					if(!is_array($val_params)){
						$message = $val_params;
					}else{
						if(isset($val_params['message'])){
							$message = $val_params['message'];
							unset($val_params['message']);
						}
						$rules = $val_params;
					}
				}
				if(!call_user_func_array([get_class($this), $fce], [&$value, $rules])){
					if(!empty($message)){
						$this->errorReport[$key][] = $message;
					}
					if($fce == OBE_FieldValid::NOEMPTY){
						return $value;
					}
				}
			}
		}
		return $value;
	}

	static function no_empty(&$val, $rules){
		if(mb_strlen($val) != 0){
			return true;
		}
		return false;
	}

	static function num(&$val, $rules){
		if(is_numeric($val)){
			if(isset($rules['min']) && $val < $rules['min']){
				return false;
			}
			if(isset($rules['max']) && $val > $rules['max']){
				return false;
			}
			return true;
		}
		return false;
	}

	static function num_wsc(&$val, $rules){
		if(isset($rules['spec'])){
			$spec = $rules['spec'];
		}else{
			$spec = ' \.-_';
		}
		return preg_match('~^[[:digit:]' . $spec . ']*$~', $val);
	}

	static function alnum(&$val, $rules){
		return preg_match('~^[[:alnum:]' . OBE_Strings::PRERG_CZ_CHARS . ']*$~', $val);
	}

	static function al(&$val, $rules){
		return preg_match('~^[[:alpha:]' . OBE_Strings::PRERG_CZ_CHARS . ']*$~', $val);
	}

	static function al_wsc(&$val, $rules){
		if(isset($rules['spec'])){
			$spec = $rules['spec'];
		}else{
			$spec = ' \.-_';
		}
		return preg_match('~^[[:alpha:]' . OBE_Strings::PRERG_CZ_CHARS . $spec . ']*$~', $val);
	}

	static function alnum_wsc(&$val, $rules){
		if(isset($rules['spec'])){
			$spec = $rules['spec'];
		}else{
			$spec = ' \.-_';
		}
		return preg_match('~^[[:alnum:]' . OBE_Strings::PRERG_CZ_CHARS . $spec . ']*$~', $val);
	}

	static function email(&$val, $rules){
		if($_SERVER['SERVER_ADDR'] != LOCAL_SERV_ADR){
			return preg_match("~^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|" . self::$domains . ")\$~", trim($val));
		}else{
			return true;
		}
	}

	static function pass(&$val, $rules){
		return true;
	}

	static function captcha(&$val, $rules){
		return captchaClass::validatekey($_POST[$rules['save_to']], $val);
	}

	static function len(&$val, $rules){
		if(isset($rules['len']) && mb_strlen($val) == $rules['len']){
			return true;
		}
		return false;
	}

	static function minlen(&$val, $rules){
		if(isset($rules['min']) && mb_strlen($val) < $rules['min']){
			return false;
		}
		return true;
	}

	static function maxlen(&$val, $rules){
		if(isset($rules['max']) && mb_strlen($val) > $rules['max']){
			return false;
		}
		return true;
	}

	static function between(&$val, $rules){
		return true;
	}

	static function vbool(&$val, $rules){
		$enablevals = [0, 1, '0', '1', 'true', 'false', 'yes', 'no'];
		if(isset($rules['only'])){
			if($val == $rules['only']){
				return true;
			}else{
				return false;
			}
		}elseif(in_array(strtolower($val), $enablevals) || empty($val)){
			return true;
		}
		return false;
	}

	static function boolToNum(&$val, $rules){
		$enablevals0 = [0, '0', 'false', 'no'];
		$enablevals1 = [1, '1', 'true', 'yes'];
		if(in_array(strtolower($val), $enablevals1)){
			$val = 1;
			return true;
		}elseif(in_array(strtolower($val), $enablevals0) || empty($val)){
			$val = 0;
			return true;
		}
		return false;
	}

	static function must_true(&$val, $rules){
		$enablevals = [1, '1', 'true', 'yes'];
		if(in_array(strtolower($val), $enablevals)){
			return true;
		}
		return false;
	}

	static function link(&$val, $rules){
		if(substr($val, 0, 7) != 'http://' && substr($val, 0, 8) != 'https://' && substr($val, 0, 7) != 'mailto:' && substr($val, 0, 2) != './'){
			$val = 'http://' . $val;
		}
		return true;
	}

	static function mysql(&$val, $rules = []){
		$val = OBE_App::$db->escape_string($val);
		return true;
	}

	static function file(&$val, $rules){
		if(empty($val)){
			return false;
		}else{
			return true;
		}
	}

	static function date(&$val, $rules){
		if(is_string($val)){
			return true;
		}
	}

	static function file_type(&$val, $rules){
		if(!empty($val['name'])){/* tohle nechapu */
			$types = [];
			$subval = &$val[0];
			if(isset($rules['types'])){
				$types = explode(';', $rules['types']);
			}
			if(empty($types)){
				throw new OBE_Exception('In Validator file_type cannot be $rules[\'types\'] an empty');
				return false;
			}
			if(isset($subval['name'])){
				$realFileName = $subval['name'];
				$ext = end(explode('.', $realFileName));
				if(!isset($rules['strict']) || $rules['strict'] == 'include'){
					if(in_array($ext, $types)){
						return true;
					}
				}elseif($rules['strict'] == 'exclude'){
					if(!in_array($ext, $types)){
						return true;
					}
				}
			}
			return false;
		}
		return true;
	}
}