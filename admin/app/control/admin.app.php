<?php

class AjaxException extends Exception{
}

class AdminApp{

	const MAIN_TPL = 'index.tpl';

	const CONTENT_TPL = 'module.view.tpl';

	const SESID_TR_LEN = 5;

	/** @var \Nette\DI\Container */
	public static $container;

	public static $settings;

	/**
	 * shortcat => name
	 * @var Array
	 */
	public static $langs = null;

	/**
	 * shortcat => id
	 * @var Array
	 */
	public static $langsMap = null;

	/**
	 *
	 * @var MModule
	 */
	public static $modules = null;

	public static $modulesName2Id = null;

	public static $lastLoc = null;

	private static $redirectAvaible = true;

	private static $displayTPL = self::MAIN_TPL;

	private static $contentTPL = self::CONTENT_TPL;

	private static $swfUpload = false;

	static $mainModule = null;

	static function run()
	{
		OBE_Log::logTimer(__CLASS__ . '::' . __FUNCTION__ . '');

		if(OBE_Http::issetGet('logout')){
			AdminUserClass::logOut();
		}

		if(AdminMenuClass::getModul() == 'cron'){
			AdminUserClass::$userId = 0;
			$moduleId = self::$modules->getModuleIdByName('cron');
			self::$mainModule = self::$modules->createModuleById($moduleId);
			self::$mainModule->Run();

			self::finish(false);
		}

		if(AdminMenuClass::getModul() == 'reset'){
			$moduleId = self::$modules->getModuleIdByName('reset');
			self::$mainModule = self::$modules->createModuleById($moduleId);
			self::$mainModule->Run();

			self::finish(false);
		}

		if(AdminUserClass::checkLogon()){
			if(self::$swfUpload){
				OBE_Http::setGet(k_ajax, 'asyncUpload');
			}
		}else{
			self::showLogForm();
		}

		self::initMenu();
		self::runModule();
		self::finish();
	}

	static function init()
	{
		$_SESSION['fileman_security'] = true;

		self::$langs = MArray::MapValToKey(OBE_Language::$langs, 'languageshortcut', 'languagename');
		self::$langsMap = MArray::MapValToKey(OBE_Language::$langs, 'languageshortcut', 'langid');

		if(count(self::$langs) < 2){
			self::$langs = null;
		}

		self::$modules = new MModule();
		self::$settings = OBE_AppCore::LoadVar(SettingsHelper::SETTINGS_VAR);

		self::$modulesName2Id = MArray::MapVal(self::$modules->getVisible(), 'name');
	}

	static function showLogForm()
	{
		self::appFinishWithLogOut();
	}

	static function appFinishWithLogOut()
	{
		OBE_App::$Smarty->clearAllAssign();
		OBE_App::$Smarty->assign([
			'MODULE' => [
				'name' => 'Přihlášení'
			]
		]);
		self::$contentTPL = 'login.tpl';
		self::Finish();
	}

	static function initMenu()
	{
		AdminMenuClass::init();
		AdminLogDBAccess::Start();
	}

	static function runModule()
	{
		OBE_Log::logTimer(__CLASS__ . '::' . __FUNCTION__ . '');

		$timer = (new OBE_Timer())->timer();

		if(self::$mainModule = self::createSelectedModule()){
			self::$mainModule->Run();
			self::$mainModule->Finalize(self::$mainModule->info->isMode(ModuleInfoClass::MODE_AJAX));
			self::$mainModule->fushAndCleanSession();
			OBE_Log::log('main module end');
		}else{
			OBE_Log::logw('module cannot crete');
		}

		OBE_Log::log('module runtime: ' . $timer->timer() . 's');
	}

	static function less()
	{
		// 		if(!self::$swfUpload && OBE_Core::getConfEnvVar('less') && OBE_Core::getOutputMode() != 'ajax'){
		// 			$timer = (new OBE_Timer())->timer();
		// 			$themepath = self::getThemePath();
		// 			$dir = APP_DIR_OLD . '/../' . $themepath . 'less/';
		// 			$outDir = APP_DIR_OLD . '/../' . $themepath . 'css/';
		// 			$rel = '"' . ltrim($themepath, '.') . 'css/"';

// 			$lessObj = new OBE_Less([
		// 				'prefix' => 'def_'
		// 			], [
		// 				'theme' => '"default"',
		// 				'rel' => $rel
		// 			]);

// 			$lessObj->compile(
		// 				[
		// 					'out' => $outDir . 'main.css',
		// 					'files' => [
		// 						$dir . 'bootstrap.less' => '',
		// 						$dir . 'materialadmin.less' => ''
		// 					]
		// 				]);
		// 			$lessObj->compile([
		// 				'out' => $outDir . 'libs.css',
		// 				'files' => [
		// 					$dir . 'libs.less' => ''
		// 				]
		// 			], [], [
		// 				'theme' => 'default'
		// 			]);
		// 			$lessObj->compile([
		// 				'out' => $outDir . 'mod.css',
		// 				'files' => [
		// 					$dir . 'mod.less' => ''
		// 				]
		// 			], [], [
		// 				'theme' => 'default'
		// 			]);

// 			$lessObj = new OBE_Less([
		// 				'prefix' => 'vil_'
		// 			], [
		// 				'theme' => 'viol',
		// 				'rel' => $rel
		// 			]);

// 			$lessObj->compile(
		// 				[
		// 					'out' => $outDir . 'main_viol.css',
		// 					'files' => [
		// 						$dir . 'bootstrap.less' => '',
		// 						$dir . 'materialadmin.less' => ''
		// 					]
		// 				]);
		// 			$lessObj->compile([
		// 				'out' => $outDir . 'libs_viol.css',
		// 				'files' => [
		// 					$dir . 'libs.less' => ''
		// 				]
		// 			]);
		// 			$lessObj->compile([
		// 				'out' => $outDir . 'mod_viol.css',
		// 				'files' => [
		// 					$dir . 'mod.less' => ''
		// 				]
		// 			]);
		// 			OBE_Log::log('less: elapsed: ' . $timer->timer() . 's');
		// 		}
	}

