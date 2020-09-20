<?php

class OBE_AppCore{

	public static $ieVersion = null;

	/**
	 * configuracni nastaveni layoutu
	 * obsah promenne $appConfig z AppConfiguration class
	 * @static Array
	 */
	static $conf = [];

	/**
	 * Promenne nacitane ze databaze, sadu promenych lde najit pod klicem pod jakym sou v db tj 'front' atd.
	 * @static Array
	 */
	static $dbVars = [];

	/**
	 * Dynamicky nacitane promenne, tj hodnoty nacitane z post, get vstupu
	 * @static Array
	 */
	static $dynamicVars = [];

	/**
	 * Inicializace enginu
	 * @param boolean $debug - zmena error_reportingu true -> E_ALL/false -> E_ALL & ~E_NOTICE
	 */
	static function init(
		$debug = null,
		$subVersion = '')
	{
		self::$ieVersion = self::getIEVersion();

		$class = new ReflectionClass('AppConf' . APP_CONF);
		self::$conf = $class->getStaticPropertyValue('config');

		if($debug === null && isset(self::$conf['debug'])){
			$debug = self::$conf['debug'];
		}

		if(!OBE_Core::init($debug)){
			return false;
		}

		OBE_Session::init(OBE_Core::getConfEnvVar('url'), self::$conf['name']);

		OBE_Core::registerShutDown([
			__CLASS__,
			'destroy'
		]);

		if(OBE_App::$db){
			if(OBE_Core::isEnvGlobalConf('varloader_configuration')){
				$cf = OBE_Core::getGlobalEnvVar('varloader_configuration');
				OBE_App::$Vars = new OBE_VarLoader();
				OBE_App::$Vars->Init($cf['table'], $cf['data_row'], $cf['id_row']);
			}

			// nacteni session z db

			if(self::getAppConfDef('klients', false)){
				if(self::isAppConf('user_session_init')){
					call_user_func(self::getAppConfDef('user_session_init'));
				}else{
					OBE_UserSession::Init();
				}

				if(self::getAppConfDef('user_admin', false)){
					OBE_UserSession::setAdminUser(true, false, true);
				}
			}
		}

		// zpracovani configurace
		self::useDynVar();

		OBE_App::$Smarty = new OBE_Smarty();

		return true;
	}

	static function rewriteConfConstsByVariant(
		$variant)
	{
		OBE_Log::logl1('prepisuji nastaveni dle layoutu `' . $variant . '`');
		if(isset(self::$conf['by-template']) && isset(self::$conf['by-template'][$variant])){
			foreach(self::$conf['by-template'][$variant] as $key => $val){
				self::$conf[$key] = $val;
			}
		}
	}

	static function destroy()
	{
		if(self::isAppConf('user_session_save')){
			call_user_func(self::getAppConfDef('user_session_save'));
		}

		OBE_App::destroy();
	}

	static function redirect(
		$toPage = null)
	{
		if($toPage === null){
			$toPage = self::$lastPage;
		}

		if(empty($toPage)){
			$toPage = OBE_Core::getConfEnvVar('url');
		}

// 		if(!OBE_Error::gotErrors() && !OBE_App::$db->anyError()){
		OBE_Http::headerRedirect($toPage);
// 		}else{
// 			throw new OBE_Exception('Nedojde k přesměrování protože se za běhu vyskytla chyba');
// 		}
		exit();
	}

	static function useDynVar()
	{
		OBE_Log::logl1('Načtení parametrů webu');

		$lc = & self::$conf;
		if(isset($lc['dynamic_vars']) && !empty($lc['dynamic_vars'])){
			foreach($lc['dynamic_vars'] as $k => $i){
				self::$dynamicVars[$k] = (new OBE_DynVar($k))->init($i);
			}
		}
	}

	static function LoadDBVars()
	{
		OBE_Log::logl1('Načtení DB vars');
		$lc = self::$conf;
		if(OBE_Core::isEnvGlobalConf('varloader_configuration') && isset($lc['load_vars'])){
			foreach($lc['load_vars'] as $key => $name){
				self::$dbVars[$key] = self::LoadVar($name);
			}
		}
	}

	/**
	 * nacteni promenne z databaze
	 * @param array $var_id
	 */
	static function loadVar(
		$var_id,
		$default = false)
	{
		if(array_key_exists($var_id, OBE_App::$newVars)){
			return OBE_App::$newVars[$var_id];
		}

		if(OBE_App::$Vars && OBE_App::$Vars->Load($var_id)){
			return OBE_App::$Vars->data;
		}
		return $default;
	}

	static function saveVar(
		$var_id,
		$data)
	{
		return OBE_App::$Vars->SaveD($var_id, $data);
	}

	public static function getBaseUrl()
	{
		return OBE_Http::correctProtocol(OBE_Core::getConfEnvVar('url'));
	}

	static function existDBVar(
		$groupName,
		$keyName)
	{
		if(isset(self::$dbVars[$groupName])){
			if(isset(self::$dbVars[$groupName][$keyName])){
				return true;
			}
		}
		return false;
	}

	/**
	 * vraci promenne z poli dynamicky nactenych z databaze
	 * @param String $groupName
	 * @param String $keyName
	 */
	static function getDBVar(
		$groupName,
		$keyName,
		$bReport = true)
	{
		if(isset(self::$dbVars[$groupName])){
			if(isset(self::$dbVars[$groupName][$keyName])){
				return self::$dbVars[$groupName][$keyName];
			}else{
				if($bReport){
					OBE_Log::logle('OBE_AppCore::getDBVar key "' . $keyName . '" is not set in self::$dbVars[' . $groupName . ']');
				}
			}
		}else{
			if($bReport){
				OBE_Log::logle('OBE_AppCore::getDBVar group "' . $groupName . '" is not exist, ' . $keyName);
			}
		}
		return null;
	}

	public static function setDBVar(
		$key,
		$values)
	{
		self::$dbVars[$key] = $values;
	}

	public static function mergeDBVar(
		$key,
		$values)
	{
		if(!empty($values)){
			self::$dbVars[$key] = array_merge(self::$dbVars[$key], $values);
		}
	}

	static function getIEVersion()
	{
		if(isset($_SERVER['HTTP_USER_AGENT'])){
			if(preg_match("/; MSIE ([0-9]{1,2})\.([0-9]{1})/i", $_SERVER['HTTP_USER_AGENT'], $results)){
				return $results[1];
			}
		}
		return null;
	}

	/**
	 * vraci hodnoty z AppConfiguration::$appConfig
	 * @param String $keyName
	 * @param bool $check - jestli vypsat warning
	 */
	public static function getAppConf(
		$keyName,
		$check = false)
	{
		if(isset(self::$conf[$keyName])){
			return self::$conf[$keyName];
		}else if($check){
			OBE_Log::logle('OBE_AppCore::getAppConf key "' . $keyName . '" is not exist');
		}
		return null;
	}

	/**
	 * vraci hodnoty z AppConfiguration::$appConfig
	 * @param String $keyName
	 * @param Void $def - tohle vrací když parametr není definován
	 */
	public static function getAppConfDef(
		$keyName,
		$def = null)
	{
		if(isset(self::$conf[$keyName])){
			return self::$conf[$keyName];
		}

		return $def;
	}

	public static function isAppConf(
		$keyName)
	{
		return isset(self::$conf[$keyName]);
	}

	public static function getFraze(
		$key)
	{
		return self::$dbVars['fraze'][$key];
	}
}