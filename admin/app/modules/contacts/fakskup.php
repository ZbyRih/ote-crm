<?php

class FakSkupContactSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'editFakSkup',
		self::CREATE => 'editFakSkup',
		ListAction::EDIT => 'editFakSkup'
	];

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editFakSkup(
		$info)
	{
		// seznam fak skupin a jejich editace
		if(!$info->scope->parent->isEmptyRecId()){
			$FakSkup = new MFakSkup();

			if($info->scope->action == self::CREATE){
				$info->scope->setRecId(MFakSkup::createNew($info->scope->parent->recordId));
				$info->scope->resetViewByRedirect($info->scope->recordId, ListAction::EDIT);
			}

			if($info->scope->action == ListAction::EDIT && $info->scope->isSetRecId()){
				$Form = ViewsFactory::createModelForm($FakSkup, $info, 'fak_skup');

				$Form->getField('ContactDetails', 'title')->setList(FormUITypes::$regTitles);

				$Form->recursionSave = -1;

				$Form->processForm();
				if($Form->isSaved()){
					$info->scope->resetViewByRedirect();
				}

				$this->views->add($Form);
			}else{
				$link = ViewsFactory::createLink($info->scope->getDynLink(NULL, self::CREATE), 'Přidat novou fakturační skupinu', 'md md-my-library-add');
				$this->views->add($link);

				$List = ViewsFactory::createModelList($info);

				$FakSkup->conditions['klient_id'] = $info->scope->parent->recordId;

				$List->configByArray(
					[
						'form' => 'fak_skup',

						'model' => $FakSkup,
						'actions' => [
							ListAction::EDIT,
							ListAction::DELETE
						],
						'spcCols' => [
							'FakSkup' => [
								'cis' => 'Číslo fa. skup.',
								'nazev' => 'Název'
							],
							'ContactDetails' => [
								'firstname' => 'Jméno',
								'lastname' => 'Přijmení',
								'email' => 'email'
							]
						]
					]);

				$List->setActionCallBacks([
					ListAction::DELETE => [
						$this,
						'onListDeleteFakSkup'
					]
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
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function onListDeleteFakSkup(
		$info)
	{
		$ids = $info->scope->getCarry(k_mIds);
		if(!$ids && $info->scope->isSetRecId()){
			$ids = [
				$info->scope->recordId
			];
		}

		if($ids){
			$SmlOM = new MSmlOM();
			$SmlOM->removeAssociateModels();

			foreach($ids as $id){
				// asi taky nejak pregenerovat vska v zalohach
				if($d = $SmlOM->FindAll([
					'fak_skup_id' => $id
				])){
					foreach($d as $i){
						$i['SmlOM']['fak_skup_id'] = NULL;
						$SmlOM->Save($i);
					}
				}
				$FakSkup = new MFakSkup();
				$FakSkup->Delete($id);
			}
			return true;
		}
		return false;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	function onBeforeSaveFakSkup(
		$data,
		$form)
	{
		$data['FakSkup']['klient_id'] = $form->scope->parent->recordId;
		$data['Contacts']['fakturacni'] = 1;
		return $data;
	}
}