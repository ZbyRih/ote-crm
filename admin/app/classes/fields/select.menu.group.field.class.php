<?php
class SelectMenuGroupFieldClass extends ModuleItemSelectFieldClass{

	/**
	 * @param array $arrayDefinition
	 * @param AppFormClass2 $parent
	 * @param string $infoConstruct
	 */
	function __construct($arrayDefinition = [], $parent = NULL, $infoConstruct = 'FormFieldInfo'){
		parent::__construct($arrayDefinition, $parent, $infoConstruct);

		$this->setSelect('Vybrat', MODULES::MENU);
		$this->setAdds([k_type => 'g']);
	}

	/**
	 * @param FormFieldClass $field
	 */
	function handleAccessPre(){
		if($val = $this->getValue()){
			$menus = OBE_AppCore::LoadVar('menu');
			if(isset($menus[$val])){
				$this->setItemLabel($menus[$val]);
			}else{
				$this->setItemLabel('Neplatné');
			}
		}
		parent::handleAccessPre();
	}

	/**
	 * @param FormFieldClass $field
	 */
	function handleAccessPost(){
	}
}