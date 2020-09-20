<?php

class ViewsFactory{

	public static function createView(
		$data)
	{
		$obj = new ViewElementClass();
		$obj->data = $data;
		return $obj;
	}

	public static function newCardPane(
		$head = null)
	{
		$card = new NewCardElement();
		$card->setName($head);
		return $card;
	}

	public static function createLink(
		$link,
		$fraze,
		$ico = null,
		$confirm = false,
		$popup = false)
	{
		$obj = new LinkViewElement();
		$obj->init($link, $fraze, $ico, $confirm, $popup);
		return $obj;
	}

	/**
	 *
	 * @param string $msg
	 * @param string $typ - 'info', 'success', 'warning', 'danger'
	 */
	public static function createMessage(
		$msg = null,
		$typ = null)
	{
		$obj = new MessageViewClass('message');
		if($msg){
			$obj->data[] = [
				'text' => $msg,
				'type' => $typ
			];
		}
		return $obj;
	}

	public static function createJump(
		$jumpto)
	{
		$obj = new ViewElementClass('jump');
		$obj->data = [
			'jumpto' => $jumpto
		];
		return $obj;
	}

	public static function createField(
		$def)
	{
		$obj = new ViewElementClass('field');
		$class = FromUITypesHandlersClass::getClass($def['type']);
		$field = new $class($def);
		$obj->data = $field->getView();
		return $obj;
	}

	public static function createFile(
		$img)
	{
		$obj = new ViewElementClass('file');
		$obj->data = [
			'file' => 'fields/variable.media',
			'data' => $img
		];
		return $obj;
	}

	public static function createHtml(
		$html)
	{
		$obj = new ViewElementClass('html');
		$html = str_replace('src="', 'src="/', $html);
		$obj->data = $html;
		return $obj;
	}

	/**
	 *
	 * @param integer/string $module
	 * @param string $link
	 * @param string $fraze
	 */
	public static function createAjaxSelect(
		$module,
		$link,
		$fraze,
		$class = null,
		$ico = null)
	{
		$obj = new AjaxSelectViewElement();
		$obj->init($module, $link, $fraze, $class, $ico);
		return $obj;
	}

	public static function createPack(
		$float = true)
	{
		return (new PacketViewElement())->init($float);
	}

	public static function createStatus(
		$name,
		$statuses)
	{
		$obj = new StatusViewElement();
		$obj->init($name, $statuses);
		return $obj;
	}

	public static function createStats(
		$name = '',
		$data = [],
		$type = StatsViewElement::LISTV)
	{
		$obj = new StatsViewElement();
		$obj->init($name, $data, $type);
		return $obj;
	}

	public static function createGraph(
		$name = '',
		$data = [],
		$subType = StatsViewElement::LINE)
	{
		$obj = new StatsViewElement();
		$obj->init($name, $data, StatsViewElement::GRAPH);
		$obj->graphType = $subType;
		return $obj;
	}

