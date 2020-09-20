<?php

class TypeAttachment extends OBE_AttachmentThumbnail{
	var $ctype = 'obe';

	function getFullSrcPath(){
		return $this->getTypeConf('srcPath') . $this->id . '_' . $this->file;
	}

	function getHttp(){
		return OBE_Core::getConfEnvVar('url') . $this->getTypeConf('srcPath') . $this->id . '_' . $this->file;
	}

	function getThumbFileName(){
		return $this->getTypeConf('thumbnailsPath') . $this->id . '_M' . ceil($this->size[SWIDTH]) . ceil($this->size[SHEIGHT]) . $this->name . '.jpg';
	}

	public function getTypeConf($confKey){
		return EnviromentConfig::$global[$this->ctype][$confKey];
	}
}

class OBE_AttachmentsClass{
	var $images = NULL;

	function load($images, array $resizeTo = NULL, $resizeType = OBE_Attachment::RESIZE_NORMAL, $exclude = []){
		$this->images = [];
		foreach($images as $key => $image){
			$img = $this->createAttachment($image);
			$img->createInfo($resizeTo, $resizeType);

			if($resizeTo !== NULL){
				if($img->createThumbNail($resizeType)){
					$this->images[$key] = $img;
				}
			}else{
				$this->images[$key] = $img;
			}
		}
		return $this->images;
	}

	function loadFromSQL($sql, array $resizeTo = NULL, $resizeType = OBE_Attachment::RESIZE_NORMAL, $assocKey = 'fileid'){
		if($images = OBE_App::$db->FetchAssoc($sql, $assocKey)){
			return self::load($images, $resizeTo, $resizeType);
		}
		return NULL;
	}

	function createAttachment($item){
		return new OBE_AttachmentThumbnail($item);
	}

	function exclude($exclude = NULL, $sql = ''){
		if($exclude !== NULL){
			if(!is_array($exclude)){
				$exclude = [$exclude];
				return $sql . ' IN (' . implode(', ', $exclude) . ')';
			}
		}
		return '';
	}

	function toArray($ids){
		if(is_array($ids)){
			return $ids;
		}elseif($ids !== NULL){
			return [$ids];
		}
		return NULL;
	}
}