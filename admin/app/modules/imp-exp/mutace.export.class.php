<?php
class MutationExportView extends ExportViewElement{
	static $allHead = ['value' => 'Fráze', 'name' => 'Popis', 'keyname' => 'Klíč', 'mutationid' => 'id', 'groupid' => 'Skupina', 'access' => 'Přístupová práva', 'visible' => 'Viditelnost'];

	private $fraze = NULL;

	/**
	 *
	 * @param AppModuleClass $moduleObj
	 * @param Array $fraze
	 */
	public function __construct($moduleObj, $fraze){
		$this->fraze = $fraze;

		if(!AdminUserClass::isSuperUser()){
			unset(self::$allHead['access']);
			unset(self::$allHead['visible']);
		}

		$this->setLabels('Export Frází');

		$csvWriter = new CSVWriterClass(self::$allHead);
		$formObj = $this->createForm($moduleObj);

		parent::__construct('mutation.csv', $formObj, $csvWriter);
	}

	/**
	 *
	 * @param AppModuleClass $moduleObj
	 * @param Array $fraze
	 */
	public function createForm($moduleObj){
		$formObj  = ViewsFactory::createForm($moduleObj->scope);

		$groups = $moduleObj->getModulGroupList(true);

		$formObj->createField('all', FormUITypes::CHECKBOX, 0, 'Exportovat vše', true);
		$formObj->createField('group', FormUITypes::DROP_DOWN, NULL, 'Omezit na skupinu', true);

		$groupField = $formObj->getField('group');
		$groupField->setList(['list' => $groups]);

		return $formObj;
	}

	/**
	 *
	 * @param AppFormClass2 $formObj
	 */
	public function handleFormSend($formObj){

		$head = self::$allHead;
		unset($head['value']);
		$fields = array_keys($head);

		$conditions = [];
		if(!AdminUserClass::isSuperUser()){
			$conditions = ['visible = 1', 'access = 0'];
		}

		if(!$formObj->getFieldValue('all')){
			$conditions[] = 'groupid = ' . $formObj->getFieldValue('group');
		}

		$mutations = new MMutations();

		$rawSql = $mutations->FindAll($conditions, $fields, ['groupid' => 'ASC', 'keyname' => 'ASC'], NULL, NULL, -1, true);

		return $rawSql;
	}

	function handleResultLine($item){
		if(isset($this->fraze[$item['keyname']])){
			$item['value'] = $this->fraze[$item['keyname']];
		}else{
			$item['value'] = '';
		}
		return $item;
	}
}