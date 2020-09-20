<?php

class TagDriverTag2Entity extends TagsFieldDriver{

	public $tagRelModel = 'MEntityRelTags';

	public $tagLinkModel = 'MEntityTagRel';

	public $relEntityKey = 'entityid';

	public $relTagIdKey = 'entitytagid';

	function __construct($fieldObj, $config = []){
		parent::__construct($fieldObj);
		MArray::extendObject($this, $config);
	}

	function addTagByName(&$tagName, $callBack = NULL, $continue = false){
		if($recordId = $this->getMasterRecId()){
			if($eTagId = parent::addTagByName($tagName)){
				if(!$this->isTagAdded($eTagId, $recordId)){
					$relObj = $this->getRelLink();
					$relData = [
						$relObj->name => [
							$this->relEntityKey => $recordId,
							$this->relTagIdKey => $eTagId
						]
					];
					$relObj->Save($relData);
					$relId = $relObj->id;

					if(!$continue){
						$this->field->tagsInterface->sendXmlItem($tagName, $relId);
					}

					if(is_callable($callBack)){
						call_user_func_array($callBack, [
							$this->field
						]);
					}

					if(!$continue){
						exit(0);
					}
				}
				if(!$continue){
					$this->field->tagsInterface->sendAllreadyXml();
				}
			}
		}
		if(!$continue){
			exit(0);
		}
	}

	function isTagAdded($tagId, $recordId = null){
		$relObj = $this->getRelLink();
		$num = $relObj->CountBy(NULL, NULL, [
			$this->relTagIdKey => $tagId,
			$this->relEntityKey => $recordId
		]);
		if($num > 0){
			return true;
		}
		return false;
	}

	function delTag($tag, $callBack = NULL){
		if($recordId = $this->getMasterRecId()){
			$tagId = $this->field->tagsInterface->checkNewTag($tag);

			$relObj = $this->getRelLink();
			$items = $relObj->FindAll([
				$this->relTagIdKey => $tagId,
				$this->relEntityKey => $recordId
			]);
			$ids = MArray::getKeyValsFromModels($items, $relObj->name, 'id');

			$relObj->Delete($ids);

			if(is_callable($callBack)){
				call_user_func_array($callBack, [
					$this->field
				]);
			}
		}

		exit(0);
	}

	function delAll(){
		if($list = $this->loadRelList()){
			$relObj = $this->getRelLink();
			$relObj->Delete([
				'id' => array_keys($list)
			]);
		}

		exit(0);
	}

	function loadRelList(){
		if($recordId = $this->getMasterRecId()){
			$relObj = $this->getRelModel();

			if($relItems = $relObj->findAll([
				$this->relEntityKey => $recordId
			], $this->field->tagsInterface->tagListFields)){
				$list = [];

				$tagModel = new $this->field->tagsInterface->tagModel();

				foreach($relItems as $item){
					$list[$item[$relObj->name][$relObj->primaryKey]] = $tagModel->getName($item);
				}
				return $list;
			}
		}
		return NULL;
	}

	function getIds(){
		if($recordId = $this->getMasterRecId()){
			$relObj = $this->getRelLink();
			if($relItems = $relObj->findAll([
				$this->relEntityKey => $recordId
			], $this->field->tagsInterface->tagListFields)){
				return MArray::getKeyValsFromModels($item, $relObj->name, $this->relTagIdKey);
			}
		}
		return NULL;
	}

	function setIds($tagIds){
		if($recordId = $this->getMasterRecId()){
			$relObj = $this->getRelLink();
			$relObj->Delete(NULL, [
				$relObj->name . '.' . $this->relEntityKey => relEntityKey
			]);
			if($tagIds = MArray::AllwaysArray($tagIds)){
				foreach($tagIds as $tagId){
					$relData[] = [
						$relObj->name => [
							$this->relEntityKey => $recordId,
							$this->relTagIdKey => $tagId
						]
					];
				}
				$relObj->Save($relData);
			}
		}
	}

	/**
	 * return ModelClass
	 */
	private function getRelModel(){
		return new $this->tagRelModel();
	}

	/**
	 * return ModelClass
	 */
	private function getRelLink(){
		return new $this->tagLinkModel();
	}
}