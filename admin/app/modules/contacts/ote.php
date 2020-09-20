<?php


class OteSubModule extends OFBaseSubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'listOte'
	];

	public $onQNHandle = null;

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function listOte($info){

		if(!$info->scope->parent->isEmptyRecId()){
			$qn = $this->createYearsNav($info);

			if($qn->list){
				$this->views->add($qn);

				$List = ViewsFactory::createModelList($info);

				$SmlOM = new MSmlOM();
				$oms = $SmlOM->getOmsForKlient($info->scope->parent->recordId, $qn->curr);

				if(empty($oms)){
					$msg = ViewsFactory::createMessage('Uživatel nemá nasmlouvaná žádná odběrná místa v roce ' . $qn->curr, 'info');
					$this->views->add($msg);
					return true;
				}

				$omIds = array_keys($oms);

				$gp6 = new GP6HeadWMailAndOM();
				$gp6->conditions = [
					'odber_mist_id' => $omIds,
					'!' . $qn->curr . ' BETWEEN YEAR(GP6Head.from) AND YEAR(GP6Head.to)',
					'GP6Head.depricated' => 0,
					'!GP6Head.faktura_id IS NULL'
				];
				$gp6->order = [
					'GP6Head.odber_mist_id' => 'DESC',
					'GP6Head.ote_id' => 'DESC'
				];

				$List->configByArray(
					[
						'form' => 'ote_list',
						'model' => $gp6,
						'actions' => [
							ListAction::SELECT
						],
						'spcCols' => [

							'OTEMails' => [
								'CONCAT(DATE_FORMAT(GP6Head.from, \'%d.%m. %Y\'), \' - \', DATE_FORMAT(GP6Head.to, \'%d.%m. %Y\'))' => 'Od - do',
								'ote_kod' => 'Kód',
								'ote_id' => 'ID'
							],
							'GP6Head' => [
								'CONCAT_WS(\', \', OdberMist.eic, Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'O.M.',
								'attributes_segment' => 'Segment',
								'attributes_corReason' => 'Důvod',
								'priceTotal' => 'Total bez DPH',
								'cancelled' => 'Zrušená',
								'!IF(GP6Head.faktura_id, 1, 0) AS vyfak' => 'Vyfakturovaná'
							]
						],
						'numTypes' => [
							'GP6Head' => [
								'priceTotal' => 3
							]
						],
						'valuesSubstitute' => [
							'GP6Head' => [
								'cancelled' => [
									0 => 'ne',
									1 => 'ano'
								],
								'attributes_segment' => GP6Head::SEGMENT,
								'attributes_corReason' => GP6Head::COR_REASON,
								'vyfak' => [
									0 => 'ne',
									1 => 'ano'
								]
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
								'name' => 'č. o.m., eic, adr.'
							]
						]
					]);

				$List->setActionCallBacks([
					ListAction::SELECT => [
						$this,
						'toOte'
					]
				]);

				$akce = (new ListAction('preview'))->setTitle('Náhled')->setIcon('md md-remove-red-eye')->setRight(FormFieldRights::VIEW);

				$List->actions->addAction('preview', $akce);
				$List->actions->setCallBack('preview', [
					$this,
					'doPreview'
				]);

				$akce = (new ListAction('createFak'))->setTitle('Vytvořit fakturu')
					->setIcon('md md-note-add')
					->setRight(FormFieldRights::EDIT)
					->setMass('createFak');

				$List->actions->addAction('createFak', $akce);
				$List->actions->setCallBack('createFak', [
					$this,
					'doCreateFak'
				]);

				if($List->handleActions()){
					$info->scope->resetViewByRedirect();
				}

				$this->views->add($List);
			}
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param integer $omId
	 */
	private function createYearsNav($info, $omId = NULL){

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
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $List
	 * @return boolean
	 */
	public function toOte($info, $List){
		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				AdminApp::Redirect('module=faktury&fakturyv=previewXml&fakturyr=' . $id);
				return true;
			}
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $List
	 * @return boolean
	 */
	public function doPreview($info, $List){
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
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $List
	 * @return boolean
	 */
	public function doCreateFak($info, $List){
		if($ids = ListClass::getActionIds($info)){
			$nids = [];

			$gp6 = new GP6FullWMailAndOM();
			foreach($ids as $i){
				$g = $gp6->FindOneById($i);
				if(!$g[$gp6->name]['faktura_id']){
					$nids[] = $i;
				}
			}

			if(empty($nids)){
				AdminApp::postMessage('Není nic vybráno / nebo nebyla vybrána nevyfakturovaná zpráva.', 'warning');
				return;
			}

			$p = $info->scope->parent;
			$n = new ModuleUrlScope('faktury', $p);
			$n->setStatic('selTab', 'faktury');
			$n->setStatic('oids', implode(',', $nids));

			$n->resetViewByRedirect(null, 'create');

			return true;
		}
		return false;
	}
}