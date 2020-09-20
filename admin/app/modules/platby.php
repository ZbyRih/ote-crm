<?php

use malkusch\lock\mutex\FlockMutex;

class ModulPlatby extends AppModuleClass{

	var $modelName = 'MPlatby';

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam plateb',
			'icon' => 'md md-list'
		],
		'create' => [
			'name' => 'Přidat platbu ručně',
			'icon' => 'md md-my-library-add'
		],
		'parzals' => [
			'name' => 'Párovat platby na Zálohy',
			'icon' => 'md md-link',
			'callback' => 'parZalsView',
			'access' => FormFieldRights::DELETE
		],
		'parfak' => [
			'name' => 'Párovat platby na Faktury',
			'icon' => 'md md-link',
			'callback' => 'parFakView',
			'access' => FormFieldRights::DELETE
		],
		'nacist' => [
			'name' => 'Načíst platby z e-mailů',
			'icon' => 'md md-import-export',
			'callback' => 'loadPlatby',
			'confirm' => true,
			'access' => FormFieldRights::DELETE
		],
		'nahrat' => [
			'name' => 'Nahrát soubor z banky',
			'icon' => 'md md-file-upload',
			'callback' => 'loadPlatbyBanka',
			'access' => FormFieldRights::DELETE
		]
	];

	protected $aTab = null;

	protected $year = null;

	/** @var EntityTagCtrl */
	protected $tagsCtrl = null;

	public function __construct(
		$moduleData = NULL,
		$modelName = NULL)
	{
		if(AdminUserClass::isOnlyOwn()){
			unset($this->topMenu['create']);
			unset($this->topMenu['parzals']);
			unset($this->topMenu['parfak']);
			unset($this->topMenu['nahrat']);
		}

		parent::__construct($moduleData, $modelName);

		$this->tagsCtrl = new EntityTagCtrl('user_tags', 'Štítky',
			[
				'driver' => TagsListFieldDrivers::TE2E,
				'tagRelModel' => 'MPlatbaRelTags',
				'tagLinkModel' => 'MPlatbaTagRel',
				'relEntityKey' => 'platba_id',
				'relTagIdKey' => 'tag_id'
			]);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems(
		$info)
	{
		$tabViewObj = ViewsFactory::createTabs($info, 'Skupina');

		$tabs = [
			'vse' => 'Vše',
			null,
			'linked' => 'Spárované',
			'null',
			'alone' => 'Nespárované',
			'null',
			'auto' => 'Automatické',
			'null',
			'man' => 'Ručně zadanné',
			'null',
			'dep' => 'Odebrané',
			'null',
			'dokl' => 'Vystavené doklady',
			'null'
		];

		$tabViewObj->setMulti($tabs, $this);

		if($info->scope->isEmptyRecId()){
			$this->views->add($tabViewObj);
		}

		// --- LIST
		$this->aTab = $tabViewObj->handleValue();

		$Qn = $this->views->add($this->createYearsNav($info));

		$this->year = $Qn->curr;

		if(!$List = $this->_createMainListObj($info)){
			return true;
		}

		$List->setActionCallBacks(
			[
				ListAction::EDIT => [
					$this,
					'__editModuleItem'
				],
				ListAction::DELETE => [
					$this,
					'onListDeletePlatby'
				]
			]);

		if($this->aTab == 'dep'){
			if($action = $List->actions->get(ListAction::DELETE)){
				$action->setIcon('md md-undo');
				$action->setTitle('Vrátit');
				$action->setMass('Vrátit');
			}
		}

		if($List->handleActions()){
			$info->scope->ResetViewByRedirect();
		}

		$this->views->add($List);
		return true;
	}

	function _createMainListObj(
		$info)
	{
		$Platby = $this->getBaseModel();

		$Platby->conditions['deprecated'] = 0;

		if(AdminUserClass::isOnlyOwn()){
			$Platby->conditions[] = '!Platba.vs IN (
				SELECT DISTINCT z.vs FROM
					es_klients AS k,
					tx_sml_om AS sml,
					tx_zalohy AS z
					WHERE k.owner_id = ' . AdminUserClass::$userId . '
					AND k.deleted = 0
					AND k.active = 1
					AND k.disabled = 0
					AND sml.klient_id = k.klient_id
					AND z.odber_mist_id = sml.odber_mist_id
					AND z.klient_id = k.klient_id
				)';
		}

		if($this->year){
			$Platby->conditions[] = 'YEAR(Platba.when) = ' . $this->year;
		}

		if($this->aTab == 'linked'){
			$Platby->conditions['!isLinkedPlatba(Platba.platba, Platba.platba_id)'] = 1;
		}elseif($this->aTab == 'alone'){
			$Platby->conditions['!isLinkedPlatba(Platba.platba, Platba.platba_id)'] = 0;
		}elseif($this->aTab == 'rucne'){
			$Platby->conditions['man'] = 0;
		}elseif($this->aTab == 'man'){
			$Platby->conditions['man'] = 1;
		}elseif($this->aTab == 'dep'){
			$Platby->conditions['deprecated'] = 1;
		}elseif($this->aTab == 'dokl'){
			$Platby->conditions[] = '!Platba.cislo IS NOT NULL';
			$Platby->conditions[] = '!Platba.cislo != ""';
		}

		if($this->aTab == 'dokl'){
			$sort = [
				'Platba.cislo' => 'DESC',
				'DATE_FORMAT(Platba.when, \'%d.%m. %Y\')',
				'Platba.from_cu',
				'Platba.subject',
				'Platba.vs',
				'Platba.platba'
			];
		}else{
			$sort = [
				'DATE_FORMAT(Platba.when, \'%d.%m. %Y\')',
				'Platba.from_cu',
				'Platba.subject',
				'Platba.vs',
				'Platba.platba',
				'Platba.cislo'
			];
		}

		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'actions' => [
					ListAction::EDIT,
					ListAction::DELETE
				],
				'model' => $Platby,
				'cols' => [
					'Platba' => [
						'from_cu',
						'subject',
						'platba',
						'preplatek',
						'vs',
						'man'
					]
				],
				'spcCols' => [
					'Platba' => [
						'!isLinkedPlatba(platba, Platba.platba_id)' => 'Napárováno',
						'DATE_FORMAT(Platba.when, \'%d.%m. %Y\')' => 'Datum',
						'cislo' => 'Číslo',
						'!IF(Platba.cislo, 1, 0) AS doklad' => 'Vytištěno'
					]
				],
				'pagination' => true,
				'itemsOnPage' => 20,
				'numbered' => true,
				'filter' => [
					[
						'type' => 'like',
						'fields' => [
							'Platba.from_cu',
							'Platba.subject',
							'Platba.vs',
							'Platba.platba'
						],
						'name' => 'Č.u., v.s., částka, popis'
					],
					[
						'type' => 'x',
						'fields' => [
							'ntags'
						],
						'name' => '<span class="text-sm" style="top:10px; position: relative;">Neobsahuje<br />Štítky<span>',
						'mod' => false
					],
					[
						'type' => 'tags',
						'fields' => [
							'tagy'
						],
						'name' => 'Štítky',
						'obj' => $this->tagsCtrl
					]
				],
				'sort' => $sort,
				'valuesSubstitute' => [
					'Platba' => [
						'man' => [
							'1' => 'manual',
							'0' => 'auto'
						],
						'isLinkedPlatba(platba, Platba.platba_id)' => [
							'0' => 'ne',
							'1' => 'ano'
						],
						'doklad' => [
							'0' => 'ne',
							'1' => 'ano'
						]
					]
				]
			]);

		if($this->aTab == 'dokl'){
			$List->actions->del(ListAction::DELETE);
			$akce = (new ListAction('deldokl'))->setTitle('Zrušit doklad')
				->setIcon('glyphicon glyphicon-remove')
				->setRight(FormFieldRights::DELETE);
			$List->actions->addAction('deldokl', $akce);
			$List->actions->setCallBack('deldokl', [
				$this,
				'onListCancelDokl'
			]);
		}

		if($a = $List->actions->get(ListAction::DELETE)){
			$a->setTitle('odebrat');
		}

		if($this->aTab == 'dokl' || $this->aTab != 'dep'){

			if($this->aTab == 'dokl'){
				$akce = (new ListAction('doklad'))->setTitle('Stáhnout příjmový doklad')->setIcon('md md-file-download');
				$List->actions->addAction('doklad', $akce);
			}else{
				$akce = (new ListAction('doklad'))->setTitle('Vytvořit příjmový doklad')
					->setIcon('md md-local-print-shop')
					->setRight(FormFieldRights::DELETE);
				$List->actions->addAction('doklad', $akce);
			}

			if(AdminUserClass::isSuperUser()){
				$akce = (new ListAction('doklad_prev'))->setTitle('Náhled příjmového dokladu')->setIcon('md md-pageview');
				$List->actions->addAction('doklad_prev', $akce);
			}

			if($info->scope->action == 'doklad'){
				OBE_AppCore::redirect('/platby.doklad/download/' . $info->scope->recordId);
				// 				if($this->exportDoklad($info, $info->scope->recordId)){
				// 					$info->scope->resetViewByRedirect();
				// 				}
				// 				return true;
			}

			if($info->scope->action == 'doklad_prev'){
				OBE_AppCore::redirect('/platby.doklad/nahled/' . $info->scope->recordId);

// 				$html = $this->exportDoklad($info, $info->scope->recordId, true);

// 				$this->views->dropAll();
				// 				$this->views->add(ViewsFactory::createHtml($html));

// 				return null;
				// 				if($this->exportDoklad($info, $info->scope->recordId, true)){
				// 						$info->scope->resetViewByRedirect();
				// 				}
			}
		}

		return $List;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __editModuleItem(
		$info)
	{
		parent::__editModuleItem($info);

		if($info->scope->isEmptyRecId()){
			return (new PlatbaEditSuModule('platba_edit', $this))->setView(ListAction::EDIT)->callback();
		}else{

			$TWO = ViewsFactory::createTabs($info, '', true, 'platby');

			$TWO->addItems([
				SubModule::DEFAULT_VIEW => 'Napárování',
				ListAction::EDIT => 'Edit'
			]);

			$this->views->add($TWO);

			return (new PlatbaEditSuModule('platba_edit', $this))->setView($TWO->getVal())
				->callback();
		}
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return boolean
	 */
	function onListDeletePlatby(
		$info,
		$list)
	{
		if($ids = ListClass::getActionIds($info)){
			$M = (new MPlatby())->removeAssociateModels();

			$unDelete = ($this->aTab == 'dep');

			try{
				if($pls = $M->FindAllById($ids)){
					if($unDelete || $M->canDelete($pls)){
						foreach($pls as $p){
							$p[$M->name]['deprecated'] = $unDelete ? 0 : 1;
							$M->Save($p);
						}
					}
				}
			}catch(ModelDeleteException $e){
				AdminApp::postMessage($e->getMessage(), 'danger');
			}
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return boolean
	 */
	function onListCancelDokl(
		$info,
		$list)
	{
		if($ids = ListClass::getActionIds($info)){
			$Model = $this->getBaseModel();
			$Model->removeAssociateModels();
			foreach($ids as $id){
				if($platba = $Model->FindOneById($id)){
					$p = $platba[$Model->name];
					if($p['cislo'] != null){
						$platba[$Model->name]['cislo'] = null;
						$Model->Save($platba);
					}
				}
			}

			MPlatby::resetCis();

			return true;
		}
		return false;
	}

	public function parZalsView()
	{
		$sub = new ZalohySpojeniPlatbSubModule('spojeni_zal', $this);
		return $sub->callback();
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function parFakView(
		$info)
	{
		$sub = new FakturySpojeniPlatebSubModule('spojeni_fa', $this);
		return $sub->callback();
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param integer $dokladId
	 */
	function exportDoklad(
		$info,
		$dokladId,
		$preview = false)
	{
		return DokladPDFExport::exportDoklad($dokladId, $preview);
	}

	function loadPlatby()
	{
		if(!AdminUserClass::is('platby')){
			AdminApp::postMessage('K načtení plateb nemáte práva', 'info');
			return true;
		}

		$mutex = new FlockMutex(fopen(WWW_DIR_OLD . '/old.php', "r"));
		if($reader = $mutex->synchronized(
			function (){
				$reader = new PlatbyMailBoxReader(OBE_AppCore::LoadVar(ModulOteacomsettings::store_key));
				$reader->read();
				return $reader;
			})){

			if($reader->hasErrors()){
				AdminApp::postMessage($reader->getErrors(), 'danger');
			}

			if($reader->hasFails()){
				AdminApp::postMessage($reader->getFails(), 'warning');
			}
		}else{
			AdminApp::postMessage('Ke stažení zpráv nedošlo, zřejmě je již aktivní jiné stahování.', 'warning');
		}

		$this->info->scope->ResetViewByRedirect(NULL, self::DEFAULT_VIEW);

		return true;
	}

	function loadPlatbyBanka()
	{
		if(!AdminUserClass::is('platby')){
			AdminApp::postMessage('K načtení plateb nemáte práva', 'info');
			return true;
		}

		$Form = ViewsFactory::createForm($this->info->scope);
		$Form->buttons->clear()->addSubmit(FormButton::SAVE, 'Zpracovat');

		$f = $Form->createField('file', FormUITypes::UPLOAD, null, 'Soubory k načtení');
		$f->setMan();
		$Form->addFieldToForm($f);

		if($data = $Form->handleFormSubmit()){
			if($file = $f->getFile()){
				$platby = file_get_contents($file);

				$reader = new PlatbyFileReader();
				$num = $reader->readCSV($platby, $rest);
// 				$num = $reader->read($platby, $rest);

				if($num > 0){
					AdminApp::postMessage('Načteno ' . $num . ' plateb ze souboru, vynecháno ' . count($rest) . '', 'info');
				}else{
					AdminApp::postMessage('Ze souboru se nenačetla žádná platba', 'warning');
				}

				$this->info->scope->ResetViewByRedirect();
			}
			$Form->clear();
		}

		$this->views->add($Form);

		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param integer $omId
	 * @return ShortNavClass
	 */
	private function createYearsNav(
		$info)
	{
		$years = (new MPlatby())->removeAssociateModels()->getYears();

		$qn = ViewsFactory::createShortNav($info->scope, 'year', $years, 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));

		return $qn;
	}
}