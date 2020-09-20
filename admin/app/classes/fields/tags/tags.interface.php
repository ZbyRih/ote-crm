<?php
class TagsInterface{

	public $tagModel = 'MEntityTag';
	public $tagNameRow = 'entitytagname';
	public $tagNameKey = 'entitytagname';
	public $tagNameId = 'entitytagid';
	public $tagListFields = ['EntityTag' => ['entitytagid', 'entitytagname']];

	function __construct($config = []){
		MArray::extendObject($this, $config);

		$eTagObj = new $this->tagModel();
		$this->tagNameRow = $eTagObj->name . '.' . $this->tagNameRow;
	}

	function checkNewTag(&$tagName){
		$eTagId = NULL;
		if(mb_strlen($tagName) > 0){
			$eTagObj = new $this->tagModel();
			if($tags = $eTagObj->FindAll(['LOWER(' . $this->tagNameRow . ')' => $tagName], [], [$this->tagNameRow])){
				$first = reset($tags);
				$tagName = $first[$eTagObj->name][$this->tagNameKey];
				$eTagId = $first[$eTagObj->name][$eTagObj->primaryKey];
			}else{
				$eNewTag = [$eTagObj->name => [$this->tagNameKey => $tagName]];
				$eTagObj->Save($eNewTag);
				$eTagId = $eTagObj->id;
			}
		}
		return $eTagId;
	}

	function getTagList($tagsIds){
		if(!empty($tagsIds)){
			$eTagObj = new $this->tagModel();
			if($items = $eTagObj->findAll([$eTagObj->primaryKey => $tagsIds])){
				$tag2ids = array_flip($tagsIds);
				$list = [];
				foreach($items as $item){
					$list[$tag2ids[$item[$eTagObj->name][$eTagObj->primaryKey]]] = $eTagObj->getName($item);
				}
				return $list;
			}
		}
		return NULL;
	}

	function getTagId($fraze){
		if(mb_strlen($fraze) > 0){
			$eTagObj = new $this->tagModel();
			$list = NULL;
			if($result = $eTagObj->FindOne(['!LOWER(' . $this->tagNameRow . ') LIKE \'' . mb_strtolower($fraze) . '%\''], $this->tagListFields, [$this->tagNameRow])){
				return $result[$eTagObj->name][$eTagObj->primaryKey];
			}
		}
	}

	function getTagListByName($tags){
		if(!empty($tags)){
			$eTagObj = new $this->tagModel();
			if($items = $eTagObj->FindAll([$this->tagNameKey => $tags], [], [$this->tagNameRow])){
				return MArray::MapValToKeyFromMArray($items, $eTagObj->name, $eTagObj->primaryKey, $this->tagNameKey);
			}
		}
	}

	function findTag($fraze){
		if(mb_strlen($fraze) > 0){
			$eTagObj = new $this->tagModel();
			$list = NULL;
			if($result = $eTagObj->FindAll(['!LOWER(' . $this->tagNameRow . ') LIKE \'' . mb_strtolower($fraze) . '%\''], $this->tagListFields, [$this->tagNameRow])){
				$list = self::makeAssocList($result);
			}
			$this->sendXmlItems($list);
		}
		exit(0);
	}

	function findAll(){
		$eTagObj = new $this->tagModel();
		$eTagObj->addAssociatedModels([
			'MEntityTagRel' => [
				  'type' => 'belongsTo'
 				, 'associationForeignKey' => 'entitytagid'
				, 'foreignKey' => 'entitytagid'
				, 'associatedModels' => [
					'MEntity' => [
						  'type' => 'belongsTo'
						, 'foreignKey' => 'entityid'
					]
				]
			]
		]);

		$eTagObj->group = ['EntityTag.entitytagid'];

		$list = NULL;

		if($result = $eTagObj->FindAll(['MEntity.moduleid' => OBE_Http::getNumGet(k_module)], $this->tagListFields, [$this->tagNameRow])){
			$list = self::makeAssocList($result);
		}
		$this->sendXmlItems($list);
		exit(0);
	}

	function sendXmlItems($items = NULL){
		$xmlObj = new OBE_XmlWriterClass();

		$xmlObj->push('list');
		if(!empty($items)){
			foreach($items as $id => $name){
				$xmlObj->element('item', $name, ['id' => $id]);
			}
		}
		$xmlObj->pop();

		$xmlObj->HeadInitialFileWMimeType();
		OBE_Log::log('send xml items');
	}

	function sendXmlItem($itemName, $id){
		$xmlObj = new OBE_XmlWriterClass();

		$xmlObj->push('item');
		$xmlObj->element('name', $itemName);
		$xmlObj->element('id', $id);
		$xmlObj->pop();

		$xmlObj->HeadInitialFileWMimeType();
		OBE_Log::log('send xml item');
	}

	function sendAllReadyXml(){
		$this->sendXmlMessage('Již je v seznamu');
	}

	function sendXmlMessage($message){
		$xmlObj = new OBE_XmlWriterClass();
		$xmlObj->push('message');
		$xmlObj->element('text', $message);
		$xmlObj->pop();
		$xmlObj->HeadInitialFileWMimeType();
		OBE_Log::log('send xml message ' . $message);
	}

	public static function makeAssocList($items, $keyId = 'entitytagid', $modelName = 'EntityTag', $keyName = 'entitytagname'){
		return MArray::MapValToKeyFromMArray($items, $modelName, $keyId, $keyName);
	}
}