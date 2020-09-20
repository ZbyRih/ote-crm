<?php

class FormFieldRights{
	const DISABLE = 0;
	const VIEW = 1;
	const EDIT = 2;
	const DELETE = 3;
}

class FormUITypes{

	const TEXT = 1;
	const UPLOAD = 2;
	const LINK = 3;
	const BUTTON = 4;
	const HIDDEN = 5;
	const FCK_EDITOR = 6;
	const SELECT_ATTCH = 7;
	const DROP_DOWN = 8;
	const SELECT_CONT = 9;
	const CHECKBOX = 10;
	const DATE = 11;
	const TEXT_AREA = 12;
	const SELECT_MENU = 13;
	const SELECT_MENU_GROUP = 14;
	const COLOR = 15;
	const SELF_SEND_DROPDOWN = 16;
	const MULTI_LIST_PICK = 17;
	const PASSWD = 18;
	const MULTI_CHECKBOX = 19;
	const TAGS = 21;
	const RADIO_LIST = 22;
	const CK_TINY = 23;
	const MODULE_ITEM_SELECT = 24;
	const SEPARATOR = 25;
	const MULTI_UPLOAD = 26;
	const EMAIL = 27;

	const TPL_FOR_FORM = 'f';
	const TPL_FOR_LIST = 'l';
	const TPL_FOR_AJAX = 'a';

	static $fieldsDir = 'fields/';
	static $fieldSufix = '.field.tpl';

	static $FormUITypesValidate = [
		  self::TEXT => []
		, self::UPLOAD => [
			  OBE_FieldValid::FILE => [
				  'message' => 'Soubor nebyl nahrán'
			  ]
		]
		, self::MULTI_UPLOAD => [
			  OBE_FieldValid::FILE => [
				  'message' => 'Soubor nebyl nahrán'
			  ]
		]
		, self::LINK => [
			  OBE_FieldValid::LINK => ['message' => 'Neplatný link']
		]
		, self::CHECKBOX => [OBE_FieldValid::VARIABLEBOOL => ['message' => 'Variable bool']]
		, self::DATE => [OBE_FieldValid::DATE => ['message' => 'Neplatné datum']]
	];

	static $rightsPrefix = [
		  FormFieldRights::DISABLE => ''
		, FormFieldRights::VIEW => 'v_'
		, FormFieldRights::EDIT => ''
		, FormFieldRights::DELETE => ''
	];

	static $UITplsByRights = [
		  FormFieldRights::DISABLE => ''
		, FormFieldRights::VIEW => 'FormUITypesTPLSView'
		, FormFieldRights::EDIT => 'FormUITypesTPLS'
		, FormFieldRights::DELETE => 'FormUITypesTPLS'
	];

	static $FormUITypesTPLSView = [
		  self::TEXT => 'pasive.text'
		, self::LINK => 'pasive.text'
		, self::DROP_DOWN => 'pasive.dropdown'
		, self::RADIO_LIST => 'pasive.radiolist'
		, self::CHECKBOX => 'pasive.checkbox'
	];

	static $FormUITypesTPLS = [
		  self::TEXT => 'text' /* obycejnej text s ID */
		, self::LINK => 'text' /* text bez ID */
		, self::PASSWD => 'password'
		, self::BUTTON => 'button' /* tlacitko */
		, self::DROP_DOWN => 'drop.down' /* dropdown menu */
		, self::SELECT_CONT => 'select.content' /* vybrat obsah */
		, self::CHECKBOX => 'checkbox' /* check box */
		, self::TEXT_AREA => 'text.area'

		, self::UPLOAD => 'file.upload' /* file upload */
		, self::MULTI_UPLOAD => 'multi.upload'
		, self::SELECT_ATTCH => 'select.attachment' /* vyber obrazku */

		, self::COLOR => 'color.picker'
		, self::DATE => 'date' /* kalendar */
		, self::FCK_EDITOR => 'fckeditor' /* fckeditor */
		, self::SELECT_MENU => 'module.item.select'//'select.menu'
		, self::SELECT_MENU_GROUP => 'module.item.select' //'select.menu_group'
		, self::SELF_SEND_DROPDOWN => 'self.send.select' /* sebe odesilajici dropdown*/
		, self::MULTI_LIST_PICK => 'multi.list.pick'
		, self::MULTI_CHECKBOX => 'multi.checkbox'
		, self::TAGS => 'tags'
		, self::RADIO_LIST => 'radiolist'
		, self::HIDDEN => 'hidden' /* hidden input */
		, self::CK_TINY => 'fckeditor.tiny'
		, self::MODULE_ITEM_SELECT => 'module.item.select'
		, self::SEPARATOR => 'separator'
		, self::EMAIL => 'email'
	];

	static $names = [
		  self::TEXT => 'Text na řádce'

		, self::UPLOAD => 'Upload souboru'
		, self::MULTI_UPLOAD => 'Hromadný upload souboru'
		, self::LINK => 'Link'
		, self::BUTTON => 'Tlacitko'
		, self::HIDDEN => 'Hidden input'
		, self::FCK_EDITOR => 'WYSiWYG editor'
		, self::SELECT_ATTCH => 'Výběr obrazku'
		, self::DROP_DOWN => 'Dropdown list'
		, self::SELECT_CONT => 'Výběr obsahu'
		, self::CHECKBOX => 'Zaškrtávátko'
		, self::DATE => 'Kalendář'
		, self::TEXT_AREA => 'Text-area'
		, self::SELECT_MENU => 'Výběr menu'
		, self::SELECT_MENU_GROUP => 'Výběr skupiny menu'
		, self::COLOR => 'Výběr barvy'
		, self::SELF_SEND_DROPDOWN => 'Dropdown sebe odesilajici'
		, self::MULTI_LIST_PICK => 'Ajax multi list with select'
		, self::PASSWD => 'Zadaní nového hesla'
		, self::MULTI_CHECKBOX => 'Sada zaškrtávátek'
		, self::TAGS => 'Seznam štítků'
		, self::RADIO_LIST => 'RadioButton Group'
		, self::CK_TINY => 'WYSIWYG zmenseny'
		, self::MODULE_ITEM_SELECT => 'Výběr položky modulu'
		, self::SEPARATOR => 'Oddělovač s popiskem'
		, self::EMAIL => 'Poslat email'
	];

