<?php

class FrontAttachment extends TypeAttachment{

	var $desc;

	var $bFDownload = false;

	function __construct(
		$item,
		$type = 'obe')
	{
		$this->id = $item['fileid'];
		$this->ctype = $type;
		if(isset($item['addinfo'])){
			$this->info = $item['addinfo'];
		}
		if(isset($item['description'])){
			$this->desc = $item['description'];
		}
		parent::__construct($item['filename']);
	}
}

class FrontAttachments extends OBE_AttachmentsClass{

	/**
	 *
	 * @param Mixed $ids - array/integer
	 * @param Array $resizeTo
	 * @param string $resizeType
	 * @param Mixed $exclude - array/integer
	 */
	function load(
		$images,
		array $resizeTo = NULL,
		$resizeType = OBE_Attachment::RESIZE_NORMAL,
		$exclude = [])
	{
		if($images = array_unique($this->toArray($images))){
			$sql = 'SELECT DISTINCT f.* FROM ' . OBE_App::$db->getTable('attachments') . ' AS f WHERE f.fileid IN (' . implode(', ', $images) . ')';
			$sql .= $this->exclude($exclude, ' AND f.fileid NOT');

			return $this->loadFromSQL($sql, $resizeTo, $resizeType);
		}
		return NULL;
	}

	/**
	 *
	 * @param Mixed $ids - array/integer
	 *        --- * @param Mixed $exclude - array/integer
	 * @param Array $resizeTo
	 * @param string $resizeType
	 */
	function loadOne(
		$images,
		$exclude = NULL,
		array $resizeTo = NULL,
		$resizeType = OBE_Attachment::RESIZE_NORMAL)
	{
		if($images = array_unique($this->toArray($images))){
			$sql = 'SELECT DISTINCT f.* FROM ' . OBE_App::$db->getTable('attachments') . ' AS f WHERE f.fileid IN (' . implode(', ', $images) . ')';
			$sql .= $this->exclude($exclude, ' AND f.fileid NOT');

			$imgs = $this->loadFromSQL($sql, $resizeTo, $resizeType);
			if(!empty($imgs)){
				return reset($imgs);
			}
		}
		return NULL;
	}

	function createAttachment(
		$item)
	{
		return new FrontAttachment($item);
	}
}

class FrontAttachmentsLoader extends FrontAttachments{

	var $resizeTo = NULL;

	var $resizeType = OBE_Attachment::RESIZE_NORMAL;

	function __construct(
		array $_resizeTo = NULL,
		$_resizeType = OBE_Attachment::RESIZE_NORMAL)
	{
		$this->resizeTo = $_resizeTo;
		$this->resizeType = $_resizeType;
	}

	function loadMod(
		&$source,
		$imageIdKey,
		$outKey)
	{
		$colectImgs = [];

		if(!empty($source) && is_array($source)){
			foreach($source as $pk => $si){
				if(!empty($si[$imageIdKey])){
					$colectImgs[$pk] = $si[$imageIdKey];
				}
			}
		}

		if(!empty($colectImgs)){
			$imgs = parent::load($colectImgs, NULL, $this->resizeTo, $this->resizeType);
			$imgs = MArray::MapObjectItemToKey($imgs, 'id');

			foreach($source as $pk => &$si){
				if(isset($imgs[$si[$imageIdKey]])){
					$si[$outKey] = $imgs[$si[$imageIdKey]];
				}
			}
		}
	}
}