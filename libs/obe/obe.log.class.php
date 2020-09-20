<?php

class OBE_Log{

	static $init = false;

	public static $db_time = '';

	static $logStats = [];

	static $errorLogEnabled = true;

	/**
	 *
	 * @var OBE_Timer
	 */
	static $timer = null;

	/**
	 *
	 * @var OBE_FileLog
	 */
	static $fileLog = null;

	/**
	 *
	 * @var OBE_DbLog
	 */
	static $dbLog = null;

	private static $panel = null;

	const GET_LOGMARK_KEY = 'logMark';

	/**
	 *
	 * @return void
	 */
	public static function init(){
		self::$init = true;
		self::$timer = (new OBE_Timer())->init();
		self::$timer->timer();
		self::$db_time = date('Y-m-d H:i:s');

		$logFile = null;
		if(class_exists('OBE_AppCore') && $logFile = OBE_AppCore::getAppConf('logFile')){
			$mark = '';
			if(OBE_Http::getGet(self::GET_LOGMARK_KEY)){
				$mark = date('Y-m-d--H-i-s');
			}
			$logFile = str_replace('{m}', $mark, APP_DIR_OLD . '/' . $logFile);
		}

		self::$fileLog = new OBE_FileLog($logFile);

		self::initHead();

		self::$panel = OBE_Core::$DebugBar->addPanel(new OBE_AppLogDebugPanel());

		if(!function_exists('__dump')){

			function __dump(){
				call_user_func_array([
					'OBE_Log',
					'varDump'
				], func_get_args());
			}
		}
		if(!function_exists('dd')){

			function dd(){
				$args = func_get_args();
				call_user_func_array([
					OBE_Core::$DebugBar->getPanel('dumps'),
					'addDump'
				], $args);
				return reset($args);
			}
		}
	}

