<?php
use App\Faktury\Grids\FakturyAjaxGrid;
use App\Faktury\Grids\FakturyOverviewGrid;

class ModulFaktury extends AppModuleClass{

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam faktur',
			'icon' => 'md md-list'
		],
		ModuleViewClass::CREATE => [
			'name' => 'Vytvořit ruční fakturu',
			'icon' => 'md md-note-add'
		],
		'sparovat' => [
			'name' => 'Párovat platby',
			'icon' => 'md md-link',
			'callback' => 'sparovatPlatby'
		]
	];

	private $listModel;

	function __construct(
		$moduleData = NULL,
		$parent = NULL,
		$name = null)
	{
		if(AdminUserClass::isOnlyOwn()){
			unset($this->topMenu['sparovat']);
		}
		$this->handlers = $this->handlers + [
			'view' => 'fakturaView',
			'sparovat' => 'sparovatPlatby'
		];
		parent::__construct($moduleData, $parent, $name);
	}

	/**
	 * {@inheritdoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems(
		$info)
	{
		$Tabs = $this->createTabs($info);
		$Qn = $this->createYearsNav($info);

		if(!$Qn->list){
			return true;
		}

		$U = new MUser();
		$us = MArray::MapValToKeyFromMArray($U->FindAll(), $U->name, 'id', 'jmeno');

		$v = $Tabs->handleValue();

		$this->views->add($Qn);

		$List = (new FakturyOverviewGrid())->setView($v)
			->setYear($Qn->curr)
			->create($info);

		$List->setAction(ListAction::DELETE, [
			$this,
			'facturaDelete'
		]);

		$List->setAction(ListAction::EDIT, [
			$this,
			'__editModuleItem'
		]);

		if($a = $List->getAction(ListAction::DELETE)){
			$a->setMass(null);
		}

		$List->addAction('send', [
			$this,
			'fakturaSend'
		])
			->setTitle('Označit jako odesláno')
			->setIcon('md md-send')
			->setRight(FormFieldRights::EDIT);

		$List->addAction('preview', [
			$this,
			'fakturaPreview'
		])
			->setTitle('Náhled')
			->setIcon('md md-search')
			->setRight(FormFieldRights::VIEW);

		$List->addAction('down', [
			$this,
			'fakturaDownload'
		])
			->setTitle('Stáhnout pdf')
			->setIcon('md md-file-download')
			->setRight(FormFieldRights::VIEW);

		$List->addAction('predelat', [
			$this,
			'fakturaRestore'
		])
			->setTitle('Znovu vygenerovat pdf (předchozí verze bude ztracena)')
			->setIcon('md md-refresh')
			->setRight(FormFieldRights::EDIT);

		$this->views->add($List);

		try{
			if($List->run()){
				$info->scope->resetViewByRedirect();
			}
		}catch(ModelDeleteException $e){
			AdminApp::postMessage($e->getMessage(), 'danger');
		}

		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 */
	function createAjaxSelectList(
		$info)
	{
		return (new FakturyAjaxGrid())->create($info);
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return ShortNavClass
	 */
	private function createYearsNav(
		$info)
	{
		$years = (new MFaktury())->removeAssociateModels()->getYears();

		$qn = ViewsFactory::createShortNav($info->scope, 'year', $years, 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));

		return $qn;
	}

	function createTabs(
		$info)
	{
		$Tabs = ViewsFactory::createTabs($info, 'Skupina');
		$Tabs->addItems(
			[
				'base' => 'Vše',
				'prepl' => 'Přeplatky',
				'nedpl' => 'Nedoplatky',
				'neuhr' => 'Neuhrazené',
				'neods' => 'Neodeslané',
				'storno' => 'Storno',
				'man' => 'Ručně zadané'
			]);
		$this->views->add($Tabs);
		return $Tabs;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function facturaDelete(
		$info)
	{
		$fak = new MFaktury();
		$gp6 = new GP6Head();

		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				$fak->Delete($id);
			}
		}
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function fakturaSend(
		$info)
	{
		$fak = new MFaktury();

		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				if($f = $fak->FindOneById($id)){
					if($f[$fak->name]['odeslano']){
						AdminApp::postMessage('Faktura ' . $f[$fak->name]['cis'] . ' byla již odeslána.', 'warning');
					}else{
						AdminApp::postMessage('Faktura ' . $f[$fak->name]['cis'] . ' označena jako odeslána.', 'info');
						$f[$fak->name] = [
							'id' => $id,
							'odeslano' => 'NOW()'
						];
						$fak->Save($f);
					}
				}
			}
		}
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function fakturaPreview(
		$info)
	{
		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				$info->scope->resetViewByRedirect($id, 'view');
			}
		}
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function fakturaDownload(
		$info)
	{
		$fak = new MFaktury();
		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				(new FakturaFile($id))->downloadFile();
			}
		}
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function fakturaRestore(
		$info)
	{
		$fak = new MFaktury();
		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				if($f = $fak->FindOneById($id)){
					if($f[$fak->name]['man']){
						$info->scope->resetViewByRedirect();
					}

					$gp6 = new GP6Full();
					$gs = $gp6->FindBy('faktura_id', $id, [], [], [
						'GP6Head.from' => 'ASC'
					]);

					try{
						$v = (new OTEFaktura())->setCislo($f[$fak->name]['cis'])
							->setExts(unserialize($f[$fak->name]['params']))
							->setDzp(OBE_DateTime::getDBToDate($f[$fak->name]['dzp']))
							->setSplatnost(OBE_DateTime::getDBToDate($f[$fak->name]['splatnost']))
							->setVystaveni(OBE_DateTime::getDBToDate($f[$fak->name]['vystaveno']))
							->load(MArray::getKeyValsFromModels($gs, $gp6->name, 'id'), false)
							->build()
							->render()
							->udpate(AdminUserClass::$userId, $id)
							->sendPdf();
					}catch(FakturaException $e){
						AdminApp::postMessage($e->getMessage(), 'warning');
					}
				}
			}
		}
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function fakturaView(
		$info)
	{
		if($ids = ListClass::getActionIds($info)){
			$fak = new MFaktury();
			foreach($ids as $id){
				try{
					$this->views->add($fak->getView($id));
					$this->views->add(ViewsFactory::createLink($info->scope->getStaticLink(self::DEFAULT_VIEW), 'Zpět', 'md md-backspace'));

					FakturyDetailLists::addListOfPlas($info, $info->scope->recordId, $this->views);
					FakturyDetailLists::addListOfOtes($info, $info->scope->recordId, $this->views);
				}catch(FakturaException $e){
					AdminApp::postMessage($e->getMessage(), 'warning');
				}
			}
		}
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function __editModuleItem(
		$info)
	{
		parent::__editModuleItem($info);

		$sub = new FakturyEditSubModule('spojeni', $this);
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function sparovatPlatby(
		$info)
	{
		$sub = new FakturySpojeniPlatebSubModule('spojeni', $this);
		return $sub->callback();
	}
}