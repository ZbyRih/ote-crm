<?php

class OBE_Core{

	public static $cli = false;

	public static $debug = null;

	public static $safeMode = false;

	public static $phpMajor = null;

	public static $phpMinor = null;

	public static $memoryLimit = 0;

	private static $env;

	private static $destroyCallBacks = null;

	/**
	 * @var string : null - nic, ajax - ajax;
	 */
	private static $requestMode = null;

	public static $DebugBar;

	public static $protocol = 'http:';

	/**
	 * Inicializuje system
	 * @param boolean $debug - zmena error_reportingu true -> E_ALL/false -> E_ALL & ~E_NOTICE
	 */
	static function init(
		$debug = false,
		$subVersion = '')
	{
		self::$cli = (php_sapi_name() == "cli");

		date_default_timezone_set('Europe/Prague');

		self::detectProtocol();

		if($phpVersion = phpversion()){
			$chunks = explode('.', $phpVersion);
			self::$phpMajor = $chunks[0];
			self::$phpMinor = $chunks[1];
		}

		self::setDebug($debug);

		if(self::$phpMinor <= 2 && self::$phpMajor <= 5){
			self::$bSafeMode = ini_get('safe_mode');
		}

		mb_internal_encoding('UTF-8');

		self::$memoryLimit = OBE_Math::getFormatIniLimits(ini_get('memory_limit'));

		register_shutdown_function([
			__CLASS__,
			'destroy'
		]);

		return self::loadEnv($subVersion);
	}

	static function detectProtocol()
	{
		if(array_key_exists('REQUEST_SCHEME', $_SERVER) || array_key_exists('HTTPS', $_SERVER)){
			self::$protocol = (($_SERVER['REQUEST_SCHEME'] == 'https' || (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on')) ? 'https:' : 'http:');
		}
	}

	/**
	 * Nacte nastaveni prostredi, db atd.
	 * @param string $subVersion - zatim nepouzito
	 * @return object $adb - object pro pristup k databazi
	 */
	static function loadEnv(
		$subVersion = NULL)
	{
		if(isset($_SERVER['REMOTE_ADDR'])){
			$name = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		}else{
			$name = 'localhost';
		}
		$host_name = self::createHostName($subVersion);
		$host_local_name = $host_name . '%' . $name;

		if(isset(EnviromentConfig::$alias[$host_name])){
			$host_name = EnviromentConfig::$alias[$host_name];
		}

		if(isset(EnviromentConfig::$config[$host_local_name])){
			self::$env = EnviromentConfig::$config[$host_local_name];
			OBE_Log::log('OBE_Core::loadEnv ' . $host_local_name);
		}else if(isset(EnviromentConfig::$config[$host_name])){
			self::$env = EnviromentConfig::$config[$host_name];
			OBE_Log::log('OBE_Core::loadEnv ' . $host_name);
		}else{
			throw new OBE_Exception('`' . $host_local_name . '` nebo `' . $host_name . '` nejsou definovany v EnviromentConfig.');
		}

		$url = self::$env['url'];
		if(substr($url, 0, 5) != self::$protocol){
			$url = self::$protocol . substr($url, 5);
		}
		self::$env['url'] = $url;

		if(isset(self::$env['db'])){
			if(OBE_App::$db = self::initDB(self::$env['db'])){
				OBE_Log::initDbLog();
				return true;
			}
			return false;
		}

		return true;
	}

	static function setDebug(
		$debug)
	{
		if(self::$debug != $debug){
			ini_set('display_errors', (int) $debug);
			if($debug){
				if(self::$phpMinor <= 2 && self::$phpMajor <= 5){
					error_reporting(E_ALL);
				}else{
					error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED));
				}
			}else{
				error_reporting(E_ALL & ~(E_STRICT | E_DEPRECATED));
			}
			self::$debug = $debug;
		}
	}

	static function showErrors()
	{
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
	}

	/**
	 * Pripojeni na databazi
	 */
	static function initDB(
		$cfg)
	{
		return (new OBE_IDB())->connect($cfg);
	}

	static function destroy(
		OBE_Exception $e = null)
	{
		if(self::$destroyCallBacks){
			foreach(array_reverse(self::$destroyCallBacks) as $fce){
				call_user_func($fce);
			}
		}

		OBE_Log::finishLog($e);

		OBE_App::$db = NULL;
	}

	static function registerShutDown(
		$fce)
	{
		self::$destroyCallBacks[] = $fce;
	}

	static function createHostName(
		$subVersion = NULL)
	{
		if(isset($_SERVER['HTTP_HOST'])){
			$host_name = $_SERVER['HTTP_HOST'];
			if(substr($host_name, 0, 4) == 'www.'){
				$host_name = substr($host_name, 4);
			}
			$host_name = str_replace([
				'.',
				'-'
			], '', $host_name);
		}else{
			$host_name = 'localhost';
		}
		if($subVersion != NULL){
			$host_name = '_' . $subVersion;
		}
		return $host_name;
	}

	static function isAvaibleMemory(
		$needMem)
	{
		$freeMem = self::$memoryLimit - memory_get_usage(true);
		if($needMem > $freeMem){
			return false;
		}
		return true;
	}

	static function getActualUrl()
	{
		$url = rtrim(self::getConfEnvVar('url'), '/');
		if($_SERVER['SERVER_NAME'] == 'localhost'){
			$last = -1;
			for($i = 0; $i < 3; $i++){
				$last = strpos($_SERVER['REQUEST_URI'], '/', $last + 1);
			}
			return $url . substr($_SERVER['REQUEST_URI'], $last);
		}else{
			return $url . $_SERVER['REQUEST_URI'];
		}
	}

	public static function isEnvConf(
		$keyName)
	{
		return isset(self::$env[$keyName]);
	}

	/**
	 * @param String $keyName
	 */
	public static function getConfEnvVar(
		$keyName,
		$default = NULL)
	{
		if(isset(self::$env[$keyName])){
			return self::$env[$keyName];
		}else{
			OBE_Log::logle('NOTICE: OBE_Core::getConfEnvVar key "' . $keyName . '" is not exist');
		}
		return $default;
	}

	public static function getConfEnvVarDef(
		$keyName,
		$default = NULL)
	{
		if(isset(self::$env[$keyName])){
			return self::$env[$keyName];
		}
		return $default;
	}

	public static function getGlobalEnvVar(
		$keyName,
		$default = NULL)
	{
		if(isset(EnviromentConfig::$global[$keyName])){
			return EnviromentConfig::$global[$keyName];
		}else{
			OBE_Log::logle('NOTICE: OBE_Core::getGlobalEnvVar key "' . $keyName . '" is not exist');
		}
		return $default;
	}

	public static function isEnvGlobalConf(
		$keyName)
	{
		return isset(EnviromentConfig::$global[$keyName]);
	}

	public static function fileExists(
		$file)
	{
		if(!file_exists($file)){
			throw new OBE_Exception('Adresář\soubor `' . $file . '` neexistuje');
		}
		return $file;
	}

	public static function getOutputMode()
	{
		return self::$requestMode;
	}

	public static function setOutputMode(
		$mode)
	{
		self::$requestMode = $mode;
	}

	/**
	 * @param string $directory - název adresáře
	 * @return \Nette\Caching\Cache
	 */
	public static function getCache(
		$directory)
	{
		return new \Nette\Caching\Cache(new \Nette\Caching\Storages\FileStorage(APP_DIR_OLD . '/temp'), $directory);
	}
}