	static function finish(
		$displayTPL = null)
	{
		AdminLogDBAccess::Stop();

		if($displayTPL !== false){

			if(OBE_Core::getOutputMode() != 'ajax'){
				OBE_App::$Smarty->assign(self::createView());
			}

			if(!$displayTPL){
				$displayTPL = self::$displayTPL;
			}

			OBE_App::$Smarty->assign('ContentTemplate', self::$contentTPL);
			OBE_App::$Smarty->display($displayTPL);
		}

		OBE_Log::logTimer('AdminApp::finish and exit');

// 		exit();
	}

	static function createView()
	{
		if(AdminUserClass::isLogged()){
			$menu = AdminMenuClass::getViewMenu();
			$user = AdminUserClass::getViewUser();
		}else{
			$menu = $user = [];
		}

		$theme = self::getViewTheme($user);
		$lang = self::getViewLang();
		$scripts = \App\Extensions\App\WebPackManifest::script('/', [
			'/dist/css/theme_def_css.css',
			'/dist/js/app_js.js'
		]);
		$another = [
			'scripts' => $scripts,
			'SELECT_MODULE' => MODULES::SELECT,
			'BACKEND' => [
				'NAME' => OBE_AppCore::getAppConf('title')
			],
			'httpBase' => OBE_Core::getConfEnvVar('url'),
			'protocol' => OBE_Core::$protocol,
			'session_id' => OBE_Strings::simple_transfer(OBE_Session::getID(), self::SESID_TR_LEN),
			'SETTINGS' => self::$settings,
			'js_scripts' => [], // OBE_AppCore::getJavaScripts(),
			'demo' => OBE_Core::getConfEnvVar('demo'),
			'debug' => OBE_Core::$debug,
			'debugBar' => null,
			'ajax' => OBE_Http::issetGet(k_ajax),
			'langs' => OBE_Language::$langs,
			'langid' => OBE_Language::$id
		];

		return array_merge($menu, $user, $theme, $lang, $another);
	}

	static function getViewTheme(
		$user)
	{
		return [
			'THEME' => [
				'stylePath' => self::getThemePath(),
				'imgsPath' => self::getThemeImgPath(),
				'color' => ((isset($user['USER']) && $user['USER']['theme'] == 1) ? 'viol' : '')
			]
		];
	}

	static function getThemePath()
	{
		if(!isset(OBE_AppCore::$dynamicVars['theme'])){
			OBE_AppCore::init();
		}
		return './themes/' . OBE_AppCore::$dynamicVars['theme']->get() . '/';
	}

	static function getThemeImgPath()
	{
		return self::getThemePath() . OBE_AppCore::getAppConf('style_imgs_path');
	}

	static function getViewLang()
	{
		return [];
		$revMap = array_flip(self::$langsMap);
		return [
			'LANG' => [
				'name' => 'lang',
				'list' => self::$langs,
				'value' => $revMap[OBE_Language::$id],
				'letOnly' => 'module'
			]
		];
	}

	static function setDisplayTpl(
		$displayTPL = null)
	{
		self::$displayTPL = $displayTPL;
	}

	static function setMainTpl(
		$mainTPL = null)
	{
		self::$contentTPL = $mainTPL;
	}

	/**
	 *
	 * @return AppModuleClass
	 */
	public static function createSelectedModule()
	{
		if($selectedModuleData = AdminMenuClass::getSelectedModuleData()){
			$modulObj = self::$modules->createModuleByData($selectedModuleData);

			AdminLogDBAccess::setModul($selectedModuleData['name']);

			return $modulObj;
		}
		return null;
	}

	static function blockRedirect()
	{
		self::$redirectAvaible = false;
	}

	static function Redirect(
		$link)
	{
		if(self::$redirectAvaible){
			self::$mainModule->fushAndCleanSession();
			OBE_AppCore::redirect('?' . $link);
		}
	}

	static function PageForceReload()
	{
		OBE_App::$Smarty->assign('force_reload', true);
	}

	/**
	 *
	 * @param string $msg
	 * @param string $typ - [success, info, warning, danger]
	 */
	static function postMessage(
		$msg,
		$typ)
	{
		$msgs = OBE_Session::read('post_message');
		if(!is_array($msgs)){
			$msgs = [];
		}
		$msg = MArray::AllwaysArray($msg);
		foreach($msg as $m){
			$msgs[] = [
				'text' => $m,
				'type' => $typ
			];
		}

		OBE_Session::write('post_message', $msgs);
	}

	static function getPostMessages()
	{
		if($msgs = OBE_Session::read('post_message')){
			if(is_array($msgs)){
				foreach($msgs as $k => $m){
					$msgs[$k]['text'] = str_replace('|', '<br />', $m['text']);
				}
				return $msgs;
			}
		}

		return null;
	}

	static function clearPostMessages()
	{
		OBE_Session::write('post_message', null);
	}
}