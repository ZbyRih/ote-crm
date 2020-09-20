<?php

class AdminMenuClass{
	/* vybranej modul */
	static $selectedModuleId = NULL;

	const URL_MODUL = k_module;

	static function init(){
		self::checkUrlForSystemAndModul();
	}

	static function checkUrlForSystemAndModul(){
		self::$selectedModuleId = AdminApp::$modules->getModuleIdByName(self::getModul());
		if(self::$selectedModuleId == NULL){
			self::$selectedModuleId = AdminApp::$modules->getDefault();
		}
	}

	static public function getModul(){
		return self::getItem(self::URL_MODUL);
	}

	private static function getItem($key){
		$item = NULL;
		if(!OBE_Http::emptyGet($key)){
			return OBE_Http::getGet($key);
		}else{
			return AdminUserClass::getSession($key);
		}
		return NULL;
	}

	static function getViewMenu(){
		$item = AdminApp::$modules[self::$selectedModuleId];
		$item['selected'] = true;
		AdminApp::$modules[self::$selectedModuleId] = $item;

		return ['MENU' => ['items' => AdminApp::$modules->getMenu(), 'selected' => self::$selectedModuleId]];
	}

	static function getSelectedModuleData(){
		if(self::$selectedModuleId !== NULL){
			return AdminApp::$modules->getModuleById(self::$selectedModuleId);
		}
		return NULL;
	}
}