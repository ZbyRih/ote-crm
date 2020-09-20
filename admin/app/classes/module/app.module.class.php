<?php

class AppModuleClass extends ModuleViewClass{

	const DEFAULT_AJAX_INDEX_TPL = 'index.ajax.tpl';

	const DEFAULT_AJAX_NONE_TPL = 'index.ajax.tpl';

	// pomalu nebude potreba

	/**
	 * object modelu pro konkretni modul
	 * @var ModelClass
	 */
	var $model = NULL;

	/**
	 * nazev primarniho modelu ktery modul pouziva
	 * @var string
	 */
	var $modelName = NULL;

	/**
	 *
	 * @var Array ('request' => '_callback')
	 */
	var $ajaxRequests = [];

	var $actions = [];

	var $title = NULL;

	private $helpContent = NULL;

	/**
	 *
	 * @param array/string $modul
	 * @param SubModule $parent
	 * @param string $name
	 */
	function __construct($moduleData = NULL, $parent = NULL, $name = null){
		parent::__construct($moduleData, $parent, $name);

		new AttachmentCtrlClass2();

		$this->createAjaxHelp();

		if($this->modelName){
			$this->model = new $this->modelName(); // mozna neni potreba
		}
	}

	function Run(){
		try{
			$ajaxRequest = null;

			if(OBE_Http::issetGet(k_ajax)){ // master ajax request

				$this->info->setMode(ModuleInfoClass::MODE_AJAX);
				$ajaxRequest = OBE_Http::getGet(k_ajax);

				if(isset($this->ajaxRequests[$ajaxRequest])){
					$callback = $this->ajaxRequests[$ajaxRequest];

					if(!is_array($callback)){
						$callback = [
							$this,
							$callback
						];
					}

					call_user_func($callback, $this->info);
					AdminApp::setDisplayTpl(self::DEFAULT_AJAX_INDEX_TPL);
					return;
				}
			}

			$this->initTopMenu();

			$msgView = ViewsFactory::createMessage();
			$this->views->add($msgView);

			$this->checkActions();

			$this->callback();

			$msgView->data = AdminApp::getPostMessages();

			if(!is_null($ajaxRequest)){
				OBE_Http::respond('500 Internal Server Error', 'Ajax request \'' . $ajaxRequest . '\' není implementován v ' . get_class($this));
			}

			if($this->info->isMode(ModuleInfoClass::MODE_AJAX)){
				OBE_Http::respond('500 Internal Server Error', 'Ajax požadavek nebyl řádně zpracován');
			}
		}catch(AjaxException $e){
			OBE_Http::respond('500 Internal Server Error', $e->getMessage());
		}
	}

	function Finalize($ajax){
		if($ajax){
			OBE_App::$Smarty->assign(
				[
					'MODULE' => [
						'view' => $this->scope->getView(),
						'elements' => $this->views,
						'scope' => $this->scope
					] + $this->info->getView()
				]);
		}else{
			OBE_App::$Smarty->assign(
				[
					'MODULE' => [
						'view' => $this->scope->getView(),
						'menu' => $this->topMenu->getView(),
						'elements' => $this->views,
						'scope' => $this->scope,
						'help' => $this->haveHelp(),
						'breadcrumbs' => [],
						'headTitle' => $this->title
					] + $this->info->getView()
				]);
			AdminApp::clearPostMessages();
		}
	}

	/**
	 * vytvori model pro danej modul definovanej v modelName
	 * @param $forListRestrictedByLanguage - jazykova restrikce pres entity.langid
	 * @return ModelClass
	 */
	function getBaseModel($forListRestrictedByLanguage = false){
		if($this->modelName === NULL){
			throw new OBE_Exception('V modulu neni nastavena proměnná modelName');
		}
		$model = new $this->modelName();
		if($forListRestrictedByLanguage){
			$this->_assignLanguageIdToModel($model);
		}
		return $model;
	}

	function _assignLanguageIdToModel($model){
		if(isset($model->associatedModels[MEntity::HANDLER])){
			$model->associatedModels[MEntity::HANDLER]['conditions'][] = MEntity::HANDLER . '.langid = ' . OBE_Language::$id;
		}
	}

	function checkActions(){
		$clb = new CallBackControlClass($this, $this->actions);
		$clb->catchByGet($this->scope->getActionKey());
	}

	/**
	 * zkontroluje $_POST na pritomnost k_extid, k_colid, k_data
	 * @return AjaxTriade
	 */
	function checkAjaxTriade(){
		$ajaxObj = new AjaxTriade(k_colid, k_data, k_extid);
		if($ajaxObj->checkAjaxTriade()){
			return $ajaxObj;
		}
		return NULL;
	}

	/**
	 *
	 * @param string $type
	 * @return ViewElementClass
	 */
	public function statsView($type){
		return new StatsViewElement();
	}

	function createAjaxHelp(){
		$helpObj = new MHelper();
		if($this->helpContent = $helpObj->getContextById($this->info->id)){
			$this->ajaxRequests['help'] = [
				$this,
				'getHelp'
			];
		}
	}

	function haveHelp(){
		if(isset($this->ajaxRequests['help'])){
			return true;
		}
		return false;
	}

	function getHelp(){
		echo '<div class="help-content">' . $this->helpContent . '</div>';
		OBE_Http::respondCode('200 OK');
		exit();
	}

	function getModulGroupList($bDontAddNull = false){
		$groupObj = new MGroups();
		if($groups = $groupObj->FindBy('moduleid', $this->info->id, [
			'visible = 1'
		])){
			$groups = MArray::GetMArrayForOneModel($groups, $groupObj->name);
			$groupList = MArray::MapValToKey($groups, 'groupid', 'groupname');
			if(!$bDontAddNull){
				$this->_addNullToList($groupList);
			}
			return $groupList;
		}else{
			if(!$bDontAddNull){
				$groupList = [];
				$this->_addNullToList($groupList);
				return $groupList;
			}
		}
		return NULL;
	}

	function createGroupsManageInModule($parentAppModule){
		if($groupsModuleObj = AdminApp::$modules->createModuleById(MODULES::GROUPS)){
			return $groupsModuleObj->createLocalGroupListAndEdit($this->info->id, $parentAppModule);
		}
	}

	function _addNullToList(&$list, $primaryKey = 'id', $nameKey = 'name', $modelName = NULL, $onBegin = true){
		if($onBegin){
			MArray::unshift($list, [
				'NULL' => '- nevybráno -'
			]);
		}else{
			$list['NULL'] = '- nevybráno -';
		}
	}
}