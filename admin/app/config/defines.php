<?php

class MODULES{

	const LAYOUT = 1;

	const MENU = 2;

	const DOCUMENT = 3;

	const BUTTON = 4;

	const GALERY = 5;

	const FCE = 6;

	const MUTACE = 7;

	const ATTACHMENT = 8;

	const SLIDESHOW = 9;

	const NEWS = 12;

	const SETTINGS = 13;

	const PRODUCTIMG = 16;

	const PRODUCT = 15;

	const CATEGORY = 20;

	const CONTACTS = 22;

	const SELECT = 23;

	const FAQ = 24;

	const INVOICE = 28;

	const GROUPS = 29;

	const REFDIRS = 38;

	const NOTES = 32;

	const ODBER_MIST = 50;

	const VS_SEL = 60;

	const OTE = 70;

	const OTE_SEL = 71;

	const ZALOHY = 51;

	const FAKTURY = 56;
}

define('k_module', 'module');
define('k_view', 'view');
define('k_view_elem', 'vm');
define('k_action', 'action');
define('k_record', 'recordid');
define('k_system', 'system');
define('k_tab', 'tab');
define('k_sub_record', 'subRecordId');
define('k_parent', 'parent');
define('k_ajax', 'ajax');
define('k_ajax_ex', 'ajax_ex');
define('k_data', 'data');

define('k_formodule', 'formodule');
define('k_frommodule', 'frommodule');
define('k_addEnt', 'addEntity');
define('k_ext', 'ext');
define('k_extid', 'extid');
define('k_addParam', 'addParam');
define('k_colid', 'colid');
define('k_type', 'type');
define('k_ajaxSubSel', 'ajaxSubSel');
define('k_mIds', 'ids');

define('ExtObsah', 'ext_obsah');
define('ExtCRM', 'ext_crm');

define('MENU_SET_ID_KEY', 'menusetidt');

define('FUNCTION_DETAIL', 210);

/**
 * settings key for varloader and dbvars variables
 */
define('settings_front', 'front');

define('_SES_LANGUAGE', 'lang');

// define('XML_MAIN_FLASH',	_BASE_REL_PATH . 'xml/data.xml');
// define('XML_MAIN_SLIDE',	_BASE_REL_PATH . 'xml/slideshow.xml');
define('SLIDESHOW_WIDTH', 751);
define('SLIDESHOW_HEIGHT', 238);
define('SLIDESHOW_SPEED', 6);
define('SLIDESHOW_FADE', 3000);

define('_MENUSET_FLASHBOT', '_MENUSET_FLASHBOT');
define('_MENUSET_LOGREG', '_MENUSET_LOGREG');

define('SET_MENU_PRODUCT_SET', 'MENUSET_PRODUCT');
define('SYSTEM', 'system');

// define('FRAZE_NEVYBRANO', ' - nevybráno - ');
define('FCE_DATA_SEL', 'Vyberte data jež bude funkce zpracovávat');

define('_GET_PG', 'pg');
define('_GET_PID', 'pid');