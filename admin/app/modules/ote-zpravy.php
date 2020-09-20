<?php

use malkusch\lock\mutex\FlockMutex;

class ModulOtezpravy extends AppModuleClass{

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam zpráv',
			'icon' => 'md md-list'
		],
		'loadZpravy' => [
			'name' => 'Načtení OTE zpráv z emailové schránky',
			'icon' => 'md md-my-library-add',
			'callback' => 'loadZpravy',
			'confirm' => true
		],
		'checkUnproccessed' => [
			'name' => 'Kontrola nezpracovanych',
			'icon' => 'md md-autorenew',
			'callback' => 'checkUnproccessed'
		],
		'checkUndecrypted' => [
			'name' => 'Kontrola nerozšifrovaných',
			'icon' => 'md md-vpn-key',
			'callback' => 'checkUndecrypted'
		],
		'uploadXml' => [
			'name' => 'nahrát xml',
			'icon' => 'md md-file-upload',
			'callback' => 'uploadXml'
		]
	];

	static $an = [
		'0' => 'ne',
		'1' => 'ano',
		null => 'ne'
	];

	/**
	 *
	 * {@inheritdoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems($info){
		$tabViewObj = ViewsFactory::createTabs($info, 'Podle');
		$tabViewObj->setMulti(
			[
				'ready' => 'Rozšifrované a zpracované',
				'paneBasic',
				'unproces' => 'Nezpracované',
				'paneUnprocesed',
				'undecrypted' => 'Nerozšifrované',
				'paneUndecrypted'
			], $this);
		$this->views->add($tabViewObj);
		return $tabViewObj->handleCallBacks($info);
	}

	/**
	 *
	 * @param TopMenuItemClass $mitem
	 * @return boolean
	 */
	function loadZpravy($mitem){
		$mutex = new FlockMutex(fopen(WWW_DIR_OLD . '/old.php', "r"));
		if($processor = $mutex->synchronized(
			function (){
				$processor = new OTEMailBoxReader(OBE_AppCore::LoadVar(ModulOteacomsettings::store_key));
				$processor->read($processor->checkUnprocessed());
				return $processor;
			})){

			if($processor->hasErrors()){
				AdminApp::postMessage($processor->getErrors(), 'danger');
			}

			if($processor->hasFails()){
				AdminApp::postMessage($processor->getFails(), 'warning');
			}
		}else{
			AdminApp::postMessage('Ke stažení zpráv nedošlo, zřejmě je již aktivní jiné stahování.', 'warning');
		}
		$this->info->scope->ResetViewByRedirect(NULL, self::DEFAULT_VIEW);

		return true;
	}

	/**
	 *
	 * @param TopMenuItemClass $mitem
	 * @return boolean
	 */
	function checkUnproccessed($mitem){
		$mutex = new FlockMutex(fopen(WWW_DIR_OLD . '/old.php', "r"));
		if($processor = $mutex->synchronized(
			function (){
				$processor = new OTEMailBoxReader(OBE_AppCore::LoadVar(ModulOteacomsettings::store_key));
				$processor->checkUnprocessed();
				return $processor;
			})){

			if($processor->hasErrors()){
				AdminApp::postMessage($processor->getErrors(), 'danger');
			}

			if($processor->hasFails()){
				AdminApp::postMessage($processor->getFails(), 'warning');
			}
		}else{
			AdminApp::postMessage('Ke stažení zpráv nedošlo, zřejmě je již aktivní jiné stahování.', 'warning');
		}
		$this->info->scope->ResetViewByRedirect(NULL, self::DEFAULT_VIEW);

		return true;
	}

	/**
	 *
	 * @param TopMenuItemClass $mitem
	 * @return boolean
	 */
	function checkUndecrypted($mitem){
		$mutex = new FlockMutex(fopen(WWW_DIR_OLD . '/old.php', "r"));
		if($processor = $mutex->synchronized(
			function (){
				$processor = new OTEMailBoxReader(OBE_AppCore::LoadVar(ModulOteacomsettings::store_key));
				$processor->checkUnprocessed(true);
				return $processor;
			})){

			if($processor->hasErrors()){
				AdminApp::postMessage($processor->getErrors(), 'danger');
			}

			if($processor->hasFails()){
				AdminApp::postMessage($processor->getFails(), 'warning');
			}
		}else{
			AdminApp::postMessage('Ke stažení zpráv nedošlo, zřejmě je již aktivní jiné stahování.', 'warning');
		}
		$this->info->scope->ResetViewByRedirect(NULL, self::DEFAULT_VIEW);
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneBasic($info){
		if(true !== ($List = $this->createOTEList($info, 'ready'))){
			$this->views->add($List);
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneUnprocesed($info){
		if(true !== ($List = $this->createOTEList($info, 'unprocessed'))){
			$this->views->add($List);
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneUndecrypted($info){
		if(true !== ($List = $this->createOTEList($info, 'undecrypted'))){
			$this->views->add($List);
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function createOTEList($info, $typ){
		$model = new MOTEMails();

		if($typ == 'undecrypted'){
			$model->conditions['decrypted'] = 0;
		}else if($typ == 'unprocessed'){
			$model->conditions['decrypted'] = 1;
			$model->conditions['processed'] = 0;
		}else if($typ == 'ready'){
			$model->conditions['decrypted'] = 1;
			$model->conditions['processed'] = 1;
		}

		$model->order['OTEMails.Received'] = 'DESC';

		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'model' => $model,
				'actions' => [
					ListAction::EDIT
				],
				'cols' => [],
				'spcCols' => [
					'OTEMails' => [
						'DATE_FORMAT(OTEMails.received, \'%d.%m. %Y %H:%i\')' => 'Obdrženo',
						'ote_id' => 'OTE ID',
						'decrypted' => 'Rozšif.',
						'processed' => 'Zprac.',
						'ote_kod' => 'Kód zprávy',
						'subject' => 'Předmět'
					]
				],
				'pagination' => true,
				'itemsOnPage' => 20,
				'sort' => [
					'DATE_FORMAT(OTEMails.received, \'%d.%m. %Y %H:%i\')',
					'OTEMails.decrypted',
					'OTEMails.processed',
					'OTEMails.ote_kod'
				],
				'filter' => [
					[
						'type' => 'like',
						'fields' => [
							'OTEMails.ote_kod',
							'OTEMails.ote_id',
							'OTEMails.subject'
						],
						'name' => 'Kód, OTE ID, předmět'
					]
				],
				'valuesSubstitute' => [
					'OTEMails' => [
						'decrypted' => self::$an,
						'processed' => self::$an
					]
				]
			]);

		$List->actions->get(ListAction::EDIT)
			->setIcon('md md-remove-red-eye')
			->setTitle('Náhled');

		$List->actions->setCallBack(ListAction::EDIT, [
			$this,
			'viewOTEMessage'
		]);

		if($ret = $List->handleActions()){
			if($ret == 'view'){
				return true;
			}
			$info->scope->resetViewByRedirect();
		}
		return $List;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 */
	function viewOTEMessage($info){
		$mote = new MOTEMails();
		$m = $mote->FindOneById($info->scope->recordId);
		list($file, $cnt) = $mote->getFileCnt($m);

		$pf = new PreformatClass();
		$pf->set($file, $cnt);

		$this->views->add($pf);

		return 'view';
	}

	public function uploadXml(){
		$f = ViewsFactory::createForm($this->info->scope);
		$u = $f->createField('file', FormUITypes::UPLOAD, null, 'XML soubor');
		$u->setMan()->addToForm($f);

		$f->buttons->clear();
		$f->buttons->addSubmit(FormButton::SAVE, 'Nahrát');

		if($d = $f->handleFormSubmit()){
			if($raw = file_get_contents($u->getFile())){
				$xml = simplexml_load_string($raw);
				$p = new OTEXmlProcessor();
				if($p->process($xml, '-user-')){
					AdminApp::postMessage('XML uloženo a zpracováno', 'info');
					return true;
				}
			}
			AdminApp::postMessage('XML nezpracováno', 'danger');
			$this->info->scope->resetViewWithRecByRedirect();
		}

		$this->views->add($f);
		return true;
	}
}