	/**
	 *
	 * @param ModuleInfoClass $moduleInfo
	 * @return ListClass
	 */
	public static function createList(
		$moduleInfo)
	{
		return (new ListClass())->attach($moduleInfo);
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param string $form
	 * @return ModelListClass
	 */
	public static function createModelList(
		$info)
	{
		return (new ModelListClass())->attach($info);
	}

	public static function createGroupedModelList(
		$info)
	{
		return (new ModelGroupListClass())->attach($info);
	}

	/**
	 *
	 * @param ModuleUrlScope $scope
	 * @param array $fields
	 * @param string $formName
	 * @param string $action
	 * @param string $imageType
	 * @return AppFormClass2
	 */
	public static function createForm(
		$scope = null,
		$fields = [],
		$formName = null,
		$action = null)
	{
		return (new AppFormClass2())->init($scope, $fields, $formName, $action);
	}

	/**
	 *
	 * @param ModelClass $modelObject
	 * @param ModuleInfoClass $moduleInfo
	 * @param string $form
	 * @param string $imageType
	 * @return ModelFormClass2
	 */
	public static function createModelForm(
		$modelObject,
		$moduleInfo,
		$form = 'main')
	{
		return (new ModelFormClass2())->initM($modelObject, $moduleInfo, $form);
	}

	public static function createViewForm(
		$info,
		$configCallback)
	{
		$F = self::createForm($info->scope);
		call_user_func_array($configCallback, [
			$F
		]);
		$F->setAccess(FormFieldRights::VIEW);
		$F->buttons->clear();
		return $F;
	}

	public static function createViewModelForm(
		$model,
		$info,
		$configCallback,
		$name = 'main')
	{
		$F = self::createModelForm($model, $info, $name);
		$F->setAccess(FormFieldRights::VIEW);
		call_user_func_array($configCallback, [
			$F
		]);
		$F->processForm();
		$F->buttons->clear();
		return $F;
	}

	/**
	 *
	 * @param ModuleUrlScope $scope
	 * @return MultiFormClass
	 */
	public static function createMultiForm(
		$scope)
	{
		return (new MultiFormClass())->init($scope);
	}

	public static function createCloud(
		$model,
		$countRow,
		$itemName)
	{
		return (new CloudViewClass())->init($model, $countRow, $itemName);
	}

	/**
	 *
	 * @param ModuleUrlScope $scope
	 * @param string $key
	 * @param array $list
	 * @param integer $defPos alone = 0, prvni = 1, posledni = 2, mezi = 3
	 * @return ShortNavClass
	 */
	public static function createShortNav(
		$scope,
		$key,
		$items,
		$defPos = null)
	{
		$defPos = ($defPos) ? $defPos : ShortNavClass::mezi;
		$obj = new ShortNavClass();
		return $obj->initShortNav($scope, $key, $items, $defPos);
	}

	/**
	 *
	 * @param ModelListClass $modelList
	 * @param ModuleUrlScope $scope
	 * @param string $displayRowName
	 * @return ModelShortNavClass
	 */
	public static function createModelShortNav(
		$modelList,
		$scope,
		$displayRowName = null)
	{
		return (new ModelShortNavClass())->init($modelList, $scope, $displayRowName);
	}

	/**
	 *
	 * @param ModelListClass $configForList
	 * @param ModuleInfoClass $info
	 * @param string $parentKey
	 * @param string $nameKey
	 * @param string $form
	 * @param string $addSpec
	 * @param string $nodename
	 * @return ModelViewTreeClass
	 */
	public static function createTree(
		$configForList,
		$info,
		$parentKey,
		$nameKey,
		$addSpec = null,
		$form = 'main',
		$nodename = 'sub')
	{
		return (new ModelViewTreeClass())->init($configForList, $info, $parentKey, $nameKey, $addSpec, $form, $nodename);
	}

	/**
	 *
	 * @param ModuleInfoClass $moduleInfo
	 * @param string $title
	 * @param boolean $mustSelect
	 * @return TabViewClass
	 */
	public static function createTabs(
		$moduleInfo,
		$title = '',
		$mustSelect = true,
		$inputKey = null)
	{
		$obj = new TabViewClass();
		$obj->init($moduleInfo, $title, $mustSelect, $inputKey);
		return $obj;
	}

	public static function createImport(
		$fileFieldKey,
		$importConfig)
	{
		$obj = new ImportViewElement();
		$obj->init($fileFieldKey, $importConfig);
		return $obj;
	}

	/**
	 *
	 * @param string $fileExpName
	 * @param AppFormClass2 $formObj
	 * @param CSVWriterClass $exporter
	 * @return ExportViewElement
	 */
	public static function createExport(
		$fileExpName,
		$formObj,
		$exporter)
	{
		$obj = new ExportViewElement();
		$obj->init($fileExpName, $formObj, $exporter);
		return $obj;
	}
}