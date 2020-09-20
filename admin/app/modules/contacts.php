<?php
use App\Klients\Grids\KlientsOverviewGrid;

class ModulContacts extends AppModuleClass{

	var $modelName = 'MOdberatel';

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam odběratelů',
			'icon' => 'md md-list'
		],
		'create' => [
			'name' => 'Vytvořit nového odběratele',
			'icon' => 'md md-my-library-add'
		],
		'export' => [
			'name' => 'Exportovat odběratele',
			'icon' => 'glyphicon glyphicon-export',
			'callback' => 'export'
		],
		'taglist' => [
			'name' => 'Seznam použitých štítků',
			'icon' => 'fa fa-tags',
			'callback' => 'tagsList'
		]
	];

	var $deleted = false;

	/**
	 * @var EntityTagCtrl
	 */
	var $tagsCtrl = null;

	/** @var TabViewClass */
	var $tabsView = null;

	/**
	 * @param array/string $modul
	 * @param SubModule $parent
	 */
	function __construct(
		$moduleData = null,
		$parent = null)
	{
		parent::__construct($moduleData, $parent);
		$this->tagsCtrl = new EntityTagCtrl('user_tags', 'Štítky',
			[
				'driver' => TagsListFieldDrivers::TE2E,
				'tagRelModel' => 'MContactRelTags',
				'tagLinkModel' => 'MContactTagRel',
				'relEntityKey' => 'klient_id',
				'relTagIdKey' => 'tag_id'
			]);
	}

	/**
	 * {@inheritdoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems(
		$info)
	{
		$List = (new KlientsOverviewGrid())->setTagsCtrl($this->tagsCtrl)
			->create($info)
			->setAction(ListAction::EDIT, [
			$this,
			'__editModuleItem'
		])
			->setAction(ListAction::DELETE, [
			$this,
			'onListDeleteContact'
		]);

		if($List->run()){
			$info->scope->resetViewByRedirect();
		}

		$this->views->add($List);
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function onListDeleteContact(
		$info,
		$list)
	{
		if($ids = ListClass::getActionIds($info)){
			$model = $this->getBaseModel()->removeAssociateModels();
			foreach($ids as $id){
				$contact = $model->FindOneById($id, [
					'klient_id',
					'address_id',
					'klient_detail_id'
				]);
				if($contact){
					$contact[$model->name]['deleted'] = (($list->filter->getItem(1)->getValue() == 1) ? 0 : 1);
					$model->Save($contact);
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __editModuleItem(
		$info)
	{
		parent::__editModuleItem($info);

		$this->tabsView = $tabViewObj = ViewsFactory::createTabs($info, 'Skupina', true, 'selTab');
		$tabViewObj->setMulti(
			[
				'edit' => 'Kontaktní (fak.) údaje',
				'paneBasic',
				'fakSkup' => 'Fak. skupiny',
				'paneFaktSkup',
				'comms' => 'Poznámky',
				'paneComms',
				'files' => 'Soubory',
				'paneFiles',
				'odbMist' => 'Odběrná místa',
				'paneOdbMist',
				'cenamwh' => 'Cena MWH/OM',
				'cenaMWH',
				'zalohy' => 'Zálohy',
				'paneZalohy',
				'spotreba' => 'Sotřeba',
				'paneSpotreba'
			] + (AdminUserClass::getModuleAccesss('faktury') > FormFieldRights::DISABLE ? [
				'faktury' => 'Faktury',
				8 => 'paneFaktury'
			] : []) + (AdminUserClass::getModuleAccesss('otezpravy') > FormFieldRights::DISABLE ? [
				'ote' => 'OTE',
				9 => 'paneOte'
			] : []) + (AdminUserClass::getModuleAccesss('logs') > FormFieldRights::DISABLE ? [
				'log' => 'Log',
				10 => 'paneLog'
			] : []), $this);

		if(!$info->scope->isEmptyRecId()){

			$this->createQuickNav($info);

			$TagsForm = ViewsFactory::createModelForm(new MContactRelTags(), $info, 'tags');
			$TagsForm->getField('user_tags')->set($this->tagsCtrl);
			$TagsForm->processForm();
			$TagsForm->buttons->clear();

			$this->views->add($TagsForm);
			$this->views->add($tabViewObj);

			return $tabViewObj->handleCallBacks($info);
		}

		$tabViewObj->reset(); // kdyz jsme v create tak reset na prvni zalozku

		return $this->paneBasic($info);
	}

	function createQuickNav(
		$info)
	{
		$name = [
			'ContactDetails' => [
				'firstname',
				'lastname',
				'firm_name'
			]
		];

		$List = (new KlientsOverviewGrid())->setTagsCtrl($this->tagsCtrl)
			->create($info)
			->passView(ModuleViewClass::DEFAULT_VIEW);

		$this->deleted = ($it = $List->getFilter(1)) ? $it->getValue() : false;

		$this->views->add(ViewsFactory::createModelShortNav($List->getGrid(), $info->scope, $name));
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneBasic(
		$info)
	{
		$Contact = $this->getBaseModel();

		$Form = ViewsFactory::createModelForm($Contact, $info);

		$Form->getField('ContactDetails', 'title')->setList(FormUITypes::$regTitles);
		$Form->getField('ContactDetails', 'kind')
			->setList(MContactDetails::$KIND)
			->setHide(
			[
				0 => [
					'ContactDetails.organ',
					'ContactDetails.firm_name',
					'ContactDetails.ico',
					'ContactDetails.dico'
				],
				1 => [
					'ContactDetails.birth_date'
				]
			]);

		$User = new MUser();
		$User->conditions[] = '!login IS NOT NULL';
		$field = $Form->getField('Contacts', 'owner_id');
		if(AdminUserClass::isChangeOwner()){
			$field->setAccess(FormFieldRights::EDIT);
		}
		$field->setListByModel($User, 'id', 'jmeno', false);

		if($info->scope->isEmptyRecId()){
			$Form->removeField('Contacts_created_by');
		}else{
			$field = $Form->getField('Contacts', 'created_by');
			$field->setListByModel($User, 'id', 'jmeno', false);
		}

		if($this->deleted){
			$f = $Form->createFieldM('Contacts', 'deleted', FormUITypes::CHECKBOX, 1, 'Smazaný');
			$Form->addFieldToForm($f);
		}

		$Form->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
			$this,
			'onBeforeFillBasic'
		]);
		$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
			$this,
			'onBeforeSaveBasic'
		]);

		$Form->processForm();

		$this->views->add($Form);

		return true;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 * @param boolean $fromDB
	 */
	function onBeforeFillBasic(
		$data,
		$form,
		$fromDB)
	{
		if(!empty($data) && $fromDB){
			$data['ContactDetails']['birth_date'] = OBE_DateTime::convertFromDB($data['ContactDetails']['birth_date']);
			$data['ContactDetails']['plat_smluv_od'] = OBE_DateTime::convertFromDB($data['ContactDetails']['plat_smluv_od']);
		}
		return $data;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	function onBeforeSaveBasic(
		$data,
		$form)
	{
		$data['ContactDetails']['birth_date'] = OBE_DateTime::convertToDB($data['ContactDetails']['birth_date']);
		$data['ContactDetails']['plat_smluv_od'] = OBE_DateTime::convertToDB($data['ContactDetails']['plat_smluv_od']);

		if(!array_key_exists('created_by', $data['Contacts'])){
			$data['Contacts']['created_by'] = AdminUserClass::$userId;
		}else{
			unset($data['Contacts']['created_by']);
		}

		return $data;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneFaktSkup(
		$info)
	{
		$sub = new FakSkupContactSubModule('fakskup', $this, 'Fakturační skupiny');
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneComms(
		$info)
	{
		$sub = new CommentsContactSubModule('poznamky', $this, 'Poznámky');
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneFiles(
		$info)
	{
		$sub = new FilesContactSubModule('soubory', $this, 'Soubory');
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneOdbMist(
		$info)
	{
		$sub = new SmlOMContactSubModule('odbmist', $this, 'Odběrná místa');
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneZalohy(
		$info)
	{
		$sub = new ZalohyContactSubModule('czalohy', $this, 'Zálohy');
		$sub->onQNHandle = [
			$this,
			'updateTabBadges'
		];
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function cenaMWH(
		$info)
	{
		$sub = new SenaMWHSubModule('cenamwh', $this, 'Ceny za MWh');
		$sub->onQNHandle = [
			$this,
			'updateTabBadges'
		];
		return $sub->callback();
	}

	public function paneSpotreba()
	{
		$sub = new SpotrebaSubModule('spotreba', $this, 'Spotřeba');
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneFaktury(
		$info)
	{
		$sub = new FakturySubModule('faktury', $this, 'Faktury');
		$sub->onQNHandle = [
			$this,
			'updateTabBadges'
		];
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneOte(
		$info)
	{
		$sub = new OteSubModule('ote', $this, 'OTE');
		$sub->onQNHandle = [
			$this,
			'updateTabBadges'
		];
		return $sub->callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneLog(
		$info)
	{
		$sub = new LogSubModule('log', $this, 'Log');
		return $sub->callback();
	}

	/**
	 * @param TopMenuItemClass $mitem
	 * @return boolean
	 */
	function export(
		$mitem)
	{
		$OdbExpView = new OdberateleExportView();
		$OdbExpView->init($this->info);

		$this->views->add($OdbExpView);

		return true;
	}

	/**
	 * @param TopMenuItemClass $mitem
	 * @return boolean
	 */
	function tagsList(
		$mitem)
	{
		$Tags = new MContactRelTags();
		$Tags->group[] = 'ContactRelTags.tag_id';

		$List = ViewsFactory::createModelList($this->info);
		$List->configByArray(
			[
				'actions' => [
					ListAction::DELETE
				],
				'primaryKey' => 'EntityTag.entitytagid',
				'model' => $Tags,
				'spcCols' => [
					'EntityTag' => [
						'entitytagname' => 'Název štítku'
					],
					'ContactRelTags' => [
						'COUNT(id)' => 'Počet požití'
					]
				],
				'pagination' => true,
				'itemsOnPage' => 30,
				'sort' => [
					'EntityTag.entitytagname'
				]
			]);

		if($action = $List->actions->get(ListAction::DELETE)){
			$List->setActionCallBacks([
				$this,
				'tagsListDelete'
			], ListAction::DELETE);
		}

		if($List->handleActions()){
			$this->info->scope->resetViewByRedirect();
		}

		$this->views->add($List);

		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function tagsListDelete(
		$info,
		$list)
	{
		$info->name = 'Štítky';
		$mids = $info->scope->getCarry(k_mIds);
		if(empty($mids) && $this->info->scope->isSetRecId()){
			$mids[] = $this->info->scope->recordId;
		}
		$Tags = new MEntityTag();
		foreach($mids as $id){
			$Tags->Delete($id);
		}
		return true;
	}

	function updateTabBadges(
		$year)
	{
		if($this->tabsView && $this->info->scope->isRecId()){

			$SmlOM = new MSmlOM();
			$oms = $SmlOM->getOmsForKlient($this->info->scope->recordId, $year);

			$omIds = (!empty($oms)) ? array_keys($oms) : [];

			if(!empty($omIds)){

				$gp6 = new GP6Head();
				$gp6->conditions = [
					'odber_mist_id' => $omIds,
					'!' . $year . ' BETWEEN YEAR(GP6Head.from) AND YEAR(GP6Head.to)',
					'GP6Head.depricated' => 0,
					'!GP6Head.faktura_id IS NULL'
				];

				$num = $gp6->Count([
					'id'
				]);
				$num = reset($num);
				if($num['num'] > 0){
					$this->tabsView->setBadge('ote', $num['num']);
				}
			}
		}
	}
}