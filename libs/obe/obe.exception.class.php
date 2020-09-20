<?php

class OBE_Exception extends Exception{

	var $subMsg = NULL;

	var $varDump = NULL;

	var $name = 'Obecná';

	function log(){
		self::writeLog($this);
	}

	static function logException($eObj){
		if(method_exists($eObj, 'writeLog')){
			call_user_func([
				'OBE_Exception',
				'writeLog'
			], $eObj, $eObj);
		}else{
			self::writeLog($eObj);
		}
	}

	/**
	 *
	 * @param Exception $eObj
	 */
	static function writeLog($eObj){

		$varDump = NULL;

		if(isset($eObj->varDump) && !empty($eObj->varDump)){
			$varDump = $eObj->varDump;
		}

		$message = self::getFormatMessage($eObj);
		$stack = self::getFormatTrace($eObj);
		$stack = OBE_Error::checkStackForSmarty($stack);

		OBE_Error::$panel->addErr(array_merge($message, $stack));

		OBE_Log::log(array_merge($message, $stack));

		if(!empty($varDump)){
			OBE_Log::varDump($varDump);
		}

		OBE_Log::logToDB($message, $stack, $eObj->getMessage() . ' code:' . $eObj->getCode() . ' in: ' . $eObj->getFile() . '  on line : ' . $eObj->getLine());

		OBE_Error::output(array_merge($message, $stack), E_USER_ERROR, $varDump);
	}

	/**
	 *
	 * @param Exception $eObj
	 */
	public static function getFormatMessage($eObj){
		$messages[] = 'Exception (' . get_class($eObj) . ')  : ' . $eObj->getMessage();
		if(isset($eObj->subMsg) && !empty($eObj->subMsg)){
			$messages[] = '	Sub message: ' . $eObj->subMsg;
		}
		$messages[] = 'CODE       : ' . $eObj->getCode();
		$messages[] = 'IN         : ' . $eObj->getFile() . ' >> Called on line : ' . $eObj->getLine();

		return $messages;
	}

	/**
	 *
	 * @param Exception $eObj
	 * @return Array
	 */
	public static function getFormatTrace($eObj, $offset = 0){
		return OBE_Error::formatStack($eObj->getTrace(), $offset);
	}
}