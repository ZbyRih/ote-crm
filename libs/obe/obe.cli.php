<?php


class OBE_Cli implements \ArrayAccess{

	static $args = [];

	private static $isCli = false;

	private static $breakOnError = false;

	private static $oldHandler = null;

	private static $timer = null;

	public static function init(){
		global $argv;
		if(isset($argv)){
			self::$args = array_slice($argv, 1);
		}
		self::$isCli = php_sapi_name() == 'cli';

	}

	public static function setBreakOnError(){
		self::$breakOnError = true;
		self::$oldHandler = set_error_handler(
			function (){

				if(self::$oldHandler){
					call_user_func_array(self::$oldHandler, func_get_args());
					die('Die break on error');
				}
			});
	}

	/**
	 *
	 * @param string $param - bez '-'
	 */
	public static function hasSignal($param){
		if(false !== ($key = array_search('-' . $param, self::$args))){
			return $key + 1;
		}
		return false;
	}

	public static function getParam($param){
		if(($i = self::hasSignal($param)) !== false){
			if(isset(self::$args[$i + 1])){
				return self::$args[$i + 1];
			}
		}
		return null;
	}

	public static function isCli(){
		return self::$isCli;
	}

	public static function writeBr($string = ''){
		self::writeLn($string, self::getEnd());
	}

	public static function writeLn($string){
		if(is_array($string)){
			echo implode(self::getEnd(), $string) . self::getEnd();
		}else{
			echo $string . self::getEnd();
		}
	}

	public static function getEnd(){
		return self::isCli() ? PHP_EOL : "\r\n"; // '<br />';
	}

	/* Methods */
	public function offsetExists($offset){
		return isset(self::$args[$offset - 1]);
	}

	public function offsetGet($offset){
		return self::$args[$offset - 1];
	}

	public function offsetSet($offset, $value){
		self::$args[$offset] = $value;
	}

	public function offsetUnset($offset){
		unset(self::$args[$offset - 1]);
	}

	public static function writeBegin(){
		self::writeBr('>> - begin');
		self::writeBr('');
		self::$timer = (new OBE_Timer())->init();
	}

	public static function writeEnd(){
		self::writeBr('');
		self::writeBr('>> - end');
		if(self::$timer){
			self::writeBr('>> - run time: ' . number_format(self::$timer->elapsed() * 1000, 2, '.', '') . ' ms');
		}
	}

	public static function writeArr($k, $a){
		OBE_Cli::writeLn(
			(($k) ? '	- ' . $k : '') . ' -> ' . str_replace([
				'&',
				'='
			], [
				', ',
				' => '
			], urldecode(http_build_query($a))));
	}

	public static function dump($arg, $title = null){
		if($title){
			self::writeBr($title);
		}
		var_dump($arg);
	}
}