<?php
class AttachmentFieldClass extends ModuleItemSelectFieldClass{ //FormFieldClass{

	/**
	 * odchytava type pro zobrazeni prilozeneho obrazku
	 *
	 * @param Array $field
	 * @param ModelFormClass2 $form
	 */
	function handleAccessPre(){
		AdminApp::PageForceReload();
		parent::handleAccessPre();
		$this->data['moduleId'] = MODULES::ATTACHMENT;
		if(isset($this->data['value'])){
			$this->data['img'] = AttachmentCtrlClass2::$self->getView($this->data['value'], OBE_AppCore::getAppConfDef('img-preview', ['form' => [200, 200]])['form']);
		}
	}

	/**
	 * odchytava type pro ulozeni prilozeneho obrazku
	 *
	 * @param FormFieldClass $field
	 */
	function handleAccessPost(){
		if(empty($this->data['value'])){
			$this->data['value'] = null;
		}
	}
}