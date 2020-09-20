<?php


class ModulPohyby extends AppModuleClass{

	var $modelName = 'MPohyb';

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam pohybů',
			'icon' => 'md md-list'
		],
		'create' => [
			'name' => 'Přidat pohyb',
			'icon' => 'md md-my-library-add'
		]
	];

	var $aTab = NULL;

	public function __construct($moduleData = NULL, $modelName = NULL){
		parent::__construct($moduleData, $modelName);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems($info){
		$tabViewObj = ViewsFactory::createTabs($info, 'Skupina');

		$tabs = [
			'vse' => 'Vše',
			null,
			'in' => 'Přijmy',
			'null',
			'out' => 'Výdaje',
			'null'
		];

		$tabViewObj->setMulti($tabs, $this);

		if($info->scope->isEmptyRecId()){
			$this->views->add($tabViewObj);
		}

		// --- LIST
		$this->aTab = $tabViewObj->handleValue();

		$List = $this->_createMainListObj($info);

		$List->setActionCallBacks(
			[
				ListAction::EDIT => [
					$this,
					'__editModuleItem'
				],
				ListAction::DELETE => [
					$this,
					'onListDeletePohyby'
				]
			]);

		if($List->handleActions()){
			$info->scope->ResetViewByRedirect();
		}

		$this->views->add($List);
		return true;
	}

	function _createMainListObj($info){
		$Pohyby = $this->getBaseModel();

		if($this->aTab == 'in'){
			$Pohyby->conditions['way'] = 'in';
		}elseif($this->aTab == 'out'){
			$Pohyby->conditions['way'] = 'out';
		}

		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'actions' => [
					ListAction::EDIT,
					ListAction::DELETE
				],
				'model' => $Pohyby,
				'cols' => [
					'Pohyb' => [
						'castka',
						'way',
						'typ'
					]
				],
				'spcCols' => [
					'Pohyb' => [
						'DATE_FORMAT(Pohyb.when, \'%d.%m. %Y\')' => 'Datum'
					]
				],
				'pagination' => true,
				'itemsOnPage' => 20,
				'numbered' => true,
				'filter' => [
					[
						'type' => 'like',
						'fields' => [
							'Pohyb.from_cu',
							'Pohyb.subject',
							'Pohyb.vs',
							'Pohyb.platba'
						],
						'name' => 'Č.u., v.s., částka, popis'
					]
				],
				'valuesSubstitute' => [
					'Pohyb' => [
						'way' => [
							'in' => 'příjem',
							'out' => 'výdaj'
						],
						'typ' => [
							'hotove' => 'hotově',
							'prevodem' => 'převodem'
						]
					]
				]
			]);

		return $List;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __editModuleItem($info){
		parent::__editModuleItem($info);

		$Platby = $this->getBaseModel();

		$Form = ViewsFactory::createModelForm($Platby, $info);

		$Form->getField('Pohyb', 'typ')->setList([
			'prevodem' => 'převodem',
			'hotove' => 'hotově'
		]);

		$Form->getField('Pohyb', 'way')->setList([
			'in' => 'příjem',
			'out' => 'výdaj'
		]);

// 		$f = $Form->createField('cil', FormUITypes::DROP_DOWN, null, 'Co');
// 		$f->setList([
// 			'f' => 'Úhrada přeplatku faktury'
// 		]);
// 		$Form->addFieldToForm($f);

		$Form->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
			$this,
			'onBeforeFillPohyby'
		]);
		$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
			$this,
			'onBeforeSavePohyby'
		]);

		$Form->processForm();

		$this->views->add($Form);

		return true;
	}

	/**
	 *
	 * @param array $data
	 * @param ModelFormClass2 $form
	 * @param boolean $fromDB
	 */
	public function onBeforeFillPohyby($data, $form, $fromDB){
		if(empty($data)){
			$data = [
				'Pohyb' => []
			];
		}

		if(!isset($data['Pohyb']['when']) || empty($data['Pohyb']['when'])){
			$data['Pohyb']['when'] = OBE_DateTime::now();
		}

		if($fromDB){
			$data['Pohyb']['when'] = OBE_DateTime::convertFromDB($data['Pohyb']['when']);
		}
		return $data;
	}

	/**
	 *
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	public function onBeforeSavePohyby($data, $form){
		$data['Pohyb']['when'] = OBE_DateTime::convertToDB($data['Pohyb']['when']);
		return $data;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param integer $dokladId
	 */
	function exportDoklad($info, $dokladId, $preview = false){
		return DokladPDFExport::exportDoklad($dokladId, $preview);
	}
}