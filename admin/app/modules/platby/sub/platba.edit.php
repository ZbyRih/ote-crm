<?php

class PlatbaEditSuModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'spojit',
		ListAction::EDIT => 'edit',
		'spojit' => 'doSpojit'
	];

	protected $tagsCtrl = null;

	public function __construct(
		$modul = null,
		$parent = null,
		$name = null)
	{
		parent::__construct($modul, $parent, $name);
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
	 * @param ModuleInfoClass $info
	 */
	public function spojit(
		$info)
	{
		$scope = $info->scope->parent;
		$pinfo = $scope->info;
		$ses = $this->getSession();

		if($this->info->scope->action == 'spojit'){
			$this->doSpojit($info, $ses);
		}

		if($ses->plaId != $scope->recordId){
			$ses->plaId = $scope->recordId;
			$ses->faks = [];
			$ses->zals = [];
		}

		$P = new MPlatby();
		$pla = $P->FindOneById($scope->recordId);

		$pack = ViewsFactory::createPack();

		$pack->add(
			ViewsFactory::createViewModelForm($P, $pinfo,
				function (
					$F)
				{
					$F->setAppCallBack(ModelFormClass2::ON_BEFORE_FILL,
						function (
							$vals)
						{
							$vals['Platba']['when'] = OBE_DateTime::convertFromDB($vals['Platba']['when']);
							return $vals;
						});
				}, 'man'), 4);

		$packIn = ViewsFactory::createPack(false);

		$pack->add($packIn, 8);

		$y = OBE_DateTime::getDBToDate($pla[$P->name]['when'])->format('Y');

		if($info->scope->isAction('selFak')){
			if($id = OBE_Http::getGet('selected')){
				$ses->faks = array_unique(array_merge($ses->faks, [
					$id
				]));
				$info->scope->resetViewByRedirect();
			}
		}

		if($info->scope->isAction('selZal')){
			if($id = OBE_Http::getGet('selected')){
				$ses->zals = array_unique(array_merge($ses->zals, [
					$id
				]));
				$info->scope->resetViewByRedirect();
			}
		}

		list($linkAddFaktura, $linkAddZaloha) = $this->addLinks($y, $pla, $info, $pack);

		$sums = (object) [
			'sumZals' => 0,
			'sumFaks' => 0
		];

		if($ppfs = (new MPVParFZ())->FindBy('platba_id', $info->scope->parent->recordId)){
			$PVPFZ = collection($ppfs);
		}else{
			$PVPFZ = collection([]);
		}

		$suma = $PVPFZ->sumOf('PVParFZ.suma');

		$ListSelFak = (new SelFakList())->create($ses, $info, $PVPFZ, $sums);
		$ListSelZal = (new SelZalList())->create($ses, $info, $PVPFZ, $y, $sums);

		if($ListSelFak->handleActions() || $ListSelZal->handleActions()){
			$info->scope->resetViewByRedirect();
		}

		$packIn->add($ListSelFak);
		$packIn->add($linkAddFaktura);

		$this->addFakSum($packIn, $sums, $info);

		$packIn->add($ListSelZal);
		$packIn->add($linkAddZaloha);

		$this->addZalSum($packIn, $sums, $info);

		$this->addWholeSum($pack, $sums, $suma, $pla, $info);

		$this->views->add($pack);

		if($ses->plaId && !empty(($ses->faks)) || !empty(($ses->zals))){
// 			$this->views->add(ViewsFactory::createLink($info->scope->getLink('spojit'), 'Spojit', 'md md-link'));
		}else{
			if(!$pla['Platba']['linked']){
				AdminApp::postMessage('Nejsou vybrány žádné faktury nebo zálohy, nelze spojit platbu.', 'warning');
			}
		}
	}

	private function addLinks(
		$y,
		$pla,
		$info,
		$pack)
	{
		$P = new MPlatby();

		$sub = '&year=' . $y . '&uhr=0';
		$vs = trim(ltrim($pla[$P->name]['vs'], '0'));

		$linkFak = $info->scope->getDynLink($info->scope->recordId, 'selFak');
		$linkZal = $info->scope->getDynLink($info->scope->recordId, 'selZal');

		if($vs){
			$Fak = new MFaktury();
			$_f = $Fak->FindOneBy('cis', $vs);
			if($_f){
				$kliId = $_f[$Fak->name]['klient_id'];
				$sub .= '&kli=' . $kliId;

				$C = new MContacts();
				$_c = $C->FindOneById($kliId);
			}else{
				$C = new MContacts();
				if($_c = $C->FindOne([
					'active' => 1,
					'deleted' => 0,
					'ContactDetails.cu' => $pla[$P->name]['from_cu']
				])){
					$kliId = $_c[$C->name]['klient_id'];
					$sub .= '&kli=' . $kliId;
				}
			}
			$KF = ViewsFactory::createForm($info->scope);
			if(isset($_c)){
				$KF->createField('name', FormUITypes::TEXT, MContactDetails::name($_c['ContactDetails']), 'Jméno klienta', true);
			}
			$KF->setAccess(FormFieldRights::VIEW);
			$KF->buttons->clear();
			$pack->addBreak('Nalezený klient');
			$pack->add($KF, 6);
		}

		return [
			ViewsFactory::createAjaxSelect(MODULES::FAKTURY . $sub, $linkFak, 'Faktury', 'btn btn-default', 'fa fa-dollar'),
			ViewsFactory::createAjaxSelect(MODULES::ZALOHY . $sub, $linkZal, 'Zálohy', 'btn btn-default', 'fa fa-leanpub')
		];
	}

	private function addFakSum(
		$pack,
		$sums,
		$info)
	{
		$pack->add(
			ViewsFactory::createViewForm($info,
				function (
					$F) use (
				$sums)
				{
					$ff = $F->createField('sum', FormUITypes::TEXT, $sums->sumFaks, 'Suma k uhrazení faktur')
						->setType(FormFieldClass::CURRENCY)
						->addToForm($F);
				}));
	}

	private function addZalSum(
		$pack,
		$sums,
		$info)
	{
		$pack->add(
			ViewsFactory::createViewForm($info,
				function (
					$F) use (
				$sums)
				{
					$F->createField('sum', FormUITypes::TEXT, $sums->sumZals, 'Suma k uhrazení zaloh')
						->setType(FormFieldClass::CURRENCY)
						->addToForm($F);
				}));
	}

	private function addWholeSum(
		$pack,
		$sums,
		$suma,
		$pla,
		$info)
	{
		$pack->add(
			ViewsFactory::createViewForm($info,
				function (
					$F) use (
				$sums,
				$pla,
				$suma)
				{
					$F->createField('sum', FormUITypes::TEXT, $sums->sumZals + $sums->sumFaks, 'Celkem')
						->setType(FormFieldClass::CURRENCY)
						->addToForm($F);
					$F->createField('dif', FormUITypes::TEXT, ($pla['Platba']['platba'] - $suma) - ($sums->sumZals + $sums->sumFaks), 'Rozdíl')
						->setType(FormFieldClass::CURRENCY)
						->addToForm($F);
				}), 6);
	}

	public function doSpojit(
		$info,
		$ses)
	{
		if($ses->plaId && (!empty(($ses->faks)) || !empty(($ses->zals)))){
			$linkedFaks = [];
			$linkedZals = [];

			$F = new MFaktury();
			$Z = new MZalohy();
			$PVPFZ = new MPVParFZ();
			$Pl = new MPlatby();
			$p = $Pl->FindOneById($ses->plaId);

			$avail = $p['Platba']['platba'] - $p['Platba']['linked'];

			if(!empty($ses->faks)){
				foreach($ses->faks as $fid){
					if($avail <= 0){
						break;
					}

					$_f = $F->FindOneById($fid);
					$uhr = $_f['Faktura']['preplatek'] - $_f['Faktura']['uhrazeno'];

					if($avail - $uhr < 0){
						$uhr = $avail;
						$avail = 0;
					}else{
						$avail -= $uhr;
					}

					$linkedFaks[] = $_f['Faktura']['cis'];

					$PVPFZ->addLink($ses->plaId, 'faktura_id', $_f['Faktura']['id'], $p['Platba']['when'], $uhr);
				}
			}

			if(!empty(($ses->zals))){
				$zs = (new MZalohy())->FindAllById((empty(($ses->zals))) ? false : $ses->zals);

				if(!empty($zs)){
					$oms = collection($zs)->extract('Zalohy.odber_mist_id')->toList();

					$Z = new MOdberMistWZalSum();
					$Z->conditions[] = [
						'odber_mist_id' => (empty($oms)) ? false : $oms,
						'YEAR(Zalohy.od)' => OBE_DateTime::getDBToDate($p['Platba']['when'])->format('Y')
// 						'!isUhrZaloha(Zalohy.vyse, Zalohy.zaloha_id) IS NOT TRUE'
					];

					$zals = $Z->FindAll();

					foreach($zals as $_z){
						if($avail <= 0){
							break;
						}

						$uhr = $_z['Zalohy']['vyse'] - $_z['Zalohy']['uhrazeno'];

						if($avail - $uhr < 0){
							$uhr = $avail;
							$avail = 0;
						}else{
							$avail -= $uhr;
						}

						$linkedZals[] = $_z['Zalohy']['vs'];

						$PVPFZ->addLink($ses->plaId, 'zaloha_id', $_z['Zalohy']['zaloha_id'], $p['Platba']['when'], $uhr);
					}
				}
			}

			// prepocitat zapocet

			if(count($linkedFaks) > 0 || count($linkedZals) > 0){
				$msg = 'Spojeno: ';
			}

			if(count($linkedFaks) > 0){
				$msg .= ' - faktur ' . count($linkedFaks) . ' (' . implode(', ', array_unique($linkedFaks)) . ') ' . '<br />';
			}

			if(count($linkedZals) > 0){
				$msg .= ' - záloh ' . count($linkedZals) . ' (' . implode(', ', array_unique($linkedZals)) . ') ';
			}

			if(isset($msg)){
				AdminApp::postMessage($msg, 'success');
			}
		}

		$info->scope->resetViewByRedirect();
	}

	/**
	 * @param ModuleInfoClass $info
	 */
	public function edit(
		$info)
	{
		$info2 = clone $info;
		$info2->scope = $info->scope->parent;
		$TagsForm = ViewsFactory::createModelForm(new MPlatbaRelTags(), $info2, 'tags');
		$TagsForm->getField('user_tags')->set($this->tagsCtrl);
		$TagsForm->processForm();
		$TagsForm->buttons->clear();

		$this->views->add($TagsForm);

		$scope = $info->scope->parent;
		$pinfo = $scope->info;

		$P = new MPlatby();

		if(!$scope->isEmptyRecId()){
			$platba = $P->FindOneById($scope->recordId);
			$linked = $platba['Platba']['link'];

			$F = ViewsFactory::createModelForm($P, $pinfo);

			if($linked){
				$info->setAccess(FormFieldRights::VIEW);
				$F->setAccess(FormFieldRights::VIEW);
				$F->buttons->clear();
				$F->buttons->addCancel(FormButton::CANCEL, 'Zpět');
			}

			$F->getField('Platba', 'man')
				->setList([
				0 => 'automatická',
				1 => 'ruční'
			])
				->setAccess(FormFieldRights::VIEW);

			$F->createField('link', FormUITypes::DROP_DOWN, $linked, 'Spárováno')
				->addToForm($F)
				->setList([
				0 => 'nespojeno',
				1 => 'spojeno'
			])
				->setAccess(FormFieldRights::VIEW);

			$F->getField('Platba', 'cislo')->setAccess((AdminUserClass::is('platby') ? FormFieldRights::EDIT : FormFieldRights::VIEW));
		}else{
			$F = ViewsFactory::createModelForm($P, $pinfo, 'man');
		}

		$i = $F->createFieldM('Nic', 'vs', FormUITypes::MODULE_ITEM_SELECT, NULL, 'Vybrat V.S.');
		$i->setSelect('Vybrat V.S.', MODULES::VS_SEL)
			->setFieldsToUrl([
			'od' => 'Platba.when'
		])
			->setListToField([
			'Zalohy.vs' => 'Platba.vs'
		]);

		$F->addFieldToForm($i);

		$F->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
			$this,
			'onBeforeFillPlatba'
		]);
		$F->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
			$this,
			'onBeforeSavePlatba'
		]);

		// když je platba spojena - neumoznit editovat

		$F->processForm();

		$this->views->add($F);
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 * @param boolean $fromDB
	 */
	public function onBeforeFillPlatba(
		$data,
		$form,
		$fromDB)
	{
		if(empty($data)){
			$data = [
				'Platba' => []
			];
		}
		if(!isset($data['Platba']['when']) || empty($data['Platba']['when'])){
			$data['Platba']['when'] = OBE_DateTime::now();
		}

		if(!isset($data['Platba']['man'])){
			$data['Platba']['man'] = 1;
		}

		if($fromDB){
			$data['Platba']['when'] = OBE_DateTime::convertFromDB($data['Platba']['when']);
		}
		return $data;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	public function onBeforeSavePlatba(
		$data,
		$form)
	{
		$data['Platba']['when'] = OBE_DateTime::convertToDB($data['Platba']['when']);
		if(!$form->scope->isSetRecId()){
			$data['Platba']['man'] = 1;
		}
		$data['Platba']['edit'] = 1;
		$data['Platba']['vs'] = trim($data['Platba']['vs']);
		return $data;
	}
}