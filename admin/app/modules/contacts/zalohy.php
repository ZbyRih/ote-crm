<?php
use Contributte\EventDispatcher\EventDispatcher;
use App\Models\Events\DBCommitEvent;
use Nette\Application\LinkGenerator;
use App\Models\Orm\Orm;

class ZalohyContactSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'listZalohy',
		self::CREATE => 'listZalohy',
		ListAction::EDIT => 'editZalohy'
	];

	public $onQNHandle = null;

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function listZalohy(
		$info)
	{
		if(!$info->scope->parent->isEmptyRecId()){

			$qn = $this->createYearsNav($info);

			if($qn->list){

				$Zalohy = new MZalohy();

				$Form = ViewsFactory::createModelForm($Zalohy, $info, 'zaloha_create');

				$Form->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
					$this,
					'onBeforeFillZalohy'
				]);
				$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
					$this,
					'onBeforeSaveZalohy'
				]);

				$SmlOM = new MSmlOM();
				$SmlOM->removeAssociatedModelsByType('MSmlOMFlags');
				$SmlOM->group[] = 'odber_mist_id';

				$this->views->add($qn);

				$smloml = $SmlOM->FindAll([
					'klient_id' => $info->scope->parent->recordId,
					'!' . $qn->curr . ' <= YEAR(do)'
				], [
					'odber_mist_id',
					'CONCAT_WS(\', \', OdberMist.eic, Address.city, Address.street, Address.cp, Address.co) AS adresa',
					'vs',
					'zaloha'
				]);

				// jeste zkontrolovat pres smlOM
				$Zalohy = new MZalohy();
				$zals = $Zalohy->FindAll(
					[
						'klient_id' => $info->scope->parent->recordId,
						'!' . ($qn->curr + 1) . ' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
					]);

				if(empty($zals)){
					$this->views->add(
						ViewsFactory::createLink($info->scope->getLink('cpy_zals'), 'Kopírovat zálohy na ' . ($qn->curr + 1), 'md md-content-copy'));
				}

				if($info->scope->action == 'cpy_zals'){
					if($this->copyAllZals($info, $qn->curr)){
						$info->scope->resetViewByRedirect();
					}
				}

				$list = MArray::MapValToKeyFromMArray($smloml, 'SmlOM', 'odber_mist_id', 'adresa');
				$listvs = MArray::MapValToKeyFromMArray($smloml, 'OdberMist', 'odber_mist_id', 'com');
				$list_vyse = MArray::MapValToKeyFromMArray($smloml, 'SmlOM', 'odber_mist_id', 'zaloha');
				$listvs_faks = MArray::MapValToKeyFromMArrays($smloml, 'SmlOM', 'odber_mist_id', 'FakSkup', 'cis');

				if(!empty($listvs_faks)){
					foreach($listvs_faks as $k => $v){
						$listvs[$k] = $v;
					}
				}

				$Form->getField('Zalohy', 'odber_mist_id')
					->setList($list)
					->setParalel($Form->getField('Zalohy', 'vs'), $listvs)
					->setParalel($Form->getField('Zalohy', 'vyse'), $list_vyse);

				$field = $Form->createFieldM('interval', NULL, FormUITypes::DROP_DOWN, 0, 'Interval');
				$field->setList(([
					-1 => 'ze smlouvy'
				] + MZalohy::$INTERVAL));
				$Form->addFieldToForm($field, true);

				$Form->buttons->addSubmit(FormButton::CREATE, 'Generovat');

				$rec = $info->scope->recordId;
				$info->scope->recordId = NULL;

				$Form->processForm();

				$info->scope->recordId = $rec;

				$Zalohy = new MZalohy();

				$Zalohy->group[] = 'Zalohy.odber_mist_id';
				$Zalohy->group[] = 'YEAR(Zalohy.od)';

				$Zalohy->conditions['klient_id'] = $info->scope->parent->recordId;
				$Zalohy->conditions[] = '!' . $qn->curr . ' BETWEEN YEAR(od) AND YEAR(do)';

				$List = ViewsFactory::createModelList($info);
				$List->configByArray(
					[
						'form' => 'zalohy_list',
						'actions' => [
							ListAction::EDIT
						],
						'model' => $Zalohy,
						'spcCols' => [
							'OdberMist' => [
								'eic' => 'EIC',
								'com' => 'číslo odběrného místa'
							],
							'Address' => [
								'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)' => 'Adresa'
							],
							'Zalohy' => [
								'YEAR(od)' => 'Zálohy pro rok',
								'COUNT(zaloha_id)' => 'Počet záloh',
								'SUM(vyse)' => 'Suma'
							]
						],
						'primaryKey' => 'Zalohy.odber_mist_id',
						'sort' => [
							'OdberMist.eic',
							'OdberMist.com',
							'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)'
						],
						'numTypes' => [
							'Zalohy' => [
								'SUM(vyse)' => 3
							]
						],
						'numbered' => true,
						'static' => true
					]);

				if($List->handleActions()){
					$info->scope->resetViewByRedirect();
				}

				if($info->scope->action == ListAction::EDIT){
					$info->scope->resetViewByRedirect($info->scope->recordId, ListAction::EDIT);
				}

				$this->views->add($List);
				$this->views->add(ViewsFactory::newCardPane('Generovat zálohy'));
				$this->views->add($Form);
			}else{
				// 				$this->views->add(ViewsFactory::); nejakou hezkou hlášku info warning error
			}
		}
		return true;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 * @param boolean $fromDB
	 */
	public function onBeforeFillZalohy(
		$data,
		$form,
		$fromDB)
	{
		if(empty($data)){
			$data = [
				'Zalohy' => [],
				'OdberMist' => [
					'eic' => NULL
				]
			];
		}

		if(empty($data['Zalohy']['od'])){
			if(OBE_Session::exists('zal_od')){
				$data['Zalohy']['od'] = OBE_Session::read('zal_od');
			}else{
				$data['Zalohy']['od'] = OBE_DateTime::now();
			}
		}

		if(empty($data['Zalohy']['do'])){
			if(OBE_Session::exists('zal_do')){
				$data['Zalohy']['do'] = OBE_Session::read('zal_do');
			}else{
				$data['Zalohy']['do'] = date('d.m. Y', strtotime('12/31/' . date('Y')));
			}
		}

		if($fromDB){
			$data['Zalohy']['od'] = OBE_DateTime::convertFromDB($data['Zalohy']['od']);
			$data['Zalohy']['do'] = OBE_DateTime::convertFromDB($data['Zalohy']['do']);
		}
		return $data;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	public function onBeforeSaveZalohy(
		$data,
		$form)
	{
		$zal = $data['Zalohy'];

		if((OBE_DateTime::getYear($zal['do']) - OBE_DateTime::getYear($zal['od'])) > 2){
			$form->addErr('Rozsah pro generování není povolen více než dva roky.');
			return null;
		}

		if((OBE_DateTime::getYear($zal['od']) > OBE_DateTime::getYear($zal['do']))){
			$form->addErr('Datum do musí být větší než datum od.');
			return null;
		}

		OBE_Session::write('zal_od', $zal['od']);
		OBE_Session::write('zal_do', $zal['do']);

		$zal = MZalohy::validace($zal, $form->getField('interval')->getValue(), $form->scope->parent->recordId);

		$fac = AdminApp::$container->getByType(\App\Models\Commands\IGenerateZalohyCommand::class);
		/** @var GenerateZalohyCommand */
		$cmd = $fac->create();

		$cmd->setInterval($zal['interval']);
		$cmd->setFrom(OBE_DateTime::toDT($zal['od']));
		$cmd->setTo(OBE_DateTime::toDT($zal['do']));
		$cmd->setKlientId($form->scope->parent->recordId);
		$cmd->setOmId($zal['odber_mist_id']);
		$cmd->setAmount($zal['vyse']);
		$cmd->setVs($zal['vs']);
		$cmd->execute();

		$dispatcher = AdminApp::$container->getByType(EventDispatcher::class);
		$dispatcher->dispatch(DBCommitEvent::NAME);

		$form->scope->resetViewByRedirect();
		return null;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editZalohy(
		$info)
	{
		if(!$info->scope->parent->isEmptyRecId()){

			$om_id = $info->scope->recordId;

			$this->views->add(ViewsFactory::createLink($info->scope->parent->getLink(), 'Zpět na seznam podle OM', 'md md-backspace')); // md-arrow-back

			$OMo = new MOdberMist();
			$om = $OMo->FindOneById($om_id);

			$Zalohy = new MZalohy();
			$Zalohy->removeAssociateModels();
			$Zalohy->group[] = 'odber_mist_id';

			$qn = $this->createYearsNav($info, $om_id);
			$qn->handle();

			if($qn->list){

				$ExportLink = ViewsFactory::createLink($info->scope->getLink('exp_zal'), 'PDF zalohy ' . $qn->curr, 'md md-local-print-shop');
				$this->views->add($ExportLink);

				if(AdminUserClass::isSuperUser()){
					$ExportLink = ViewsFactory::createLink($info->scope->getLink('exp_zal_prev'), 'PDF zalohy ' . $qn->curr . ' náhled',
						'md md-local-print-shop');
					$this->views->add($ExportLink);
				}

				$sml = new MSmlOM();
				if($faks = $sml->FindAll(
					[
						'odber_mist_id' => $om_id,
						'klient_id' => $info->scope->parent->recordId,
						'!SmlOM.fak_skup_id IS NOT NULL',
						'!' . $qn->curr . ' BETWEEN YEAR(SmlOM.od) AND YEAR(SmlOM.do)'
					])){
					foreach($faks as $f){

						$info->scope->addExt('fa');

						$ExportLink = ViewsFactory::createLink($info->scope->getLink('exp_fa') . '&fa=' . $f['FakSkup']['fak_skup_id'],
							'PDF fak. Skup ' . $f['FakSkup']['cis'] . ' ' . $qn->curr, 'md md-local-print-shop');
						$this->views->add($ExportLink);

						if(AdminUserClass::isSuperUser()){
							$ExportLink = ViewsFactory::createLink($info->scope->getLink('exp_fa_prev') . '&fa=' . $f['FakSkup']['fak_skup_id'],
								'PDF fak. Skup ' . $f['FakSkup']['cis'] . ' ' . $qn->curr . ' náhled', 'md md-local-print-shop');
							$this->views->add($ExportLink);
						}
					}
				}

				// jeste zkontrolovat pres smlOM
				$Zalohy = new MZalohy();
				$zals = $Zalohy->FindAll(
					[
						'odber_mist_id' => $om_id,
						'klient_id' => $info->scope->parent->recordId,
						'!' . ($qn->curr + 1) . ' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
					]);
				if(empty($zals)){
					$this->views->add(
						ViewsFactory::createLink($info->scope->getLink('cpy_zal'), 'Kopírovat zálohy na ' . ($qn->curr + 1), 'md md-content-copy'));
				}
			}

			$this->views->add(ViewsFactory::newCardPane($om[$OMo->name]['eic'] . ' - ' . $om[$OMo->name]['com'] . ' - ' . MAddress::addr($om)));

			if($qn->list){

				$this->views->add($qn);

				if($info->scope->action == 'exp_zal'){
					if($this->exportZalohy($info, $qn->curr, $om_id)){
						$info->scope->resetViewByRedirect($om_id);
					}
				}

				if($info->scope->action == 'exp_zal_prev'){
					if($this->exportZalohy($info, $qn->curr, $om_id, true)){
						$info->scope->resetViewByRedirect($om_id);
					}
				}

				if($info->scope->action == 'exp_fa'){
					if($this->exportZalohyFa($info, $qn->curr, $om_id)){
						$info->scope->resetViewByRedirect($om_id);
					}
				}

				if($info->scope->action == 'exp_fa_prev'){
					if($this->exportZalohyFa($info, $qn->curr, $om_id, true)){
						$info->scope->resetViewByRedirect($om_id);
					}
				}

				if(!AdminUserClass::isOnlyOwn()){

					if($info->scope->action == 'cpy_zal'){
						if($this->copyZals($info, $qn->curr, $om_id)){
							$info->scope->resetViewByRedirect($om_id);
						}
					}

					$this->views->add(
						ViewsFactory::createLink($info->scope->getLink('aktualizovat_vs'),
							'Aktualizovat V.S. u neuhrazených záloh v roce ' . $qn->curr . ' dle zařazení OM', 'md md-settings-backup-restore', true)); // md-arrow-back
					if($info->scope->action == 'aktualizovat_vs'){
						if($this->aktualizovatVSZaloh($info, $qn->curr, $om_id)){
							$info->scope->resetViewByRedirect($om_id);
						}
					}
				}

				$Zalohy = new MZalohy();
				$Zalohy->removeAssociateModels();
				$Zalohy->conditions = [
					[
						'YEAR(od)' => $qn->curr,
						'OR',
						'YEAR(do)' => $qn->curr
					],
					'klient_id' => $info->scope->parent->recordId,
					'odber_mist_id' => $om_id
				];

				$scope = new ModuleUrlScope('zaloha', $info->scope);
				$info = new ModuleInfoClass($scope, 'zaloha', 'Zálohy');
				$info->control = $this->control;

				$Form = ViewsFactory::createModelForm($Zalohy, $info);
				$Form->buttons->addSubmit(FormButton::CREATE, 'Přidat');

				$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
					$this,
					'onBeforeSaveZaloha'
				]);
				$Form->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
					$this,
					'onBeforeFillZaloha'
				]);

				$Form->processForm(false, true);
				if($Form->isSaved()){
					$info->scope->resetViewByRedirect();
				}

				$List = ViewsFactory::createModelList($info);
				$List->configByArray(
					[
						'actions' => [
							ListAction::DELETE
						],
						'model' => $Zalohy,
						'primaryKey' => 'Zalohy.zaloha_id',
						'spcCols' => [
							'Zalohy' => [
								'DATE_FORMAT(od, \'%d.%m. %Y\')' => 'Zálohy od',
								'DATE_FORMAT(do, \'%d.%m. %Y\')' => 'do',
								'vs' => 'Var. symbol',
								'vyse' => 'Výše'
							]
						],
						'sort' => [
							'Zalohy.od'
						],
						'ajaxRowsEdit' => [
							'Zalohy' => [
								'DATE_FORMAT(od, \'%d.%m. %Y\')' => FormUITypes::DATE,
								'DATE_FORMAT(do, \'%d.%m. %Y\')' => FormUITypes::DATE,
								'vyse' => FormUITypes::TEXT
							]
						],
						'ajaxHandle' => [
							$this,
							'ajaxListZalohy'
						],
						'numTypes' => [
							'Zalohy' => [
								'preplatek' => 3
							]
						],
						'numbered' => true
					]);

				try{
					if($List->handleActions()){
						$info->scope->resetViewByRedirect();
					}
				}catch(ModelDeleteException $e){
					AdminApp::postMessage($e->getMessage(), 'warning');
				}catch(ModelSaveException $e){
					AdminApp::postMessage($e->getMessage(), 'warning');
				}

				$this->views->add($List);
				$this->views->add(ViewsFactory::newCardPane('Změnit výši všech záloh'));
				$this->changeVyseWholeList($info, $qn->curr);
				$this->views->add(ViewsFactory::newCardPane('Přidat zálohu'));
				$this->views->add($Form);
			}else{
				$info->scope->parent->resetViewByRedirect($info->scope->parent->recordId);
			}
		}
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param integer $omId
	 */
	private function createYearsNav(
		$info,
		$omId = NULL)
	{
		$Zalohy = new MZalohy();
		$Zalohy->removeAssociateModels();

		$cond['klient_id'] = $info->scope->parent->recordId;
		if($omId){
			$cond['odber_mist_id'] = $omId;
		}

		$years = $Zalohy->years($cond);

		$qn = ViewsFactory::createShortNav($info->scope, 'year', $years, 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));
		$qn->onHandle[] = $this->onQNHandle;

		return $qn;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 * @param boolean $fromDB
	 */
	public function onBeforeFillZaloha(
		$data,
		$form,
		$fromDB)
	{
		if(empty($data)){
			$data = [
				'Zalohy' => [],
				'OdberMist' => [
					'eic' => NULL
				]
			];
		}

		if(empty($data['Zalohy']['od'])){
			$data['Zalohy']['od'] = OBE_DateTime::now();
			$fromDB = false;
		}elseif($fromDB){
			$data['Zalohy']['od'] = OBE_DateTime::convertFromDB($data['Zalohy']['od']);
		}

		if(empty($data['Zalohy']['do'])){
			$mf = OBE_DateTime::getMonth($data['Zalohy']['od']);
			$yf = OBE_DateTime::getYear($data['Zalohy']['od']);
			$dt = date('t', strtotime($yf . '-' . $mf . '-1'));

			$data['Zalohy']['do'] = date('d.m. Y', strtotime($mf . '/' . $dt . '/' . $yf));
		}elseif($fromDB){
			$data['Zalohy']['do'] = OBE_DateTime::convertFromDB($data['Zalohy']['do']);
		}

		if(empty($data['Zalohy']['vyse']) || empty($data['Zalohy']['vs'])){
			$vyse = 0;
			$vs = null;

			$yf = OBE_DateTime::getYear($data['Zalohy']['od']);

			$SmlOm = new MSmlOM();

			if($smls = $SmlOm->FindAll(
				[
					'!' . $yf . ' BETWEEN YEAR(od)',
					'YEAR(do)',
					'klient_id' => $form->scope->parent->parent->recordId,
					'odber_mist_id' => $form->scope->parent->recordId
				])){
				foreach($smls as $sm){
					if($vyse < $sm['SmlOM']['zaloha']){
						$vyse = $sm['SmlOM']['zaloha'];
					}

					if($sm['FakSkup']['cis']){
						$vs = $sm['FakSkup']['cis'];
					}else{
						$vs = $sm['OdberMist']['com'];
					}
				}
			}

			if(empty($data['Zalohy']['vyse']) && $vyse){
				$data['Zalohy']['vyse'] = $vyse;
			}

			if(empty($data['Zalohy']['vs'])){
				$data['Zalohy']['vs'] = $vs;
			}
		}

		return $data;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	public function onBeforeSaveZaloha(
		$data,
		$form)
	{
		$data['Zalohy']['odber_mist_id'] = $form->scope->parent->recordId;
		$data['Zalohy']['klient_id'] = $form->scope->parent->parent->recordId;

		$data['Zalohy'] = MZalohy::validace($data['Zalohy'], key(MZalohy::$INTERVAL), $data['Zalohy']['klient_id']);

		$data['Zalohy']['od'] = OBE_DateTime::convertToDB($data['Zalohy']['od']);
		$data['Zalohy']['do'] = OBE_DateTime::convertToDB($data['Zalohy']['do']);

		$data['Zalohy']['vs'] = trim($data['Zalohy']['vs']);

		unset($data['Zalohy']['interval']);

		return $data;
	}

	/**
	 * @param string $field
	 * @param mixed $value
	 * @param ListClass $List
	 * @return boolean
	 */
	function ajaxListZalohy(
		$field,
		$value,
		$List)
	{
		$Zalohy = new MZalohy();
		$Zalohy->removeAssociateModels();
		if($zaloha = $Zalohy->FindOneById($List->info->scope->recordId)){
			switch($field){
				case 'Zalohy_DATE_FORMAT(od, \'%d.%m. %Y\')':
					$zaloha[$Zalohy->name]['od'] = OBE_DateTime::convertToDB($value);
					break;
				case 'Zalohy_DATE_FORMAT(do, \'%d.%m. %Y\')':
					$zaloha[$Zalohy->name]['do'] = OBE_DateTime::convertToDB($value);
					break;
				case 'Zalohy_vyse':
					$zaloha[$Zalohy->name]['vyse'] = OBE_Math::correctFloatNumber(OBE_Math::removeCurrency($value));
					break;
				default:
					throw new AjaxException($field . ' nebyl zpracován');
			}
			try{
				$Zalohy->Save($zaloha);
			}catch(ModelSaveException $e){
				throw new AjaxException($e->getMessage());
			}
		}
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param string $year
	 * @param integer $omId
	 */
	function exportZalohy(
		$info,
		$year,
		$omId,
		$preview = false)
	{
		$lg = AdminApp::$container->getByType(LinkGenerator::class);
		if($preview){
			$link = $lg->link('Odberatel:ZalohyNahled:nahledOM', [
				'id' => $omId,
				'klientId' => $info->scope->parent->recordId,
				'year' => $year
			]);
		}else{

			$link = $lg->link('Odberatel:ZalohyNahled:downloadOM', [
				'id' => $omId,
				'klientId' => $info->scope->parent->recordId,
				'year' => $year
			]);
		}
		OBE_AppCore::redirect($link);
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param string $year
	 * @param integer $omId
	 */
	function exportZalohyFa(
		$info,
		$year,
		$omId,
		$preview = false)
	{
		$lg = AdminApp::$container->getByType(LinkGenerator::class);
		if($preview){
			$link = $lg->link('Odberatel:ZalohyNahled:nahledFS',
				[
					'id' => $info->scope->getExt('fa'),
					'klientId' => $info->scope->parent->recordId,
					'year' => $year
				]);
		}else{

			$link = $lg->link('Odberatel:ZalohyNahled:downloadFS',
				[
					'id' => $info->scope->getExt('fa'),
					'klientId' => $info->scope->parent->recordId,
					'year' => $year
				]);
		}
		OBE_AppCore::redirect($link);
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param string $year
	 */
	function copyAllZals(
		$info,
		$year)
	{
		$SmlOM = new MSmlOM();

		if($smls = $SmlOM->FindAll([
			'klient_id' => $info->scope->parent->recordId,
			'!' . ($year + 1) . ' BETWEEN YEAR(od) AND YEAR(do)'
		])){
			$sml_fs = MArray::MapValToKeyFromMArray($smls, 'SmlOM', 'odber_mist_id', 'fak_skup_id');
			$smls_do = MArray::MapValToKeyFromMArray($smls, 'SmlOM', 'odber_mist_id', 'do');
			$odbmist_ids = array_keys($smls_do);
		}else{
			throw new ModelSaveException(sprintf('Do roku %s není platné žádné odběrné místo.', $year + 1));
		}

		$Zalohy = new MZalohy();
		$Zalohy->removeAssociateModels();

		if($zals = $Zalohy->FindAll(
			[
				'klient_id' => $info->scope->parent->recordId,
				'odber_mist_id' => $odbmist_ids,
				'!' . ($year + 1) . ' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
			])){
			throw new ModelSaveException(sprintf('Zálohy do roku %s nelze zkopírovat, již zálohy obsahuje.', $year + 1));
		}

		$sml_fs_ids = array_unique($sml_fs);
		$faks = [];

		if(!empty($sml_fs_ids)){
			$Fas = new MFakSkup();
			$Fas->removeAssociateModels();
			$faks = $Fas->FindAllById($sml_fs_ids);
			$faks = MArray::MapValToKeyFromMArray($faks, $Fas->name, 'fak_skup_id', 'cis');
		}

		foreach($sml_fs as $k => $i){
			if(isset($faks[$i])){
				$sml_fs[$k] = $faks[$i];
			}
		}

		$zals = $Zalohy->FindAll(
			[
				'klient_id' => $info->scope->parent->recordId,
				'odber_mist_id' => $odbmist_ids,
				'!' . $year . ' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
			], [], [
				'odber_mist_id',
				'od'
			]);

		$n = [];
		foreach($zals as $z){
			if(isset($smls_do[$z['Zalohy']['odber_mist_id']])){

				$z['Zalohy']['od'] = OBE_DateTime::addYearDB($z['Zalohy']['od']);
				$z['Zalohy']['do'] = OBE_DateTime::addYearDB($z['Zalohy']['do']);

				$zal_od = OBE_DateTime::getDBToDate($z['Zalohy']['od']);
				$zal_do = OBE_DateTime::getDBToDate($z['Zalohy']['do']);
				$sml_do = OBE_DateTime::getDBToDate($smls_do[$z['Zalohy']['odber_mist_id']]);

				if($zal_od < $sml_do && $zal_do <= $sml_do){

					if(isset($sml_fs[$z['Zalohy']['odber_mist_id']])){
						$z['Zalohy']['vs'] = $sml_fs[$z['Zalohy']['odber_mist_id']];
					}

					$z['Zalohy']['uhrazeno'] = 0;
					$z['Zalohy']['preplatek'] = 0;
					$z['Zalohy']['uhr'] = 0;
					unset($z['Zalohy']['zaloha_id']);
					$n[] = $z;
				}
			}
		}

		$Zalohy->Save($n);

		$this->activityLog('Zkopírováno', sprintf('Bylo zkopírováno: %s záloh na rok %s', count($n), $year + 1), null, 'info');

		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param string $year
	 * @param integer $omId
	 */
	function copyZals(
		$info,
		$year,
		$omId)
	{
		$Zalohy = new MZalohy();
		$Zalohy->removeAssociateModels();

		if($zals = $Zalohy->FindAll(
			[
				'odber_mist_id' => $omId,
				'klient_id' => $info->scope->parent->recordId,
				'!' . ($year + 1) . ' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
			])){
			throw new ModelSaveException(sprintf('Zálohy do roku %s nelze zkopírovat, již zálohy obsahuje.', $year + 1));
		}

		$zals = $Zalohy->FindAll(
			[
				'odber_mist_id' => $omId,
				'klient_id' => $info->scope->parent->recordId,
				'!' . $year . ' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
			], [], [
				'od'
			]);

		$n = [];
		foreach($zals as $z){
			$z['Zalohy']['od'] = OBE_DateTime::addYearDB($z['Zalohy']['od']);
			$z['Zalohy']['do'] = OBE_DateTime::addYearDB($z['Zalohy']['do']);
			$z['Zalohy']['uhrazeno'] = 0;
			$z['Zalohy']['preplatek'] = 0;
			$z['Zalohy']['uhr'] = 0;
			unset($z['Zalohy']['zaloha_id']);
			$n[] = $z;
		}

		$Zalohy->Save($n);

		$this->activityLog('Zkopírováno', sprintf('Bylo zkopírováno: %s záloh na rok %s', count($n), $year + 1), null, 'info');

		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function aktualizovatVSZaloh(
		$info,
		$year,
		$omId)
	{
		return (new MZalohy())->aktualizovatVS($omId, $info->scope->parent->recordId, $year);
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function unlinkZals(
		$info,
		$year,
		$omId)
	{
		(new MZalohyHack())->odpojitZalohy($info->scope->parent->recordId, $year, $omId);
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	public function changeVyseWholeList(
		$info,
		$year)
	{
		$f = ViewsFactory::createForm($info->scope);

		$field = $f->createField('nova_vyse', FormUITypes::TEXT, 0, 'Výše');
		$field->setType(FormFieldClass::CURRENCY);
		$f->addFieldToForm($field, true);
		$f->buttons->addSubmit('save', 'Změnit');

		$klientId = $info->scope->parent->parent->recordId;
		$omId = $info->scope->parent->recordId;

		if($a = $f->handleFormSubmit()){
			/** @var Orm */
			$orm = AdminApp::$container->getByType(Orm::class);

			$vyse = $a['nova_vyse'];

			$Zalohy = new MZalohy();
			$Zalohy->removeAssociateModels();
			$items = $Zalohy->FindAll([
				[
					'YEAR(od)' => $year,
					'OR',
					'YEAR(do)' => $year
				],
				'klient_id' => $klientId,
				'odber_mist_id' => $omId
			]);

			foreach($items as $i){
				$z = $orm->zalohy->getById($i[$Zalohy->name]['zaloha_id']);
				$z->vyse = $vyse;
				$orm->persist($z);
			}

			$orm->flush();
			$info->scope->resetViewByRedirect();
		}

		$this->views->add($f);
	}
}