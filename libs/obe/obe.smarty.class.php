<?php


function init(){
	static $init = false;
	if(!$init){
		$init = true;
		require_once __DIR__ . '/../smarty-3.1.27/Smarty.class.php';
	}
}

init();

class OBE_Smarty extends Smarty{

	function __construct($template_dir = 'templates/', $debug = false){
		parent::__construct();

		$template_dir = OBE_Core::fileExists(APP_DIR_OLD . '/' . $template_dir);
		$config_dir = OBE_Core::fileExists($template_dir . 'configs/');
		$compile_dir = OBE_Core::fileExists(APP_DIR_OLD . '/temp/templates_c/');
		$cache_dir = OBE_Core::fileExists(APP_DIR_OLD . '/temp/cache/');

		$this->caching = false;
		$this->debugging = $debug;
		$this->setCompileId(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
		$this->setTemplateDir($template_dir);
		$this->setConfigDir($config_dir);
		$this->setCompileDir($compile_dir);
		$this->setCacheDir($cache_dir);
	}

	function display($template = null, $cache_id = null, $compile_id = null, $parent = null){
		$timer = (new OBE_Timer())->timer();
		OBE_Log::logl1('smarty start display ' . $template);
//     	$old = error_reporting(E_ALL & ~E_NOTICE);


		parent::display($template, $cache_id, $compile_id, $parent);

//     	error_reporting($old);
		OBE_Log::logl1('smarty finish display in: ' . $timer->timer() . 's');
	}
}