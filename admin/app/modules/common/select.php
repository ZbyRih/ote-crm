<?php


class ModulSelect extends AppModuleClass{

	const KEY_SUBSEL_CONTENT = 'ajaxSubSel';

	const SELECT_LIST = 'l';

	const SELECT_TREE = 't';

	var $ajaxRequests = [
		'select' => 'initModuleSelectPopContent'
	];

	function Ajax(){
		$ajaxRequest = OBE_Http::getGet(k_ajax);
		if(isset($this->ajaxRequests[$ajaxRequest])){
			parent::Ajax($ajaxRequest);
			return;
		}else{
			$this->initModuleSelectPopContent($this->info);
		}
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean|void|void|boolean|boolean
	 */
	function initModuleSelectPopContent($info){
		if(OBE_Http::issetGet(k_formodule) && !OBE_Http::issetGet(k_type)){
			return $this->createModulesList($info, OBE_Http::getGet(k_formodule));
		}else if(OBE_Http::issetGet('frommodule') && !OBE_Http::issetGet(k_type)){
			$modul = OBE_Http::getGet('frommodule');
			if(!is_numeric($modul)){
				$modul = AdminApp::$modules->getModuleIdByName($modul);
			}
			return $this->createModuleItemsList($info, AdminApp::$modules->getModuleById($modul));
		}else if(OBE_Http::issetGet(k_type)){
			if(OBE_Http::isGetIs(k_type, 'g')){
				return $this->createMenuGroupSelect($info);
			}else{
				$userObj = new MUser();
				$selectModule = OBE_Http::getGet(k_type);
				if(OBE_Http::isGetIs(k_type, 'm')){
					$selectModule = MODULES::MENU;
				}

				return $this->createModuleItemsList($info, AdminApp::$modules[$selectModule], OBE_Http::getGet(k_formodule), $ajaxRequest);
			}
		}
	}

	function createModulesList($info, $forModule){
		$userObj = new MUser();

		$avaibleModules = AdminApp::$modules;
		if($forModule == MODULES::MENU){
			$avaibleModules = MArray::FilterMArray($avaibleModules, 'tomenu', true);
		}elseif($forModule == MODULES::LAYOUT){
			$avaibleModules = MArray::FilterMArray($avaibleModules, 'tolayout', true);
		}

		$moduleList = $avaibleModules;
		$moduleNameList = MArray::MapValToKey($avaibleModules, 'id', 'name');

		if(isset($moduleNameList[MODULES::MENU])){
			if($forModule == MODULES::MENU){
				unset($moduleNameList[MODULES::MENU]);
			}
			$moduleNameList = $this->addMenuGroups($forModule, $moduleNameList);
		}

		$selectModule = (new OBE_DynVar(self::KEY_SUBSEL_CONTENT, [
			OBE_DynVar::GET,
			OBE_DynVar::SES
		], null, array_keys($moduleNameList)))->get();

		$field = ViewsFactory::createField(
			[
				'tpl' => 'select.field',
				'type' => FormUITypes::DROP_DOWN,
				'key' => self::KEY_SUBSEL_CONTENT,
				'value' => $selectModule,
				'list' => $moduleNameList
			]);

		$this->views->add($field);

		if(isset($moduleList[$selectModule])){
			return $this->createModuleItemsList($info, $moduleList[$selectModule], $forModule);
		}elseif(OBE_Http::isGetIs(k_type, 'g')){
			return $this->createMenuGroupSelect($info);
		}
		return false;
	}

	function addMenuGroups($forModule, $moduleNameList){
		$moduleNameList['a'] = 'Celá menu';
		return $moduleNameList;
	}

	function createModuleItemsList($info, $module, $forModule = NULL, $ajax = NULL){
		$className = AdminApp::$modules->load($module['file']);
		$appModuleObj = new $className($module);

		if($module['id'] == MODULES::MENU){
			$this->handleMenuModule($appModuleObj);
		}

		if($ajax){
			$appModuleObj->Ajax($ajax);
			return;
		}

		$appModuleObj->info->setMode(ModuleInfoClass::MODE_AJAX);

		if(method_exists($appModuleObj, 'createAjaxSelectList')){
			$List = $appModuleObj->createAjaxSelectList($appModuleObj->info);
		}else{
			$List = $appModuleObj->_createMainListObj($appModuleObj->info);
			$List->actions->resetActions(ListAction::SELECT);
			$List->setMode('select');
		}

		if($module['listtype'] == self::SELECT_TREE){
			$List = $appModuleObj->createTreeListObj($List);
		}

		$this->views->add($List);

		return true;
	}

	function createMenuGroupSelect($info){
		$sub = (new SubModule())->createAsSub(null, 'groups');
		$listObj = ViewsFactory::createList($sub->info);
		$listObj->configByArray([
			'actions' => [
				ListAction::SELECT
			],
			'cols' => [
				'Menu' /* => 'name' */
			]
		]);

		$menuList = $menuItems = OBE_AppCore::LoadVar('menu');

		$listObj->setData($menuList);
		$this->views->add($listObj);

		return true;
	}

	/**
	 *
	 * @param ModulMenu $appModule
	 * @return void
	 */
	function handleMenuModule($appModule){
		$menus = OBE_AppCore::LoadVar('menu');
		$menuSetId = (new OBE_DynVar(MENU_SET_ID_KEY, [
			OBE_DynVar::GET,
			OBE_DynVar::SES
		]))->setDef(reset($menus))->get();
// 		$this->views->add(
// 			ViewsFactory::createField([
// 				  'type' => FormUITypes::DROP_DOWN
// 				, 'name' => MENU_SET_ID_KEY
// 				, 'value' => $menuSetId
// 				, 'list' => $menus
// 			]
// 		));
		$appModule->menuId = $menuSetId;
	}
}