<?php


class ModuleInfoClass{

	public $id = NULL;

	// modul id
	/**
	 *
	 * @var string
	 */
	public $name = NULL;

	// nazev modulu
	/**
	 *
	 * @var array
	 */
	public $access = NULL;

	// pristupova prava
	/**
	 *
	 * @var ModuleUrlScope
	 */
	public $scope = NULL;

	/**
	 *
	 * @var ModuleFieldsRightsClass
	 */
	public $rights = NULL;

	/**
	 *
	 * @var ModuleControlClass
	 */
	public $control = NULL;

	public $selectedItem = NULL;

	// nastavuje kdyz to prijde pres ajax
	const MODE_AJAX = 'ajax';

	const MODE_NORMAL = 'normal';

	static $mode = NULL;

	/**
	 *
	 * @var array
	 */
	var $model = null;

	/**
	 *
	 * @param array $moduleData
	 */
	public function __construct($scope = NULL, $file = NULL, $name = NULL, $access = FormFieldRights::DELETE, $moduleId = NULL){
		if($file){
			$this->init($scope, $file, $name, $moduleId, $access);
		}
	}

	public function initByData($moduleData = NULL, $scope = NULL){
		self::$mode = (self::$mode == null) ? ModuleInfoClass::MODE_NORMAL : self::$mode;
		$this->rights = NULL;
		$this->scope = NULL;
		if($moduleData !== NULL){
			$this->init($scope, $moduleData[MModule::FILE], $moduleData[MModule::NAME], $moduleData[MModule::ID], $moduleData[MModule::ACCESS]);
			$this->model = $moduleData[MModule::MODEL];
		}
	}

	private function init($scope, $file, $name, $moduleId, $access){
		$this->id = $moduleId;
		$this->name = $name;
		$this->scope = ($scope) ? $scope : new ModuleUrlScope($file);
		$this->scope->info = $this;
		$a = AdminUserClass::getModuleAccesss($file);
		$this->access = ($a['access']) ? $a['access'] : $access;
		$this->rights = new ModuleFieldsRightsClass($this);
	}

	public function setMode($mode){
		OBE_Core::setOutputMode((($mode == self::MODE_AJAX) ? 'ajax' : null));
		self::$mode = $mode;
		return $this;
	}

	public function isAjax(){
		return $this->isMode(self::MODE_AJAX);
	}

	public function isMode($mode){
		return (self::$mode == $mode);
	}

	public function setAccess($access){
		$this->access = $access;
		$this->rights->access = $access;
		return $this;
	}

	function getView(){
		return [
			'id' => $this->id,
			'name' => $this->name
		];
	}

	public function trace($add = ''){
		OBE_Trace::dump(
			[
				'moduleid' => $this->id,
				'modulename' => $this->name,
				'access rights' => $this->access,
				' - mark - ' => $add
			]);
	}

	public function activityLog($aktivita, $popis, $recId = null, $type = null){
		AdminLogActivity::log($this->id, $aktivita, $popis, $this->getMasterById($recId), $recId, $type);
	}

	public function getMasterById($id){
		if($id && $this->model){
			$ffields = explode('|', $this->model['name']);
			if(!empty($ffields)){
				$class = 'M' . $this->model['class'];
				$modelObj = new $class();
				$data = $modelObj->FindOneById($id, $ffields);
				$data = MArray::GetMFields($data, $ffields, $this->model['class']);
				return implode(', ', $data);
			}
		}
		return null;
	}
}