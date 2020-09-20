<?php

class OBE_Json extends OBE_Array{
	const JSON_ERROR_NONE = 0;
	const JSON_ERROR_DEPTH = 1;
	const JSON_ERROR_STATE_MISMATCH = 2;
	const JSON_ERROR_CTRL_CHAR = 3;
	const JSON_ERROR_SYNTAX = 4;
	const JSON_ERROR_UTF8 = 5;

	protected static $_messages = [
		self::JSON_ERROR_NONE 				=> 'No error has occurred',
		self::JSON_ERROR_DEPTH 				=> 'The maximum stack depth has been exceeded',
		self::JSON_ERROR_STATE_MISMATCH 	=> 'Invalid or malformed JSON',
		self::JSON_ERROR_CTRL_CHAR 			=> 'Control character error, possibly incorrectly encoded',
		self::JSON_ERROR_SYNTAX 			=> 'Syntax error',
		self::JSON_ERROR_UTF8 				=> 'Malformed UTF-8 characters, possibly incorrectly encoded'
	];

	public function decode($file, $toArray = true){

		$this->_data = [];

		if($content = file_get_contents($file)){
			if($data = json_decode($content, $toArray)){
				$this->setData($data);
			}else{
				echo "<pre>\r\n";
				if(version_compare(PHP_VERSION, '5.3.0', '>')){
					echo self::$_messages[json_last_error()]. "\r\n";
				}else{
					echo 'Chyba pri prasovani ' . $file . "\r\n";
				}
				echo 'poustim linter' . "\r\n";
				echo "</pre>";
				OBE_JsonLinter::lint($file);
			}
		}
	}
};