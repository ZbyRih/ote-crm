<?php

// define('_ADMIN_IMPEXP_PATH', APP_CLASSES_PATH . 'imp-exp/');

class ModulImpexp extends AppModuleClass{
	var $topMenu = [
		  'exp_prod' => [
		  	  'type' => 'link'
		  	, 'name' => 'Export Produktů'
		  	, 'callback' => '_vCallbackView'
		  ]
		, 'imp_prod' => [
		  	  'type' => 'link'
		  	, 'name' => 'Import Produktů'
		  	, 'callback' => '_vCallbackView'
		]
		, 'exp_cont' => [
		  	  'type' => 'link'
		  	, 'name' => 'Export Kontaktů'
		  	, 'callback' => '_vCallbackView'
		  ]
		, 'imp_cont' => [
		  	  'type' => 'link'
		  	, 'name' => 'Import Kontaktů'
		  	, 'callback' => '_vCallbackView'
		]
		, 'xml_exp_prod' => [
		  	  'type' => 'link'
		  	, 'name' => 'XML Export Produktů pro zbozi.cz'
		  	, 'callback' => '_vCallbackView'
		]
	];

	var $neco = [
		  'exp_prod' => ['imp' => false, 'action' => 'Export']
		, 'exp_cont' => ['imp' => false, 'action' => 'Export']
		, 'imp_prod' => ['imp' => true, 'action' => 'Import']
		, 'imp_cont' => ['imp' => true, 'action' => 'Import']
		, 'xml_exp_prod' => ['imp' => false, 'action' => 'Export']
	];

	var $params = [
		  'exp_prod' => 'product'
		, 'exp_cont' => 'contacts'
		, 'imp_prod' => 'product'
		, 'imp_cont' => 'contacts'
		, 'xml_exp_prod' => 'productXML'
	];

	var $exts = [
		  'exp_prod' => 'csv'
		, 'exp_cont' => 'csv'
		, 'imp_prod' => ''
		, 'imp_cont' => ''
		, 'xml_exp_prod' => 'xml'
	];

	function Initialize(){
		parent::Initialize();
	}

	function _vCallbackView($view = ''){
		$view = $this->info->currentView;
		$data = [];
		$viewData = $this->neco[$view];
		$viewData['label'] = $this->topMenu->topMenuItems[$view]->name;

		if(OBE_Http::issetPost('_action')){
			$class = $this->params[$view];
			$ftype = $this->exts[$view];
			switch($view){
			case 'exp_prod':
			case 'exp_cont':
				$this->ProcesExport($class, $ftype);
				break;
			case 'imp_prod':
			case 'imp_cont':
				$data = $this->ProcesImport($class);
				break;
			case 'xml_exp_prod':
				$this->ProcesExport($class, $ftype);
				break;
			}
		}
		$this->views->add(ViewsFactory::createImport($data, $viewData));
		return true;
	}

	function ProcesImport($type){
		if(!empty($_FILES['csv_file'])){
			$class = $type . 'Import';
			$Import = new $class();
			$lines = $Import->ParseCSVFile($_FILES['csv_file']['tmp_name']);
			if($lines == -1){
				OBE_App::$db->FinishTransaction(false);
				return ['message' => $Import->error_buffer, 'aft_imp' => true];
			}
			list($num, $status) = $Import->WriteAllToDB();
			return [
				  'messages' => $Import->errors_on_line
				, 'aft_imp' => true
				, 'nums' => $Import->cat_import_count
				, 'update_num' => $Import->ok_update_num
				, 'adds' => $num - $Import->ok_update_num
				, 'all' => $lines
				, 'status' => $status
			];
		}
	}

	function ProcesExport($type, $ext){
		$class = $type . 'ExportClass';
		$Export = new $class();
		$Export->Main();
		$Export->Flush($type . '.' . $ext);
		exit;
	}
}