	static function initHead(){
		self::log('LOG START AT - ' . date('H:i:s d.m.Y', self::$timer->start));
		self::log(
			'Get: ' . 'http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));
		self::logl2('Referer        : ' . self::addHeader('HTTP_REFERER', 'NONE!'));
		self::logl2('Accept         : ' . self::addHeader('HTTP_ACCEPT', 'NONE!'));
		self::logl2('Accept-charset : ' . self::addHeader('HTTP_ACCEPT_CHARSET', 'NONE!'));
		self::logl2('Accept-encoding: ' . self::addHeader('HTTP_ACCEPT_ENCODING', 'NONE!'));
		self::logl2('Accept-lang    : ' . self::addHeader('HTTP_ACCEPT_LANGUAGE', 'NONE!'));
		self::logl2('Connection     : ' . self::addHeader('HTTP_CONNECTION', 'NONE!'));
		self::logl2('User-agent     : ' . self::addHeader('HTTP_USER_AGENT', 'NONE!'));
	}

	static function addHeader($header, $else = null){
// 		if(isset($_SERVER[$header])){
// 			return $_SERVER[$header];
// 		}else if($else){
// 			return $else;
// 		}
		return '';
	}

	static function initDbLog(){
		if(OBE_App::$db !== null){
			// 			self::$dbLog = new OBE_DbLog();
		}
	}

	/**
	 *
	 * @return void
	 */
	static function finishLog(OBE_Exception $e = null){
		if(!self::$init){
			return;
		}

		if($e){
			$e->log();
		}

		self::$timer->finish();

		self::$logStats[] = 'Run stats';
		self::$logStats[] = ' -- Sql -- ';
		self::$logStats[] = '  queries      : ' . ((OBE_App::$db) ? OBE_App::$db->stat() : 'null');
		self::$logStats[] = '  errors       : ' . ((OBE_App::$db) ? OBE_App::$db->errors->count() : null);
		self::$logStats[] = ' -- php errors -- ';
		self::$logStats[] = '  errors       : ' . OBE_Error::$numErrors;
		self::$logStats[] = '  exceptions   : ' . OBE_Error::$numExceptions;
		self::$logStats[] = '  strict       : ' . OBE_Error::$numStrict;
		self::$logStats[] = '  deprecates   : ' . OBE_Error::$numDeprecates;
		self::$logStats[] = '-----------------';
		self::$logStats[] = 'Log - run time  : ' . number_format(self::$timer->elapsed(), 4, '.', '');
		self::$logStats[] = 'Memory peak (B) : ' . memory_get_peak_usage(true);

		self::log(self::$logStats);

		self::$fileLog->flush();
		self::$fileLog->close();

		if(OBE_Error::gotErrors() || OBE_App::$db->errors->count() > 0){

			self::$fileLog->clean();
			self::$fileLog->copy();

			if(($feedBackMail = OBE_AppCore::getDBVar('front', 'ERR_REPORT_MAIL', false)) && OBE_AppCore::getAppConfDef('mail-error', true)){
				$mailObj = new OBE_Mail($feedBackMail, OBE_AppCore::getDBVar('front', 'POPT_EMAIL_TO'), 'Err form ' . OBE_Core::getConfEnvVar('url'));
				$mailObj->AddTextToBody(self::getFileLog());
				$mailObj->Send();
			}
		}

		if(OBE_Core::getOutputMode() != 'ajax' && !OBE_Core::$cli){
			OBE_Core::$DebugBar->create(OBE_Core::$debug);
		}
	}

	static function logQuery($sql, $result, $elapsed = null, $error = null){
		if(!self::$init){
			return;
		}

		if(self::$dbLog && self::$dbLog->bLogingProcess){
			return;
		}

		$logText = self::replaceWhiteChars($sql);
		$stack = null;

		if($result === false){
			$stack = OBE_Error::getFormatedStack(2);

			self::logToDB($logText, $stack, $error, $result, $elapsed);
		}

		$lines[] = $logText . ';';
		$lines[] = "\t" . $elapsed . 's, ' . 'result: ' . ((is_object($result)) ? 'true' : (int) $result);

		if($error && self::$errorLogEnabled){

			$lines[0] = "\n" . $lines[0];
			$lines[] = '-------------------------------------------------------------------------------';
			$lines[] = 'ERROR : ' . $error;
			$lines[] = "-------------------------------------------------------------------------------";
		}

		self::writeToFileLog($lines);

		if(!empty($stack)){
			self::writeToFileLog($stack);
		}

		if(OBE_Core::$debug && $error){
			if(OBE_Cli::isCli()){
				echo $error . "\r\n";
				echo $sql . "\r\n";
				echo implode("\r\n", $stack) . "\r\n";
			}else{
				echo '<div style="width; 100px; font-family: serif;">' . $error . '</div>';
				echo '<div style="width; 100px; font-family: serif;">' . SqlFormatter::format($sql) . '</div>';
				if(!empty($stack)){
					echo '<pre>' . implode("\n", $stack) . '</pre>';
				}
			}
		}
	}

	static function logTimer($str = ''){
		// 		if(!self::$init){
		// 			return;
		// 		}

// 		self::writeToFileLog('log timer: ' . self::$timer->timer() . 's in: ' . $str);
	}

	/**
	 * zapis textu do logovaciho souboru
	 * @param String $str
	 */
	static function logl1($str){
		// 		self::writeToFileLog('>>>>>>>> -- ' . $str);
	}

	/**
	 * zapis textu do logovaciho souboru
	 * @param String $str
	 */
	static function logl2($str){
		// 		self::writeToFileLog('     -- ' . $str);
	}

	/**
	 * zapis textu do logovaciho souboru
	 * @param String $str
	 */
	static function logl3($str){
		// 		self::writeToFileLog('         -- ' . $str);
	}

	/**
	 * zapis textu do logovaciho souboru
	 * @param String $str
	 */
	static function loglw($str){
		// 		self::writeToFileLog('-- > WARNING < ' . $str);
		// 		self::writeToPanel('warning', $str);
	}

	/**
	 * zapis textu do logovaciho souboru
	 * @param String $str
	 */
	static function logle($str){
		// 		self::writeToFileLog('-- >> CHYBA << ' . $str);
		// 		self::writeToPanel('error', $str);
	}

	/**
	 * zapis textu do logovaciho souboru
	 * @param String $str
	 */
	static function log($str){
		// 		self::writeToFileLog($str);
		// 		if(self::$timer){
		// 			self::writeToFileLog('(' . self::$timer->timer() . 's)');
		// 		}
	}

	/**
	 * zapis do logovaciho souboru, s pridanim pridanim informace o miste volani
	 * @param String $string
	 */
	static function logCT($string){
		// 		$stack = OBE_Error::getFormatedStack(2);
		// 		array_unshift($stack, $string);
		// 		self::log($stack);
	}

	/**
	 * var_dump zápis do logovacího souboru
	 * @param ...
	 */
	static function varDump(){
		return;
		// 		if(self::$fileLog !== null){
		// 			$args = func_get_args();
		// 			self::writeToFileLog('--- var dump - s ---');
		// 			self::writeToFileLog(call_user_func_array('print_r', [
		// 				$args,
		// 				true
		// 			]));
		// 			self::writeToFileLog('--- var dump - e ---');
		// 		}
	}

	static private function writeToFileLog($string){
		return;
		if(self::$fileLog){
			self::$fileLog->write($string);
		}else{
			$string = (is_array($string) ? (implode(PHP_EOL, $string) . PHP_EOL) : ($string . PHP_EOL));

			echo $string . PHP_EOL;
		}
	}

	static private function writeToPanel($class, $str){
		// 		self::$panel->add($str, $class);
	}

	static function logToDB($message, $stack, $error = null, $result = null, $time = null){
		// 		if(self::$dbLog !== null){
		// 			self::$dbLog->write($message, $stack, $error, $result, $time);
		// 		}
	}

	static function isDBLogging(){
// 		if(self::$dbLog !== null){
// 			return self::$dbLog->bLogingProcess;
// 		}
		return false;
	}

	static function disableDBLog(){
		self::$errorLogEnabled = false;
	// 		if(self::$dbLog !== null){
	// 			self::$dbLog->disable();
	// 		}
	}

	static function enableDBLog(){
		self::$errorLogEnabled = true;
	// 		if(self::$dbLog !== null){
	// 			self::$dbLog->enable();
	// 		}
	}

	static function getFileLog(){
// 		if(self::$fileLog){
// 			return self::$fileLog->getContent();
// 		}
		return null;
	}

	static function replaceWhiteChars($txt){
		return preg_replace("/ |\t{2,}|\r\n|$/", ' ', $txt);
	}
}

class OBE_AppLogDebugPanel extends OBE_DebugPanel{

	private $logs = [];

	public function __construct($id = ''){
		parent::__construct('log-table');
		$this->head = 'App Log';
		$this->label = 'App Log';
	}

	public function add($str, $class){
		$this->logs[$str] = $class;
	}

	public function getContent(){
		return '';
		$cnt = '';
		foreach($this->logs as $s => $c){
			$cnt .= '<li class="' . $c . '"><span>' . $c . '</span><span>' . $s . '</span></li>';
		}

		return '
			<ul class="table"><li class="head"><span>typ</span><span>message</span></li>
			' . $cnt . '
			</ul>
		';
	}
}