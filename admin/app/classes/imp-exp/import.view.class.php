<?php
class ImportViewElement extends ViewElementClass{
	var $fileFieldKey = NULL;
	/**
	 *
	 * @var ImportClass
	 */
	var $importObj = NULL;
	var $label = NULL;
	var $submitLabel = NULL;
	var $bSubmit = false;


	public function __construct($type = NULL){
		parent::__construct('import');
	}
	/**
	 *
	 * @param String $fileFieldKey - name input $_FILES
	 * @param ImportConfigClass $importConfig - konfigurace importu
	 */
	public function init($fileFieldKey, $importConfig){
		$this->fileFieldKey = $fileFieldKey;
		$this->importObj = new ImportClass($importConfig);
	}

	function handleImport($label, $submitLabel){
		$this->label = $label;
		$this->submitLabel = $submitLabel;
		if(!empty($_FILES[$this->fileFieldKey])){

			$this->bSubmit = true;
			if($this->importObj->parseCSVFile($_FILES[$this->fileFieldKey]['tmp_name'])){
				return true;
			}else{
				return false;
			}
		}
	}

	function setReadLineCallBack($callBack){
		$this->importObj->readCallBack = $callBack;
	}

	function setWriteCallBack($callBack){
		$this->importObj->writeCallBack = $callBack;
	}

	function setReadUserValidCallBack($callBack){
		$this->importObj->readUserValidCallBack = $callBack;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		$this->data = [
			  'fieldKey' => $this->fileFieldKey
			, 'label' => $this->label
			, 'action_label' => $this->submitLabel
			, 'bReport' => $this->bSubmit
			, 'report' => [
				  'errors' => $this->importObj->errors_on_line
				, 'message' => $this->importObj->error_buffer
				, 'loadedLines' => $this->importObj->report->loadedLines
				, 'processLines' => $this->importObj->report->processLines
				, 'insertNums' => $this->importObj->report->insertNums
				, 'updateNums' => $this->importObj->report->updateNums - $this->importObj->report->insertNums
				, 'fullNums' => $this->importObj->report->fullNums
			]
		];
		return $this;
	}
}