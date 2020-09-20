<?php
class EntityTagCtrl{

	const AJAX_FIELD_ID = 'ffield';

	var $ajax2fce = [
		'ajaxAddTag' => 'ajaxAddTag',
		'ajaxDelTag' => 'ajaxDelTag',
		'ajaxFindTag' => 'ajaxFindTag',
		'ajaxFindAll' => 'ajaxFindAll',
		'ajaxDelAll' => 'ajaxDelAll'
	];

	var $config = [];

	var $fieldKey = NULL;

	/**
	 *
	 * @var AppFormClass2
	 */
	var $form = null;

	function __construct(
		$fieldKey,
		$fieldTitle,
		$config = [])
	{
		$this->fieldKey = $fieldKey;
		$this->config = $config;
	}

	/**
	 *
	 * @param Array $data
	 * @param ModelFormClass2 $editFormObj
	 */
	function initTagsAfterInsertEntity(
		$data,
		$formObj)
	{
		if($tagField = $formObj->getField($this->fieldKey)){
			if($ids = $tagField->getForceTags()){
				$tagDBt2eDriver = new TagDriverTag2Entity($tagField);
				$tagDBt2eDriver->setIds($ids, $formObj->recordId);
			}else{
				OBE_Log::log('zadne tagy !');
			}
		}
	}

	/**
	 *
	 * @param ModelClass $model
	 * @param FormFieldClass $fieldObj
	 * @param [] $tags
	 * @param ModelListFilterItemClass $prevField
	 */
	function addFilterModel(
		$model,
		$fieldObj,
		$tags,
		$prevField)
	{
		if(!empty($tags)){

			if($prevField->type == 'x' && $prevField->getValue() == 1){
				$cond = [
					'!EntityTag.entitytagname NOT IN(\'' . implode('\',\'', $tags) . '\')'
				];
			}else{
				$cond = [
					'EntityTag.entitytagname' => $tags
				];
			}

			$model->associatedModels[$this->config['tagRelModel']] = [
				'type' => 'belongsTo',
// 				, 'foreignKey' => $this->config['relEntityKey']
				'associationForeignKey' => $this->config['relEntityKey'],
				'conditions' => [
					'EntityTag.entitytagname' => $tags
				]
			];
		}
	}

	function catchAjax(
		$field,
		$callback = null)
	{
		$ajaxRequest = null;
		if(OBE_Http::issetGet(k_ajax_ex)){
			$ajaxRequest = OBE_Http::getGet(k_ajax_ex);
		}else if(OBE_Http::issetGet(k_ajax)){
			$ajaxRequest = OBE_Http::getGet(k_ajax);
		}
		if($ajaxRequest){
			if(OBE_Http::issetGet(self::AJAX_FIELD_ID)){
				if($field->key != OBE_Http::getGet(self::AJAX_FIELD_ID)){
					return;
				}
			}

			if(isset($this->ajax2fce[$ajaxRequest])){
				call_user_func([
					$field,
					$this->ajax2fce[$ajaxRequest]
				], $field, $callback);
			}
		}
	}
}