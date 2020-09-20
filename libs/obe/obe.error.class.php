<?php

/**
 * trida pro formatovane vypisy vyjimek
 */

if(!defined('E_DEPRECATED')){
	define('E_DEPRECATED', NULL);
}

class OBE_Error{

	private static $debug = false;

	private static $silent = false;

	public static $numStrict = 0;

	public static $numErrors = 0;

	public static $numDeprecates = 0;

	public static $numExceptions = 0;

	public static $panel = null;

	static $codes = [
		E_ERROR => 'ERROR',
		E_WARNING => 'WARNING',
		E_PARSE => 'PARSE',
		E_NOTICE => 'NOTICE',
		E_STRICT => 'STRICT',
		E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR'
	];

	static function setUp($debug){
		self::$debug = $debug;
		set_error_handler([
			__CLASS__,
			'error_handler'
		], E_ALL);
		set_exception_handler([
			__CLASS__,
			'exception_handler'
		]);
		OBE_Trace::init(); // aby se natahla trida pres robot loader
		self::$panel = new OBE_ErrorDebugPanel();
	}

	/**
	 *
	 * @param \Exception $e
	 * @return void
	 */
	public static function exception_handler($e){
		self::$numExceptions++;

		OBE_Exception::logException($e);

		return true;
	}

	public static function error_handler($errno, $errstr = null, $errfile = null, $errline = null/*, $errcontext = null*/){
		if(strpos($errfile, '/libs/nette/caching/storages/FileStorage.php') > 0 && $errline == 271){
			return true;
		}

		if(is_array($errno)){
			$errstr = $errno['message'];
			$errfile = $errno['file'];
			$errline = $errno['line'];
			$errno = $errno['type'];
		}

		if($errno == E_DEPRECATED){
			self::$numDeprecates++;
			self::log(self::getFormatedError($errno, $errstr, $errfile, $errline), $errno);
			return true;
		}

		if($errno == E_STRICT){
			self::$numStrict++;
			self::log(self::getFormatedError($errno, $errstr, $errfile, $errline), $errno);
			return true;
		}

		self::$numErrors++;

		if($errno == E_USER_WARNING || $errno == E_USER_ERROR || $errno == E_USER_NOTICE){
			$error = self::getFormatedError($errno, $errstr, $errfile, $errline);
			self::output($error, $errno);
		}else{
			$error = array_merge([
				''
			], self::getFormatedError($errno, $errstr, $errfile, $errline), self::getFormatedStack(2));
			self::log($error, $errno);
		}

		return true;
	}

	static function log($error, $errno, $dump = NULL){
		OBE_Log::log($error);
		self::$panel->addErr($error);
		self::output($error, $errno, $dump);
	}

	static function output($error, $errno, $dump = NULL){
		if(self::$silent){
			self::$panel->addErr($error);
			return;
		}

		if(self::$debug || (error_reporting() & $errno) || self::check_user_error($errno)){
			if(OBE_Core::$cli){
				echo 'U | ' . self::compileLines($error, PHP_EOL) . PHP_EOL;
			}else{
				echo '<pre>U | ' . self::compileLines($error) . '</pre>';
			}
			if(!empty($dump)){
				echo '<pre>' . print_r($dump, true) . '</pre>';
			}
		}
	}

	private static function check_user_error($errno){
		if($errno == E_USER_WARNING || $errno == E_USER_ERROR || $errno == E_USER_NOTICE){
			return true;
		}
		return false;
	}

	static function getFormatedStack($offset = 0, $limit = null){
		return self::formatStack(debug_backtrace(), $offset, $limit);
	}

	/**
	 *
	 * @param Array $stack
	 * @param Integer $offset
	 */
	static function formatStack($stack, $offset = 0, $limit = null){
		$stack = self::checkStackForSmarty($stack);

		if($offset > 0){
			$stack = array_slice($stack, $offset, $limit);
		}

		$out = [];
		foreach($stack as $key => $item){
			if(!isset($item['class'])){
				$item['class'] = '';
				$item['type'] = '';
			}
			if(!isset($item['file'])){
				$item['file'] = '';
				$item['line'] = '';
			}
			$out[] = '	#' . $key . ' ' . $item['file'] . (($item['line']) ? ' line:' . $item['line'] : '') . ' ' . $item['class'] . $item['type'] . $item['function'];
		}

		return $out;
	}

	static function getFormatedError($errno, $errstr, $errfile, $errline){
		$error[] = ((isset(self::$codes[$errno])) ? self::$codes[$errno] : 'Error code ' . $errno) . ': ' . $errstr;
		$error[] = "\tFile : " . $errfile . "\tLine : " . $errline;
		return $error;
	}

	static function compileLines($lines = '', $separator = "\n"){
		if(is_array($lines)){
			return implode($separator, $lines) . $separator;
		}else{
			return $lines . $separator;
		}
	}

	static function gotErrors(){
		return (((self::$numDeprecates + self::$numStrict + self::$numErrors + self::$numExceptions) > 0) ? true : false);
	}

	static function setSilent($b){
		self::$silent = $b;
	}

	static function getSilent(){
		return self::$silent;
	}

	static function checkStackForSmarty($stack){

		foreach($stack as $s){
			if(isset($s['file'])){
				if(strpos($s['file'], '\libs\smarty')){
					$stack = [
						reset($stack)
					];
					break;
				}
			}
		}
		return $stack;
	}
}

class OBE_ErrorDebugPanel extends OBE_DebugPanel{

	private $errs = [];

	public function __construct($id = ''){
		parent::__construct('script-errs');
		$this->head = 'Sript errors';
	}

	public function addErr($err){
		$this->errs[] = $err;
	}

	public function getContent(){
		$cnt = '<ul class="table"><li class="head"><span></span><span>message</span></li>';

		foreach($this->errs as $e){
			$cnt .= '<li><span></span><span>' . implode($e, '<br/>') . '</span></li>';
		}

		$cnt .= '<li><span>Errors</span><span class="num">' . OBE_Error::$numErrors . '</span></li>';
		$cnt .= '<li><span>Exceptions</span><span class="num">' . OBE_Error::$numExceptions . '</span></li>';
		$cnt .= '<li><span>Stricts</span><span class="num">' . OBE_Error::$numStrict . '</span></li>';
		$cnt .= '<li><span>Deprecates</span><span class="num">' . OBE_Error::$numDeprecates . '</span></li></ul>';

		return $cnt;
	}

	public function getLabel(){
		return 'script err`s: ' . (OBE_Error::$numErrors + OBE_Error::$numDeprecates + OBE_Error::$numExceptions + OBE_Error::$numStrict);
	}
}