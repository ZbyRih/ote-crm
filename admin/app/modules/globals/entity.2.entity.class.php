<?php
class Entity2EntityExtension{

	private static $default = [
		  'relSaveModel' => 'MEntity2EntityRelSave'
		, 'relShowModel' => 'MAttachmentRel'
		, 'parentIdRow' => 'l_entityid'
		, 'relEntityId' => 'r_entityid'
		, 'cols' => ['Attachment' => ['fileid' => 'Reprezentace souboru', 'filename' => 'Název souboru'], 'MEntity2EntityRelSave' => ['description' => 'Popis souboru']]
		, 'fieldTplMap' => ['Attachment' => ['fileid' => FormUITypes::SELECT_ATTCH]]
	];

	var $relSaveModel = null;
	var $relShowModel = null;
	var $parentIdRow = null;
	var $relEntityId = null;

	var $cols = null;
	var $fieldTplMap = null;

	/**
	 *
	 * @var AppModuleClass
	 */
	private $parent = null;

	private $descriptionSrc = null;
	private $positionSrc = null;

	private $configArray = [
		  'actions' => [ListAction::DELETE]
		, 'pagination' => false
	];

	/**
	 * @param AppModuleClass $module
	 * @param array $config
	 */
	public function __construct($module, $config = null){
		$this->parent = $module;

		MArray::extendObject($this, (($config) ? $config : self::$default));
	}

	public function setDescription($descriptionSrc, $bEditAble = true){
		if($bEditAble){
			$this->configArray['ajaxRowsEdit'] = [$this->relSaveModel => [$descriptionSrc => FormUITypes::TEXT]];
			$this->configArray['ajaxHandle'] = [$this, 'ajaxEditItemSave'];
		}
		$this->descriptionSrc = $descriptionSrc;
	}

	public function setPosition($positionSrc = 'position', $bMoveAble = true){
		if($bMoveAble){
			array_unshift($this->configArray['actions'], ListAction::MOVE_DOWN, ListAction::MOVE_UP);
		}
		$this->configArray['positionSrc'] = $this->relSaveModel . '.' . $positionSrc;
		$this->configArray['orderBy'] = $this->relSaveModel . '.' . $positionSrc;
		$this->positionSrc = $positionSrc;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param string $fraze
	 */
	public function createItemsList($info, $modul, $fraze = ' Obrázek (z souborů)'){
		if(!$info->scope->isEmptyRecId()){

			$laiu = ViewsFactory::createAjaxSelect(MODULES::ATTACHMENT, $info->scope->getLinkExt('pos', 'up'), $fraze . ' na začátek');
			$laid = ViewsFactory::createAjaxSelect(MODULES::ATTACHMENT, $info->scope->getLinkExt('pos', 'down'), $fraze . ' na konec');

			$List = $this->createListObj($info);

			$this->checkAddItem($info, $List);

			if($List->handleActions()){
				$this->parent->scope->resetViewWithRecByRedirect();
			}

			$modul->views->add($laiu);
			$modul->views->add($List);
			$modul->views->add($laid);
		}
	}

	/**
	 * @param ModuleInfoClass $info
	 */
	private function createListObj($info){
		$atchListModel = $this->createListItemModel($info->scope->recordId);

		$sub = new SubModule();
		$sub->createAsSub($info, 'e2e');

		$List = ViewsFactory::createModelList($sub->info);

		$conf = $this->configArray;
		$conf['model'] = $atchListModel;
		$conf['spcCols'] = $this->cols;
		$conf['fieldTplMap'] = $this->fieldTplMap;
		$conf['parentKey'] = $this->relSaveModel . '.' . $this->parentIdRow;

		$List->configByArray($conf);

// 		$List->setActionCallBacks([
// 			  ListAction::DELETE => [$this, 'deleteListItem']
// 		]);

		return $List;
	}

	private function createListItemModel($parentId){
		$atchListModel = new $this->relShowModel();
		$atchListModel->conditions[$this->parentIdRow] = $parentId;
		return $atchListModel;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $List
	 */
	function checkAddItem($info, $List){
		if(OBE_Http::notEmptyGet('selected') && $info->scope->recordId !== null){
			$pos = OBE_Http::getGetDef('pos', 'down');

			$ctrlList = $List->_createListControl();

			$atchRS = new $this->relSaveModel();

			$attchDesc = $this->getItemEntity(OBE_Http::getGet('selected'));

			$item[$atchRS->name] = [
				  $this->parentIdRow => $info->scope->recordId
				, $this->relEntityId => OBE_Http::getGet('selected')
			];

			if($this->descriptionSrc){
				$item[$atchRS->name][$this->descriptionSrc] = $attchDesc;
			}

			if($this->positionSrc){
				if($pos == 'down'){
					$item[$atchRS->name][$this->positionSrc] = $ctrlList->GetLastOrderValue($info->scope->recordId);
				}else{
					$ctrlList->ShakeDownPositions($info->scope->recordId, [], 1, null, 2);
					$item[$atchRS->name][$this->positionSrc] = 1;
				}
			}

			$atchRS->Save($item);

			$this->parent->scope->resetViewWithRecByRedirect();
		}
	}

	private function getItemEntity($entityId){
		$attchM = new MAttachment();
		$data = $attchM->FindOneById($entityId);
		return $data[$attchM->name][$this->descriptionSrc];
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @param ListClass $List
	 * @return boolean
	 */
	public function ajaxEditItemSave($field, $value, $List){
		$RelModel = new $this->relSaveModel();
		$RelModel->removeAssociateModels();

		if($item = $RelModel->FindOneById($List->info->scope->recordId)){
			$item[$RelModel->name][$field] = $value;
			try{
				$RelModel->Save($item);
			}catch(ModelSaveException $e){
				$e->log();
				throw new AjaxException($e->getMessage());
			}
		}
		return true;
	}
}