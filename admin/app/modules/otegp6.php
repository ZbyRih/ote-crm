<?php

class ModulOtegp6 extends AppModuleClass{

	public $handlers = [
		ModuleViewClass::DEFAULT_VIEW => '__listModuleItems',
		'preview' => 'preview',
		'demopdf' => 'doDemoPdf',
		'previewXml' => 'previewXml'
	];

	/**
	 *
	 * {@inheritdoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems($info){
		$List = $this->_createMainListObj($info);

		if($List->handleActions()){
			$info->scope->resetViewByRedirect();
		}

		$this->views->add($List);

		return true;
	}

	function _createMainListObj($info){
		$Tabs = $this->createTabs($info);

		$v = $Tabs->handleValue();

		$model = new GP6HeadOM();

		if($v == 'base'){
			if(AdminUserClass::isOnlyOwn()){
				// 				$model->conditions['owner_id'] = AdminUserClass::$userId;
			}
			$model->conditions['GP6Head.depricated'] = 0;
		}else if($v == 'nofak'){
			$model->conditions[] = '!GP6Head.faktura_id IS NULL';
			$model->conditions['GP6Head.depricated'] = 0;
		}else if($v == 'fak'){
			$model->conditions[] = '!GP6Head.faktura_id IS NOT NULL';
			$model->conditions['GP6Head.depricated'] = 0;
		}else if($v == 'zahozene'){
			$model->conditions['GP6Head.depricated'] = 1;
		}

		$model->order['GP6Head.from'] = 'DESC';
		$model->order['OdberMist.com'] = 'ASC';

		$List = ViewsFactory::createModelList($info);

		$List->configByArray(
			[
				'model' => $model,
				'spcCols' => [
					'OdberMist' => [
						'com' => 'ČOM'
					],
					'GP6Head' => [
						'CONCAT_WS(\' \', DATE_FORMAT(GP6Head.from, \'%d.%m. %Y\'), DATE_FORMAT(GP6Head.to, \'%d.%m. %Y\'))' => 'Období',
						'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'O.M.',
						'priceTotal' => 'Total bez DPH',
						'attributes_corReason' => 'Důvod',
						'attributes_segment' => 'Segment',
						'type' => 'Typ',
						'pofId' => 'POF ID'
					] + (($v == 'all') ? [
						'depricated' => 'Zahozené'
					] : []) + (($v == 'all' || $v == 'base') ? [
						'!IF(faktura_id IS NULL, 0 , 1)' => 'Vyfak.'
					] : [])
				],
				'valuesSubstitute' => [
					'GP6Head' => [
						'attributes_segment' => GP6Head::SEGMENT,
						'attributes_corReason' => GP6Head::COR_REASON,
						'IF(faktura_id IS NULL, 0 , 1)' => [
							0 => 'ne',
							1 => 'ano'
						],
						'depricated' => [
							0 => 'ne',
							1 => 'ano'
						]
					]
				],
				'pagination' => true,
				'itemsOnPage' => 30,
				'filter' => [
					[
						'type' => 'like',
						'fields' => [
							'OdberMist.com',
							'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)',
							'GP6Head.pofId'
						],
						'name' => 'č. o.m., adresa, POF ID'
					],
					[
						'type' => 'list',
						'fields' => [
							'GP6Head.type'
						],
						'list' => [
							'C' => 'C',
							'A' => 'A'
						],
						'name' => 'Typ'
					]
				],
				'numTypes' => [
					'GP6Head' => [
						'priceTotal' => 3
					]
				]

			]);

		if(AdminUserClass::isSuperUser() && ($v == 'base' || $v == 'fak' || $v == 'nofak')){
			$akce = (new ListAction('preview'))->setTitle('Náhled')
				->setIcon('md md-remove-red-eye')
				->setRight(FormFieldRights::VIEW);
			$List->actions->addAction('preview', $akce);
			$List->actions->setCallBack('preview', [
				$this,
				'doPreview'
			]);
		}

		if($v == 'base' || $v = 'nofak'){
			$akce = (new ListAction('vyfakturovat'))->setTitle('Vyfakturovat')
				->setIcon('md md-add-box')
				->setRight(FormFieldRights::EDIT);
			$List->actions->addAction('vyfakturovat', $akce);
			$List->actions->setCallBack('vyfakturovat', [
				$this,
				'doVyfakturovat'
			]);
		}

		$akce = (new ListAction('xml'))->setTitle('Ukázat XML')
			->setIcon('md md-search')
			->setRight(FormFieldRights::VIEW);
		$List->actions->addAction('xml', $akce);
		$List->actions->setCallBack('xml', [
			$this,
			'doPreviewXml'
		]);

		if(($v == 'base' || $v == 'all' || $v == 'nofak') && $v != 'zahozene'){
			$akce = (new ListAction('depricate'))->setTitle('Zahodit')
				->setIcon('fa fa-trash')
				->setRight(FormFieldRights::DELETE)
				->setMass('Zahodit');
			$List->actions->addAction('depricate', $akce);
			$List->actions->setCallBack('depricate', [
				$this,
				'doDepricate'
			]);
		}

		if($v == 'zahozene'){
			$akce = (new ListAction('recover'))->setTitle('Obnovit')
				->setIcon('fa fa-recycle')
				->setRight(FormFieldRights::EDIT)
				->setMass('Zahodit');
			$List->actions->addAction('recover', $akce);
			$List->actions->setCallBack('recover', [
				$this,
				'doRecover'
			]);
		}

		return $List;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __editModuleItem($info){
		parent::__editModuleItem($info);
	}

	function createTabs($info){
		$Tabs = ViewsFactory::createTabs($info, 'Skupina');
		$Tabs->addItems(
			[
				'base' => 'Základní',
				'all' => 'Vše',
				'nofak' => 'Nevyfakturované',
				'fak' => 'Vyfakturované',
				'zahozene' => 'Zahozené'
			]);
		$this->views->add($Tabs);
		return $Tabs;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return boolean
	 */
	function doPreview($info, $list){
		$this->info->scope->resetViewByRedirect($info->scope->recordId, 'preview');
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return boolean
	 */
	function preview($info){
		$GF = new GP6Full();

		if($g = $GF->FindOneById($info->scope->recordId)){

			$this->views->add(ViewsFactory::createLink($info->scope->getModulLink(), 'Zpět na seznam', 'md md-backspace'));

			$this->views->add(ViewsFactory::newCardPane($g[$GF->name]['pofId']));

			try{
				if($g[$GF->name]['faktura_id']){
					$v = (new MFaktury())->getView($g[$GF->name]['faktura_id']);
					$this->views->add($v);
				}else{
					$v = (new OTEFaktura())->load($info->scope->recordId)
						->setCislo('náhled')
						->build()
						->render()
						->getView();
					$this->views->add($v);

					if(AdminUserClass::isSuperUser()){
						$this->views->add(ViewsFactory::createLink($info->scope->getLinkView('demopdf'), 'PDF na nečisto', 'fa fa-file-pdf-o'));
					}

					$this->views->add(ViewsFactory::createLink($info->scope->getLink('vyfakturovat'), 'Vyfakturovat', 'md md-add-box'));
				}
			}catch(FakturaException $e){
				AdminApp::postMessage($e->getMessage(), 'warning');
			}
		}

		return true;
	}

	public function doDemoPdf($info){
		$GF = new GP6Full();
		if($g = $GF->FindOneById($info->scope->recordId)){
			(new OTEFaktura())->load($info->scope->recordId)
				->setCislo('náhled')
				->build()
				->render()
				->sendPdf(true);
		}
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return boolean
	 */
	function doVyfakturovat($info, $list){
		$GF = new GP6Full();

		if($g = $GF->FindOneById($info->scope->recordId)){

			if(!$g[$GF->name]['faktura_id']){

				try{

					$v = (new OTEFaktura())->load($info->scope->recordId);

// 					$ses = parent::getSession('faktury');

// 					$ses->kli = $v->klientId;
// 					$ses->ote = [
// 						$info->scope->recordId
// 					];

					AdminApp::Redirect(
						'module=contacts&contactsv=edit&contactsr=' . $v->klientId . '&faktury=faktury&fakturyv=create2fak&selTab=faktury&selOte=' . $info->scope->recordId);
				}catch(FakturaException $e){
					AdminApp::postMessage($e->getMessage(), 'warning');
				}
			}else{
				AdminApp::postMessage('Již je vyfakturováno', 'warning');
			}
		}else{
			AdminApp::postMessage('Něco je špatně', 'warning');
			$this->info->scope->resetViewByRedirect();
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return boolean
	 */
	function doDepricate($info, $list){
		if($ids = ListClass::getActionIds($info)){
			$Model = new GP6Head();
			$Model->removeAssociateModels();
			foreach($ids as $id){
				if($m = $Model->FindOneById($id)){
					$h = $m[$Model->name];
					if($h['depricated'] == 0 && $h['faktura_id'] == null){
						$m[$Model->name]['depricated'] = 1;
						$Model->Save($m);
					}
				}
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
	function doRecover($info, $list){
		if($ids = ListClass::getActionIds($info)){
			$Model = new GP6Head();
			$Model->removeAssociateModels();
			foreach($ids as $id){
				if($m = $Model->FindOneById($id)){
					$h = $m[$Model->name];
					if($h['depricated'] == 1){
						$m[$Model->name]['depricated'] = 0;
						$Model->Save($m);
					}
				}
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
	function doPreviewXml($info, $list){
		$this->info->scope->resetViewByRedirect($info->scope->recordId, 'previewXml');
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return boolean
	 */
	function previewXml($info){
		$GF = new GP6Full();

		if($g = $GF->FindOneById($info->scope->recordId)){

			$this->views->add(ViewsFactory::createLink($info->scope->getModulLink(), 'Zpět na seznam', 'md md-backspace'));

			$this->views->add(ViewsFactory::newCardPane($g[$GF->name]['pofId']));

			try{
				$MOte = new MOTEMails();
				$m = $MOte->FindOneBy('ote_id', $g[$GF->name]['ote_id']);
				list($file, $cnt) = $MOte->getFileCnt($m);

				$pf = new PreformatClass();
				$pf->set($file, $cnt);

				$this->views->add($pf);
			}catch(OBE_FileException $e){
				AdminApp::postMessage($e->getMessage(), 'danger');
			}
		}

		return true;
	}
}