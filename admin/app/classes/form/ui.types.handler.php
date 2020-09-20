<?php

class FromUITypesHandlersClass{

	static $defaultClass = 'FormFieldClass';

	static $classMap = [
		FormUITypes::DROP_DOWN => 'ListFieldClass',
		FormUITypes::MULTI_CHECKBOX => 'ListFieldClass',
		FormUITypes::SELF_SEND_DROPDOWN => 'ListFieldClass',
		FormUITypes::SELECT_ATTCH => 'AttachmentFieldClass',
		FormUITypes::CHECKBOX => 'CheckboxFieldClass',
		FormUITypes::PASSWD => 'PasswordFieldClass',
		FormUITypes::UPLOAD => 'UploadFieldClass',
		FormUITypes::MULTI_UPLOAD => 'MultiUploadClass',
		FormUITypes::TEXT => 'TextFieldClass',
// 		, FormUITypes::SELECT_MENU => 'SelectMenuFieldClass'
		FormUITypes::SELECT_MENU_GROUP => 'SelectMenuGroupFieldClass',
		FormUITypes::TAGS => 'TagsListFieldClass',
		FormUITypes::SELECT_CONT => 'SelectContentFieldClass',
		FormUITypes::RADIO_LIST => 'ListFieldClass',
		FormUITypes::MODULE_ITEM_SELECT => 'ModuleItemSelectFieldClass',
		FormUITypes::MULTI_LIST_PICK => 'MultiListPickFieldClass'
	];

	static function getClass(
		$uiType)
	{
		if(isset(self::$classMap[$uiType])){
			return self::$classMap[$uiType];
		}else{
			return self::$defaultClass;
		}
	}
}
