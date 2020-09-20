<?php

class TagsFieldDriver{

	/**
	 *
	 * @var TagsListFieldClass
	 */
	public $field = NULL;

	/**
	 *
	 * @param TagsListFieldClass $field
	 */
	function __construct($field){
		$this->field = $field;
	}

	function addTagByName(&$tagName, $callBack = NULL){
		if($tagId = $this->field->tagsInterface->checkNewTag($tagName)){
			return $tagId;
		}
		return NULL;
	}

	function isTagAdded($tagId, $recordId = null){

	}

	function addTagId($tagId){

	}

	function delTag($relTagId, $callBack = NULL){

	}

	function delAll(){

	}

	function loadRelList(){

	}

	function getIds(){

	}

	function setIds($tagIds){

	}

	function saveValue(){

	}

	public function getMasterRecId(){
		if($this->field->parent->scope->isRecId()){
			return $this->field->parent->scope->recordId;
		}
		OBE_Log::log('TagsFieldDriver - ' . $this->field->key . ' nemá v parent->scope recId');
		return null;
	}
}