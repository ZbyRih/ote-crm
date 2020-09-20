<?php
class TagDriverNone extends TagsFieldDriver{
	/**
	 *
	 * @param TagsListFieldClass $fieldObj
	 * @param $config
	 */
	function __construct($fieldObj, $config = []){
		parent::__construct($fieldObj);
	}

	function addTagByName(&$tagName, $callBack = NULL){
		exit(0);
	}

	function delTag($relTagId, $callBack = NULL){
		exit(0);
	}

	function delAll(){
		exit(0);
	}

	function loadRelList(){
		$tagNames = $this->field->getValue();
		if(!empty($tagNames) && is_array($tagNames)){
			return $this->field->tagsInterface->getTagListByName($tagNames);
		}
		return NULL;
	}

	function getIds(){
		return $this->field->getValue();
	}

	function setIds($tagIds){
		$this->field->setValue($tagIds);
	}
}