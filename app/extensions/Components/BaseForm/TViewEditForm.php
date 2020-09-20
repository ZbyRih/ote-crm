<?php
namespace App\Extensions\Components;

trait TViewEditForm {
	use TBaseFormComponent;

	/** @var bool @persistent */
	public $edit;

	/**
	 *
	 * @return BaseForm
	 */
	abstract function getForm();

	public function setEdit($edit){
		$this->edit = $edit;
		return $this;
	}

	public function isEdit(){
		return $this->edit;
	}

	public function handleEdit(){
		$this->edit = true;
	}

	public function render(){
		$this->template->edit = true;
		if(!$this->isAllowed('edit')){
			$this->edit = false;
			$this->template->edit = false;
		}
		$this->template->view = !$this->edit;

		if($this->edit){
			parent::render();
			return;
		}

		$f = $this->getForm();
		$name = $f->getName();

		$fdl = (new FormDataList($f));

		$this->removeComponent($f);
		$this->addComponent($fdl, $name);
		parent::render();
	}
}