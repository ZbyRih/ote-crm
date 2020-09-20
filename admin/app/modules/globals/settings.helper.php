<?php

class SettingsHelper extends MSettings{

	const SETTINGS_VAR = 'front';

	static $contactGroup = [
		'modelname' => 'MGroup',
		'valrow' => 'groupid',
		'namerow' => 'groupname',
		'notNull' => true,
		'conditions' => [
			'moduleid' => MODULES::CONTACTS
		]
	];

	function __construct()
	{
		parent::__construct();
	}

	function getGroups()
	{
		return $this['groups'];
	}

	/**
	 *
	 * @param Integer $groupId
	 * @param AppFormClass2 $form
	 */
	function setUpForm(
		$groupId,
		$form)
	{
		$items = $this['groups'][$groupId]['items'];

		if(!AdminUserClass::isSuperUser()){
			$items = MArray::FilterNegMArray($items, 'access', 1);
			$items = MArray::FilterNegMArray($items, 'visible', false);
		}

		$ls = $this->loadLangSettings();
		$nls = OBE_AppCore::LoadVar(self::SETTINGS_VAR);

		$data = $ls + $nls;

		$ndata = [];

		foreach($items as $i){
			$name = $i['name'];

			if(AdminUserClass::isSuperUser()){
				$name = $i['key'] . ' - \'' . $name . '\'' . (($i['access']) ? '*' : '');
			}

			if($field = $form->createField($i['key'], $i['field'], '', $name)){
				$field->setStatuses([
					'lock' => $i['access'],
					'globe' => $i['lang']
				]);

				$field->addToForm($form);
			}

			if(isset($data[$i['key']])){
				$ndata[$i['key']] = $data[$i['key']];
			}
		}

		foreach($this['spec'] as $s){
			foreach($s['items'] as $it){
				if($s['type'] == 'layout'){
					$this->layoutField($form, $it['key']);
				}else if($s['type'] == 'contact'){
					$this->contactGroupFields($form, $it['key']);
				}
			}
		}

		return $ndata;
	}

	function loadLangSettings()
	{
// 		$langObj = new MLanguages();
// 		$lang = $langObj->FindOneById(OBE_Language::$id, [
// 			'settings'
// 		]);
// 		if(!empty($lang['Language']['settings'])){
// 			return unserialize($lang['Language']['settings']);
// 		}
		return [];
	}

	function getItems(
		$groupId)
	{
		$items = $this['groups'][$groupId]['items'];

		if(!AdminUserClass::isSuperUser()){
			$items = MArray::FilterNegMArray($items, 'access', 0);
			$items = MArray::FilterNegMArray($items, 'visible', 1);
		}

		return $items;
	}

	function save(
		$data)
	{
		$old = MArray::AllwaysArray($this->loadLangSettings());
		$new = $data + $old;
		$keys = $this->getAllKeys('lang', 1);

		$this->saveLangSettings($this->getValidData($new, $keys));

		$old = MArray::AllwaysArray(OBE_AppCore::LoadVar(self::SETTINGS_VAR));
		$new = $data + $old;
		$keys = $this->getAllKeys('lang', 0);

		$val = $this->getValidData($new, $keys);

		OBE_App::$Vars->SaveD(self::SETTINGS_VAR, $this->getValidData($new, $keys));

		return $data;
	}

	function getValidData(
		$data,
		$keys)
	{
		$diff = array_diff_key($keys, $data);
		if(!empty($diff)){
			$data = array_merge(array_fill_keys(array_keys($diff), NULL), $data);
		}
		return array_intersect_key($data, $keys);
	}

	function getAllKeys(
		$fkey = NULL,
		$fvalue = NULL)
	{
		$all = [];
		foreach($this['groups'] as $g){
			$all = array_merge($all, $g['items']);
		}

		$all = MArray::MapVal(($fkey) ? MArray::FilterMArray($all, $fkey, $fvalue) : $all, 'key');

		return array_flip($all);
	}

	function saveLangSettings(
		$settings)
	{
	// 		$langObj = new MLanguages();
	// 		$sdata['Language']['langid'] = OBE_Language::$id;
	// 		$sdata['Language']['settings'] = serialize($settings);
	// 		$langObj->Save($sdata);
	}

	function layoutField(
		$formObj,
		$fieldKey)
	{
		// 		$variants = new FrontLayoutVariants();

// 		if($formObj->isFieldExists($fieldKey)){
		// 			$formObj->getField($fieldKey)
		// 				->setList(MArray::MapVal($variants, 'name'));
		// 		}else{
		// 			OBE_Log::log('Settings key `' . $fieldKey . '` doesnt exist');
		// 		}
	}

	function contactGroupFields(
		$formObj,
		$fieldKey)
	{
		if($formObj->isFieldExists($fieldKey)){
			$formObj->getField($fieldKey)->setList(self::$contactGroup);
		}else{
			OBE_Log::log('Settings key `' . $fieldKey . '` doesnt exist');
		}
	}
}