<?php

class ModulGp6select extends AppModuleClass{

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam OTE zpráv',
			'icon' => 'md md-list'
		]
	];

	public function __construct($moduleData = NULL, $modelName = NULL){
		parent::__construct($moduleData, $modelName);
	}

	function __listModuleItems($info){
		$List = $this->_createMainListObj($info);
		$this->views->add($List);
		return true;
	}

	function _createMainListObj($info){
		$List = ViewsFactory::createModelList($info);

		$year = OBE_Http::getGet('year');
		$clientId = OBE_Http::getGet('cid');

		$SmlOM = new MSmlOM();
		$oms = $SmlOM->getOmsForKlient($clientId, $year);

		if(!empty($oms)){
			$omIds = array_keys($oms);
		}else{
			$omIds = [];
			AdminApp::postMessage('Pro daný rok (' . $year . ') nebylo nalezeno žádné odběrné místo.', 'warning');
			return null;
		}

		$gp6 = new GP6FullWMailAndOM();

		$gp6->conditions = [
			'odber_mist_id' => $omIds,
			'!' . $year . ' BETWEEN YEAR(GP6Head.from) AND YEAR(GP6Head.to)',
			'!GP6Head.faktura_id IS NULL',
			'GP6Head.depricated = 0'
		];

		$gp6->order = [
			'GP6Head.odber_mist_id' => 'ASC',
			'GP6Head.from' => 'DESC',
			'OTEMails.ote_id' => 'DESC'
		];

		$List->configByArray(
			[
				'model' => $gp6,
				'actions' => [
					ListAction::SELECT
				],
				'spcCols' => [

					'OTEMails' => [
						'ote_id' => 'ID'
					],
					'GP6Head' => [
						'odber_mist_id' => 'O.M.',
						'DATE_FORMAT(GP6Head.from, \'%d.%m. %Y\')' => 'Od',
						'DATE_FORMAT(GP6Head.to, \'%d.%m. %Y\')' => 'Do',
						'attributes_segment' => 'Segment',
						'attributes_corReason' => 'Důvod',
						'priceTotalDph' => 'Total s DPH',
						'cancelled' => 'Zrušená'
					]
				],
				'numTypes' => [
					'GP6Head' => [
						'priceTotalDph' => 3
					]
				],
				'valuesSubstitute' => [
					'GP6Head' => [
						'odber_mist_id' => $oms,
						'cancelled' => [
							0 => 'ne',
							1 => 'ano'
						],
						'attributes_segment' => GP6Head::SEGMENT,
						'attributes_corReason' => GP6Head::COR_REASON
					]
				],
				'filter' => [
					[
						'type' => 'like',
						'fields' => [
							'OdberMist.com',
							'OdberMist.eic',
							'CAST(CONCAT_WS(\', \', OdberMist.eic, Address.city, Address.street, Address.cp, Address.co) AS CHAR CHARACTER SET utf8)'
						],
						'name' => 'č. o.m., eic'
					]
				]
			]);

		return $List;
	}
}