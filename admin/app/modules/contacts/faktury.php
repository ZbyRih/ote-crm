<?php
use App\Models\Orm\Orm;
use App\Models\Commands\IFakturaDownloadCommand;
use Nette\Utils\DateTime;

class FakturySubModule extends OFBaseSubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'listFak',
		self::CREATE => 'createFak',
		'create2fak' => 'create2Fak',
		'create3FakFin' => 'create3FakFin',
		'preview' => 'fakturaView'
	];

	public $onQNHandle = null;

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function listFak(
		$info)
	{
		if($info->scope->parent->isEmptyRecId()){
			return true;
		}

		$this->createDatumSplatnostiForm($info);

		$qn = $this->createYearsNav($info);
		if($qn->list){
			$this->views->add($qn);

			$faks = new MFaktury();

			$faks->conditions = [
				'klient_id' => $info->scope->parent->recordId,
				'!' . $qn->curr . ' <= YEAR(do)',
				'!deleted IS NULL'
			];

			$L = ViewsFactory::createModelList($info);
			$L->configByArray(
				[
					'form' => 'faktury',
					'model' => $faks,
					'actions' => [ // ListAction::DELETE
					],
					'spcCols' => [
						'Faktura' => [
							'CONCAT(DATE_FORMAT(Faktura.od, \'%d.%m. %Y\'), \' - \', DATE_FORMAT(Faktura.do, \'%d.%m. %Y\'))' => 'Od - do',
							// 								'om nemam kurva'
							'cis' => 'Číslo',
							'man' => 'Ruční',
							'storno' => 'Stornovaná',
							'IF(odeslano, 1, 0)' => 'Odeslána',
							'suma' => 'Suma',
							'dph' => 'DPH',
							'suma_a_dph' => 'Suma vč. DPH',
							'preplatek' => 'Doplatek'
						]
					],
					'numTypes' => [
						'Faktura' => [
							'suma' => 3,
							'dph' => 3,
							'suma_a_dph' => 3,
							'preplatek' => 3
						]
					],
					'valuesSubstitute' => [
						'Faktura' => [
							'storno' => [
								0 => 'ne',
								1 => 'ano'
							],
							'IF(Faktura.odeslano, 1, 0)' => [
								0 => 'ne',
								1 => 'ano'
							],
							'man' => [
								0 => 'ne',
								1 => 'ano'
							]
						]
					],
					'static' => true,
					'sorting' => '2:desc'
				]);

			// $L->setActionCallBacks([
			// 	ListAction::DELETE => [
			// 		$this,
			// 		'facturaDelete'
			// 	]
			// ]);

			$akce = (new ListAction('send'))->setTitle('Označit jako odesláno')
				->setIcon('md md-send')
				->setRight(FormFieldRights::EDIT);
			$L->actions->addAction('send', $akce);
			$L->actions->setCallBack('send', [
				$this,
				'fakturaSend'
			]);

			$akce = (new ListAction('preview'))->setTitle('Náhled')
				->setIcon('md md-search')
				->setRight(FormFieldRights::VIEW);
			$L->actions->addAction('preview', $akce);
			$L->actions->setCallBack('preview', [
				$this,
				'fakturaPreview'
			]);

			$akce = (new ListAction('down'))->setTitle('Stáhnout pdf')
				->setIcon('md md-file-download')
				->setRight(FormFieldRights::VIEW);
			$L->actions->addAction('down', $akce);
			$L->actions->setCallBack('down', [
				$this,
				'fakturaDownload'
			]);

			$this->views->add($L);

			if($L->handleActions()){
				$info->scope->resetViewByRedirect();
			}

			$this->views->add(ViewsFactory::createLink($info->scope->getLink(self::CREATE), 'Vytvořit fakturu', 'md md-note-add'));

			if($info->scope->action == self::CREATE){
				$info->scope->resetViewByRedirect(null, self::CREATE);
			}
		}

		// seznam faktur
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function facturaDelete(
		$info)
	{
		// $fak = new MFaktury();
		// $gp6 = new GP6Head();

		// if ($ids = ListClass::getActionIds($info)) {
		// 	foreach ($ids as $id) {
		// 		$fak->Delete($id);
		// 	}
		// }
		// return true;
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
		$fak = new MFaktury();
		if($ids = ListClass::getActionIds($info)){
			$id = reset($ids);
			$info->scope->resetViewByRedirect($id, 'preview');
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
		$cmdDownload = AdminApp::$container->getByType(IFakturaDownloadCommand::class);
		$orm = AdminApp::$container->getByType(Orm::class);

		$fak = new MFaktury();
		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){

				$fa = $orm->faktury->getById($id);
				$cmd = $cmdDownload->create();
				$cmd->setFa($fa);
				$cmd->execute();
			}
		}
		exit();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function fakturaView(
		$info)
	{
		$fak = new MFaktury();
		if($ids = ListClass::getActionIds($info)){
			$fak = new MFaktury();
			foreach($ids as $id){
				try{
					$this->views->add($fak->getView($id));
					$this->views->add(ViewsFactory::createLink($info->scope->getStaticLink(self::DEFAULT_VIEW), 'Zpět', 'md md-backspace'));
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
	function createFak(
		$info)
	{
		$qn = $this->createYearsNav($info);
		$qn->handle();

		$this->views->add(ViewsFactory::newCardPane('Vybrání OTE zpráv'));

		$ses = $this->getSession();

		$link = $info->scope->getDynLink($info->scope->recordId, 'selote');

		$this->views->add(
			ViewsFactory::createAjaxSelect(MODULES::OTE_SEL . '&year=' . $qn->curr . '&cid=' . $info->scope->parent->recordId, $link, 'zprávy ote',
				'btn btn-default', 'fa fa-files-o'));

		$this->views->add(ViewsFactory::createLink($info->scope->getStaticLink(self::DEFAULT_VIEW), 'Zpět', 'md md-backspace'));

		if($ids = OBE_Http::getGet('oids')){
			$ses->ote = array_unique(array_merge($ses->ote, explode(',', $ids)));
			$info->scope->resetViewByRedirect();
		}

		if($info->scope->isAction('selote')){
			if($id = OBE_Http::getGet('selected')){
				$ses->ote = array_unique(array_merge($ses->ote, [
					$id
				]));
				$info->scope->resetViewByRedirect();
			}
		}

		$min = null;
		$max = null;

		if($ses->ote){

			$gp6 = new GP6FullWMailAndOM();
			$gp6->order = [
				'from' => 'ASC',
				'attributes_corReason' => 'ASC',
				'odber_mist_id ASC'
			];

			$gp6->conditions = [
				'id' => (is_array($ses->ote)) ? $ses->ote : 'FALSE'
			];

			$List = ViewsFactory::createModelList($info);
			$List->configByArray(
				[
					'form' => 'ote_list',
					'model' => $gp6,
					'actions' => [
						ListAction::DELETE
					],
					'spcCols' => [
						'OTEMails' => [
							'ote_kod' => 'Kód'
						],
						'GP6Head' => [
							'CONCAT_WS(\', \', OdberMist.eic, Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'O.M.',
							'priceTotalDph' => 'Total s DPH',
							'attributes_corReason' => 'Důvod',
							'attributes_segment' => 'Segment',
							'CONCAT_WS(\' - \', DATE_FORMAT(from, \'%d.%m. %Y\'), DATE_FORMAT(to, \'%d.%m. %Y\'))' => 'Období'
						]
					],
					'numTypes' => [
						'GP6Head' => [
							'priceTotalDph' => 3
						]
					],
					'valuesSubstitute' => [
						'GP6Head' => [
							'attributes_segment' => GP6Head::SEGMENT,
							'attributes_corReason' => GP6Head::COR_REASON
						]
					]
				]);

			$List->setActionCallBacks([
				ListAction::DELETE => [
					$this,
					'removeOTE'
				]
			]);

			$akce = (new ListAction('xml'))->setTitle('Ukázat XML')
				->setIcon('md md-search')
				->setRight(FormFieldRights::VIEW);
			$List->actions->addAction('xml', $akce);
			$List->actions->setCallBack('xml', [
				$this,
				'toOte'
			]);

			$List->setAppCallBack(ListClass::ON_DATAPROCCESS, [
				$this,
				'processData'
			]);

			if($List->handleActions()){
				$info->scope->resetViewByRedirect();
			}

			$this->views->add($List);

			$this->createFakSumForm($info);
		}

		// list s výběrem fakturací z ote které se použijí pro vyfakturování
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $List
	 * @return boolean
	 */
	function removeOTE(
		$info,
		$List)
	{
		$ses = $this->getSession();

		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				if(null !== ($i = array_search($id, $ses->ote))){
					$arr = $ses->ote;
					unset($arr[$i]);
					$ses->ote = $arr;
				}
			}

			return true;
		}
		return false;
	}

	function processData(
		$item,
		$orgItem,
		$List)
	{
		$data = $orgItem['GP6Body']['data'];
		if(!empty($data)){
			$data = unserialize($data);
		}
		return $item;
	}

	/**
	 * @param ModuleInfoClass $info
	 */
	function createFakSumForm(
		$info)
	{
		$ses = $this->getSession();

		try{
			$F = (new OTEFaktura())->load($ses->ote)->build();

			// zkontrolovat jestli jdou gp6 za sebou
			// a jestli neni pro nejakou opravna faktura

			$Form = ViewsFactory::createForm($info->scope, [], 'faktury');

			$fl = $Form->createField('od', FormUITypes::DATE, $F->from->format('j.n. Y'), 'Období od');
			$fl->inline = 'first';
			$fl->setDisabled();
			$Form->addFieldToForm($fl, true);

			$t = OBE_DateTime::modifyClone($F->to, '-1 day');

			$fl = $Form->createField('do', FormUITypes::DATE, $t->format('j.n. Y'), 'Období do');
			$fl->inline = 'next';
			$fl->setDisabled();
			$Form->addFieldToForm($fl, true);

			$fl = $Form->createField('sum', FormUITypes::TEXT, $F->totalDph, 'Suma s DPH');
			$fl->inline = 'last';
			$fl->setType(FormFieldClass::CURRENCY);
			$fl->setDisabled();
			$Form->addFieldToForm($fl, true);

			$Form->buttons->addSubmit('save', 'Pokračovat');

			if($Form->handleFormSubmit()){
				$info->scope->resetViewByRedirect($info->scope->recordId, 'create2fak');
			}

			$this->views->add($Form);
		}catch(FakturaException $e){
			AdminApp::postMessage($e->getMessage(), 'warning');
		}
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function create2Fak(
		$info)
	{
		// list s výběrem fakturací z ote které se použijí pro vyfakturování
		$ses = $this->getSession();

		if($oid = OBE_Http::getGet('selOte')){
			$ses->ote = [
				$oid
			];
		}

		if(count($ses->ote) < 1){
			$info->scope->goToViewByRedirect(self::DEFAULT_VIEW);
		}

		$mGp6f = new GP6Full();

		if($data = $mGp6f->FindAll([
			'id' => (is_array($ses->ote)) ? $ses->ote : 'FALSE'
		])){

			$this->views->add(ViewsFactory::newCardPane('Parametry faktury'));

			$F = ViewsFactory::createForm($info->scope);

			$F->createField('title', FormUITypes::TEXT, $ses->params['title'], 'Popis', true);
			$p = $F->createField('sum_u_z', FormUITypes::TEXT, $ses->params['z'], 'Základ');
			$p->setType(FormFieldClass::CURRENCY)->addToForm($F);
			$p = $F->createField('sum_u_d', FormUITypes::TEXT, $ses->params['d'], 'DPH');
			$p->setType(FormFieldClass::CURRENCY)->addToForm($F);
			$p = $F->createField('sum_u_c', FormUITypes::TEXT, $ses->params['c'], 'Vč. DPH');
			$p->setType(FormFieldClass::CURRENCY)->addToForm($F);

			$p = $F->createField('dzp', FormUITypes::DATE, $ses->dzp, 'Datum zdanitelneho plnění');
			$F->addFieldToForm($p);

			$F->buttons->addSubmit(FormButton::SAVE, 'Změnit');

			if($d = $F->handleFormSubmit()){
				$ses->dzp = OBE_DateTime::getDBToDate(OBE_DateTime::convertToDB($d['dzp']));
				$ses->params = [
					'title' => $d['title'],
					'z' => $d['sum_u_z'],
					'd' => $d['sum_u_d'],
					'c' => $d['sum_u_c']
				];
				$info->scope->resetViewWithRecByRedirect();
			}

			try{
				$v = (new OTEFaktura())->setCislo('náhled')
					->setExts($ses->params)
					->setDzp($ses->dzp)
					->load($ses->ote)
					->build()
					->render()
					->getView();

				if($ses->dzp){
					$F->getField('dzp')->setValue($ses->dzp->format('j.n. Y'));
				}

				$this->views->add($F);
				$this->views->add($v);
				$this->views->add(
					ViewsFactory::createLink($info->scope->getStaticLink('create3FakFin'), 'Takhle se mi to líbí, udělat fakturu', 'md md-thumb-up'));
			}catch(FakturaException $e){
				AdminApp::postMessage($e->getMessage(), 'warning');
			}

			// zapocitat zalohy ktere jsou a ty co zaplacene nejsou tak je vycislit k zaplaceni, cili zapocitat do fakturovane castky a oznacit jako uhrazene

			$this->views->add(ViewsFactory::createLink($info->scope->getStaticLink(self::CREATE), 'Zpět', 'md md-backspace'));
		}
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function create3FakFin(
		$info)
	{
		$ses = $this->getSession();

		if(count($ses->ote) < 1){
			$info->scope->goToViewByRedirect(self::DEFAULT_VIEW);
		}

		if($ses->dzp instanceof \DateTime){
			$pp = $ses->dzp->format('y');
		}else{
			$t = new \DateTime();
			$pp = $t->modify('-1 day')->format('y');
		}

		try{

			(new OTEFaktura())->setCislo('náhled')
				->setExts($ses->params)
				->setDzp($ses->dzp)
				->setCislo(MFaktury::getNewCis($pp))
				->load($ses->ote)
				->build()
				->render()
				->save(AdminUserClass::$userId)
				->savePdf();
		}catch(FakturaException $e){
			AdminApp::postMessage($e->getMessage(), 'warning');
		}

		$ses->ote = [];
		$ses->params = [
			'title' => null,
			'z' => 0,
			'd' => 0,
			'c' => 0
		];

		$info->scope->goToViewByRedirect(self::DEFAULT_VIEW);
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $List
	 * @return boolean
	 */
	public function toOte(
		$info,
		$List)
	{
		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				AdminApp::Redirect('module=otegp6&otegp6v=previewXml&otegp6r=' . $id);
				return true;
			}
			return true;
		}
		return false;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param integer $omId
	 * @return ShortNavClass
	 */
	private function createYearsNav(
		$info,
		$omId = null)
	{
		$OTEMails = new MOTEMails();
		$OTEMails->removeAssociateModels();

		$years = $OTEMails->years();

		$qn = ViewsFactory::createShortNav($info->scope, 'year', $years, 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));
		$qn->onHandle[] = $this->onQNHandle;

		return $qn;
	}

	/**
	 * @param ModuleInfoClass $info
	 */
	private function createDatumSplatnostiForm(
		$info)
	{
		$c = new MContacts();
		$k = $c->FindOneById($info->scope->parent->recordId);

		$F = ViewsFactory::createForm($info->scope);
		$F->createField('dat_splat', FormUITypes::TEXT, $k['ContactDetails']['dat_spla_dnu'], 'Datum splatnosti (dny.)', true);
		$F->buttons->addSubmit(FormButton::SAVE, 'Uložit');

		if($data = $F->handleFormSubmit()){

			$s = [
				'ContactDetails' => [
					'klient_detail_id' => $k['ContactDetails']['klient_detail_id'],
					'dat_spla_dnu' => $data['dat_splat']
				]
			];

			(new MContactDetails())->Save($s);

			$info->scope->resetViewByRedirect($info->scope->recordId);
		}
		$this->views->add($F);
	}
}
