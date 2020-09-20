<?php
class UploadFieldClass extends FormFieldClass{

	public $upload = false;

	private $db = true;

	public function __construct($arrayDefinition = [], $parent = NULL, $infoConstruct = 'FormFieldInfo'){
		parent::__construct($arrayDefinition, $parent, $infoConstruct);
	}

	/**
	 * {@inheritDoc}
	 * @see FormFieldClass::handleAccessPre()
	 */
	function handleAccessPre(){
// 		OBE_Log::logCT('handleAccessPre');
		if($this->data['value'] && !is_array($this->data['value'])){
			if($this->db){
				$this->data['img'] = AttachmentCtrlClass2::$self->getView($this->data['value'], OBE_AppCore::getAppConfDef('img-preview', ['form' => [200, 200]])['form']);
			}
		}
		$this->data = AttachmentCtrlClass2::$self->append($this->data);
	}

	/**
	 * {@inheritDoc}
	 * @see FormFieldClass::handleAccessPost()
	 */
	function handleAccessPost(){
// 		OBE_Trace::callPoint('handleAccessPost', 1, 3);
// 		__dump($this->data);
// 		__dump(($this->data['value'] && is_array($this->data['value']))? 'true' : 'false');

// 		OBE_Log::logCT('handleAccessPost');

		if($this->data['value'] && is_array($this->data['value'])){
			$this->data['file'] = $file = OBE_File::sanitizeUpload($this->data['value']);
			if($this->db){
				$this->data['value'] = AttachmentCtrlClass2::$self->uploadFile($file);
			}else{
				$this->data['value'] = $file;
			}
		}
	}

	public function setValue($value){
// 		OBE_Trace::callPoint('setValue', 1, 3);
// 		__dump('set value');
// 		__dump($this->key);
// 		__dump($value);
		return parent::setValue($value);
	}

	public function setDB(){
		$this->db = true;
		return $this;
	}

	public function setMan(){
		$this->db = false;
		return $this;
	}

	public function getFile(){
		if(isset($this->data['value']['tmp_name'])){
			return $this->data['value']['tmp_name'];
		}
		return null;
	}

	function setUpload($upload = true){
		$this->upload = $upload;
		return $this;
	}

	public function createFileInfo($fullPathFile){
		$this->data['file_info'] = OBE_File::getInfo($fullPathFile);
		return $this;
	}

	public function getView(){
		return parent::getView() + ['upload' => $this->upload];
	}
}