<?php

class ModulTags extends AppModuleClass{
	var $modelName = 'MEntityTag';

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			  'type' => 'link'
			, 'name' => 'Seznam'
		  	, 'icon' => 'md md-list'
		]
		, 'mrak' => [
			  'type' => 'link'
			, 'name' => 'Mrak'
			, 'callback' => 'cloudView'
		  	, 'icon' => 'md md-cloud'
		]
	];

	function __listModuleItems($info){
		$tagsObj = new MEntityTag();
		$tagsObj->addAssociatedModels([
			'MEntityTagRel' => [
				  'type' => 'hasOne'
				, 'foreignKey' => 'entitytagid'
			]
		]);
		$tagsObj->group = ['EntityTag.entitytagid'];

		$listTags = ViewsFactory::createModelList($info);
		$listTags->configByArray([
			  'actions' => [ListAction::EDIT, ListAction::DELETE]
			, 'model' => $tagsObj
			, 'spcCols' => [
				  'EntityTag' => [
				  	  'entitytagname' => 'Název'
					, '(SELECT COUNT(*) FROM obe_entity_tags_2_entity AS oet2e , obe_entitys AS oe WHERE oet2e.entitytagid = EntityTag.`entitytagid` AND oe.entityid = oet2e.entityid AND oe.moduleid = 3) AS in_doc' => 'v Dokumentech'
					, '(SELECT COUNT(*) FROM obe_entity_tags_2_entity AS oet2e , obe_entitys AS oe WHERE oet2e.entitytagid = EntityTag.`entitytagid` AND oe.entityid = oet2e.entityid AND oe.moduleid = 8) AS in_atch' => 'v Souborech'
					, '(SELECT COUNT(*) FROM es_entity_tags_2_product AS eet2p WHERE eet2p.entitytagid = EntityTag.`entitytagid`) AS in_prods' => 'v Produktech'
				]
			]
			, 'orderBy' => ['COUNT(entitytagid)', 'EntityTag.entitytagname']
			, 'pagination' => true
			, 'sort' => ['EntityTag.entitytagname']
			, 'filter' => [
				['type' => 'like', 'fields' => ['EntityTag.entitytagname'], 'name' => 'název']
			]
			, 'ajaxRowsEdit' => ['EntityTag' => ['entitytagname' => FormUITypes::TEXT]]
			, 'ajaxHandle' => [$this, 'rename']
		]);

		$listTags->setActionCallBacks([$this, '__editModuleItem'], ListAction::EDIT);

		if($listTags->handleActions()){
			$this->scope->resetViewByRedirect();
		}

		$this->views->add($listTags);

		return true;
	}

	function cloudView(){
		$tagsObj = new MEntityTag();
		$tagsObj->rows[] = 'COUNT(EntityTagRel.entitytagid) AS tcount';
		$tagsObj->addAssociatedModels([
			'MEntityTagRel' => [
				  'type' => 'hasOne'
				, 'foreignKey' => 'entitytagid'
			]
		]);
		$tagsObj->order['`tcount`'] = 'DESC';
		$tagsObj->order[] = 'EntityTag.entitytagname';
		$tagsObj->group = ['EntityTag.entitytagid'];

		$cloudObj = ViewsFactory::createCloud($tagsObj, 'EntityTagRel.tcount', 'EntityTag.entitytagname');

		$cloudObj->handleAction([ListAction::DELETE => [$this, 'deleteItem']], $this);

		$this->views->add($cloudObj);
		return true;
	}

	function deleteItem($itemId){
		$tagsObj = new MEntityTag();
		$tagsObj->Delete($itemId);
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see ModuleViewClass::__editModuleItem()
	 */
	function __editModuleItem($info){
		parent::__editModuleItem($info);

		if(!$info->scope->isEmptyRecId()){
			$docModel = $this->getBaseModel();

			$doc = $docModel->FindOneById($info->scope->recordId);

			$docEditForm  = ViewsFactory::createForm($this->scope);
			$field = $docEditForm->createField('tagname', FormUITypes::TEXT, $doc['EntityTag']['entitytagname'], 'Název štítku', true);

			if($docEditForm->handleFormSubmit()){
				$data = $docEditForm->getData();
				$doc['EntityTag']['entitytagname'] = $data['tagname'];
				$docModel->Save($doc);

				$this->scope->resetViewByRedirect($info->scope->recordId);
			}

			$this->views->add($docEditForm);
		}
		return true;
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @param ListClass $List
	 * @return boolean
	 */
	function ajaxEdit($field, $value, $List){
		$Model = $this->getBaseModel();
		$data = $Model->FindOneById($List->scope->recordId);
		$data[$Model->name]['entitytagname'] = trim($value);
		$Model->Save($data);
		return true;
	}
}