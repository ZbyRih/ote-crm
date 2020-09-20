<?php
class ExportViewElement extends ViewElementClass{
	var $label = NULL;
	var $submitLabel = NULL;

	/**
	 *
	 * @var AppFormClass2
	 */
	public $formObj = NULL;
	public $writerObj = NULL;

	public function __construct($type = NULL){
		parent::__construct('export');
	}

	public function init($fileExpName, $formObj, $exporter){
		$this->formObj = $formObj;
		$this->writerObj = $exporter;
		$this->handleForm($fileExpName);
	}

	public function setLabels($label, $submitLabel = 'Export'){
		$this->label = $label;
		$this->submitLabel = $submitLabel;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		$this->data = [
			  'form' => $this->formObj->getForm()
			, 'label' => $this->label
		];
		return $this;
	}

	public function handleForm($fileExpName){
		$fieldObj = $this->formObj->createField('codepage', FormUITypes::DROP_DOWN, 'WINDOWS-1250', 'Kódování');
		$fieldObj->setList(['UTF-8' => 'UTF-8', 'WINDOWS-1250' => 'Windows 1250']);
		$this->formObj->addFieldToForm($fieldObj);

		$handleRet = $this->formObj->handleFormSubmit();

		if($handleRet !== false && $handleRet !== NULL){
			$rawSql = $this->handleFormSend($this->formObj);
			$this->writerObj->setCodePage($fieldObj->getValue());
			$this->doExport($rawSql, $fileExpName);
		}
	}

	public function handleFormSend($formObj){
		return NULL;
	}

	private function doExport($rawSql, $fileExpName){
		$exportObj = new ExportClass($rawSql, $this->writerObj, [$this, 'handleResultLine']);
		$exportObj->export();
		$exportObj->flush($fileExpName);
		exit;
	}

	public function handleResultLine($item){
		return $item;
	}
}