	static $dbTypes = [
		  '0' => 'Normální pole'
		, '1' => 'Když prázdné tak NULL'
		, '2' => 'Korekce float čísla'
	];

	/**
	 * Sablony pro pole listu vyberu pres ajax
	 * @var Array
	 */
	static $ListAjaxUITypesTPLS = [
		  self::SELECT_CONT => 'select.content' /* vybrat obsah */
		, self::SELECT_MENU => 'select.menu'
		, self::SELECT_MENU_GROUP => 'select.menu_group'
	];

	static $ListUITypesTPLS = [
		  self::SELECT_ATTCH => 'img.preview' /* vyber obrazku */
		, self::SELECT_CONT => 'select.content' /* vyber obsahu */
		, self::TAGS => 'tags.l'
		, self::EMAIL => 'email.l'
	];

	/**
	 * list pro sitemap.xml, frekvence zmeny obsahu
	 * @var unknown_type
	 */
// 	static $smChangeFrequency = [
// 		  0 => 'nikdy'
// 		, 1 => 'pokaždé'
// 		, 2 => 'hodinově'
// 		, 3 => 'denně'
// 		, 4 => 'týdně'
// 		, 5 => 'měsíčně'
// 		, 6 => 'ročně'
// 	];

// 	/**
// 	 * list pro sitemap.xml, priorita stranky
// 	 * @var array
// 	 */
// 	static $smPriority = [
// 		0 => 0, '0.1' => 0.1, '0.2' => 0.2, '0.3' => 0.3, '0.4' => 0.4, '0.5' => 0.5, '0.6' => 0.6, '0.7' => 0.7, '0.8' => 0.8, '0.9' => 0.9, '1.0' => 1.0
// 	];

	/**
	 * Seznam oslovení pro registraci uživatele
	 * @var Array
	 */
	static $regTitles = [
		  '' => '-- není --'
		, 'pan' => 'pan'
		, 'paní' => 'paní'
		, 'slečna' => 'slečna'
		, 'Ing.' => 'Ing.'
		, 'Bc.' => 'Bc.'
		, 'Mgr.' => 'Mgr.'
		, 'Dr.' => 'Dr.'
		, 'Prof.' => 'Prof.'
		, 'spol' => 'spol'
	];


	/**
	 * bylo by dobry udelat to tak aby se ve formulari pro ruzny UI registrovaly funkce ktere by mohli modifikovat vstup/vystup pro element formulare,
	 * a to tak ze dostanou hodnotu a v druhy promenny dostanou smer jestli z a nebo do formulare (in, out) bud jako konstantu a nebo jako retezec
	 */
	static function UI2Tpl($uiId, $rights = FormFieldRights::DELETE){/* pro form */
		$varWithFieldsName = self::$UITplsByRights[$rights];
		$fieldArray = self::$$varWithFieldsName;
		$prefix = self::$rightsPrefix[$rights];
		if(isset($fieldArray[$uiId])){
			return self::createFieldTpl($prefix . $fieldArray[$uiId]);
		}else{
			if($rights == FormFieldRights::VIEW){
				return self::createFieldTpl(self::$rightsPrefix[FormFieldRights::VIEW] . reset($fieldArray));
			}
		}
		return NULL;
	}

	static function UI2ListTpl($uiId){/* pro klasickej list */
		if(isset(self::$ListUITypesTPLS[$uiId])){
			return self::createFieldTpl(self::$ListUITypesTPLS[$uiId]);
		}
		return NULL;
	}

	static function UI2ListAjaxTpl($uiId){/* pro ajaxovy okna listu vraci tpl jen pro prvky ktery maji nahradit odkaz ktery otevira vstupni pole */
		if(isset(self::$ListAjaxUITypesTPLS[$uiId])){
			return self::createFieldTpl(self::$ListAjaxUITypesTPLS[$uiId]);
		}
		return NULL;
	}

	static function GetTPL($uiId, $type = ''){
		switch($type){
			case self::TPL_FOR_FORM:
			default:
				return self::UI2Tpl($uiId);
				break;
			case self::TPL_FOR_LIST:
				return self::UI2ListTpl($uiId);
				break;
			case self::TPL_FOR_AJAX:
				return self::UI2ListAjaxTpl($uiId);
				break;
		}
		return NULL;
	}

	static function makeTemplate($uitype, $rights){
		if(!empty($uitype)){
			if($tpl = self::UI2Tpl($uitype, $rights)){
				return $tpl;
			}else{
				return self::createFieldTpl($uitype);
			}
		}
		return NULL;
	}

	static function createFieldTpl($name){
		return self::$fieldsDir . $name . self::$fieldSufix;
	}

	static function getValidators($uiType){
		if(isset(self::$FormUITypesValidate[$uiType])){
			return self::$FormUITypesValidate[$uiType];
		}
		return [];
	}
}