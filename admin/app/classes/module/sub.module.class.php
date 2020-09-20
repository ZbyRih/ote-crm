<?php


class SubModule{

	/** @var ModulSession[] */
	private static $sessions = [];

	const DEFAULT_VIEW = 'list';

	const CREATE = 'create';

	/**
	 *
	 * @var ModuleInfoClass
	 */
	public $info = null;

	/**
	 *
	 * @var ModuleUrlScope
	 */
	public $scope = null;

	/**
	 *
	 * @var ModuleControlClass
	 */
	public $control = null;

	public $handlers = [];

	/**
	 *
	 * @var ModulViewsManager
	 */
	public $views = null;

	/**
	 *
	 * @param array/string $modul
	 * @param SubModule $parent
	 */
	function __construct($modul = null, $parent = null, $name = null){
		if(is_array($modul)){
			$this->views = new ModulViewsManager();
			$this->standartMain($modul, $parent);
		}else if($parent && is_string($modul)){
			$this->views = $parent->views;
			$this->createAsSub($parent, $modul, $name);
		}
		if(!$parent && $this->scope){
			if($this->scope->isSetRecId()){
				AdminApp::$lastLoc = $this->scope->getModulLink() . $this->scope->getRecordLink();
			}else{
				AdminApp::$lastLoc = '';
			}
		}
		OBE_Log::log(' -- create module - ' . get_class($this));
	}

	public function setHandlers($handlers){
		$this->handlers = $handlers;
		$this->control = new ModuleControlClass($this->info, $this, $this->handlers);
		return $this;
	}

	/**
	 *
	 * @param array $moduleData
	 * @param SubModule $parent
	 */
	public function standartMain($moduleData, $parent = null){
		$this->scope = new ModuleUrlScope($moduleData[MModule::FILE], (($parent) ? $parent->scope : null));
		$this->info = new ModuleInfoClass();
		$this->info->initBydata($moduleData, $this->scope);
		$this->control = new ModuleControlClass($this->info, $this, $this->handlers);
		return $this;
	}

	/**
	 *
	 * @param ModuleInfoClass $parent
	 * @param string $modul
	 * @param string $name
	 */
	public function createAsSub($parent, $modul, $name = null){
		$this->scope = new ModuleUrlScope($modul, (($parent) ? $parent->scope : null));
		$this->info = new ModuleInfoClass($this->scope, $modul, $name);
		$this->control = new ModuleControlClass($this->info, $this, $this->handlers);
		return $this;
	}

	/**
	 *
	 * @param ModuleInfoClass $parent
	 * @param string $modul
	 * @param stdClass $callbackContext
	 * @param array $handlers
	 * @param boolean $debug
	 */
	public function createFreeContext($parent, $modul, $callbackContext = null, $handlers = [], $debug = false){
		$scope = new ModuleUrlScope($modul, (($parent) ? $parent->scope : null));
		$module = new ModuleInfoClass($scope, $modul);
		$control = new ModuleControlClass($module, $callbackContext, $handlers);
		return $control->handle($debug);
	}

	/**
	 *
	 * @return boolean
	 */
	function callback(){
		return $this->control->handle();
	}

	function setView($view){
		$this->info->scope->setView($view);
		return $this;
	}

	function activityLog($aktivita, $popis, $recId = null, $type = null){
		$this->info->activityLog($aktivita, $popis, $recId, $type);
	}

	public function getSession($sessionKey = null){
		$sessionKey = ($sessionKey) ? $sessionKey : $this->info->scope->module;
		if(array_key_exists($sessionKey, self::$sessions)){
			return self::$sessions[$sessionKey];
		}else{
			$s = new ModulSession($sessionKey);
			self::$sessions[$sessionKey] = $s;
			return $s;
		}
	}

	function fushAndCleanSession(){
		if(!empty(self::$sessions)){
			foreach(self::$sessions as $k => $s){
				$s->flush();
			}
			self::$sessions = [];
		}
	}
}