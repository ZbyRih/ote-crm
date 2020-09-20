<?php
class TagDriverSession extends TagsFieldDriver{
	public $sesKey = NULL;

	/**
	 *
	 * @param TagsListFieldClass $fieldObj
	 * @param $config
	 */
	function __construct($fieldObj, $config = []){
		parent::__construct($fieldObj);
		MArray::extendObject($this, $config);

		$this->sesKey = $fieldObj->key;

		if(($ids = OBE_Session::read($this->sesKey)) !== false){
			$this->field->setValue($ids);
		}
	}

	function addTagByName(&$tagName, $callBack = NULL){
		if($eTagId = parent::addTagByName($tagName)){
			if(!$this->isTagAdded($eTagId)){
				$relId = $this->addTagId($eTagId);
				$this->field->tagsInterface->sendXmlItem($tagName, $relId);

				if(is_callable($callBack)){
					call_user_func_array($callBack, [$this->field]);
				}

				exit;
			}
			$this->field->tagsInterface->sendAllReadyXml();
		}
		exit;
	}

	function isTagAdded($tagId, $recordId = null){
		if($tagIds = $this->field->getValue()){
			if(is_array($tagIds)){
				$tagIds = array_flip($tagIds);
				if(isset($tagIds[$tagId])){
					return true;
				}
			}
		}
		return false;
	}

	function addTagId($tagId){
		$tagIds = $this->field->getValue();
		if(!is_array($tagIds)){
			$tagIds = [];
		}
		$tagIds[] = $tagId;

		$this->field->setValue($tagIds);

		$this->saveValue();

		return $tagId;
	}

	function delTag($tagId, $callBack = NULL){
		$tagIds = $this->field->getValue();
		if(!is_array($tagIds)){
			if(($key = array_search($tagId, $tagIds)) !== false){
				unset($tagIds[$key]);
			}
			$this->field->setValue($tagIds);
		}
		$this->saveValue();

		if(is_callable($callBack)){
			call_user_func_array($callBack, [$this->field]);
		}

		exit(0);
	}

	function delAll(){
		$tagIds = [];
		$this->field->setValue($tagIds);
		$this->saveValue();

		exit(0);
	}

	function loadRelList(){
		$tagIds = $this->field->getValue();
		if(!empty($tagIds) && is_array($tagIds)){
			return $this->field->tagsInterface->getTagList(array_values($tagIds));
		}
		return NULL;
	}

	function getIds(){
		return OBE_Session::read($this->sesKey);
	}

	function setIds($tagIds){
		$this->field->setValue($tagIds);
		OBE_Session::write($this->sesKey, $tagIds);
	}

	function saveValue(){
		OBE_Log::log('save value ' . $this->sesKey);
		OBE_Log::varDump($this->field->getValue());

		OBE_Session::write($this->sesKey, $this->field->getValue());
		AdminUserClass::saveSessionToDB();
	}
}