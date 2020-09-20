<?php

class FilesContactSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'editFiles',
		self::CREATE => 'editFiles',
		ListAction::EDIT => 'editFiles'
	];

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editFiles($info){
		if(!$info->scope->parent->isEmptyRecId()){
			$Comments = new MComments();
			$Files = new MAttachment();

			$FileForm = ViewsFactory::createModelForm($Comments, $info, 'soubor');

			$FileForm->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
				$this,
				'onBeforeSaveComms'
			]);

			$FileForm->buttons->addSubmit(FormButton::CREATE, 'Nahrát');
			$FileForm->processForm();
			$this->views->add($FileForm);

			$List = ViewsFactory::createModelList($info);
			$model = new MCommentsWUsers();
			$model->conditions['klient_id'] = $info->scope->parent->recordId;
			$model->conditions[] = '!file_id IS NOT NULL';
			$List->configByArray(
				[
					'form' => 'soubor',
					'model' => $model,
					'actions' => [ /* ListAction::EDIT, */
						ListAction::DELETE
					],
					'spcCols' => [
						'Comments' => [
							'file_id' => 'Soubor',
							'DATE_FORMAT(inserted, \'%d.%m. %Y\')' => 'Nahráno'
						],
						'User.jmeno' => 'Nahrál'
					],
					'fieldTplMap' => [
						'Comments' => [
							'file_id' => FormUITypes::SELECT_ATTCH
						]
					],
					'pagination' => true,
					'itemsOnPage' => 20
				]);

			if($List->handleActions()){
				$info->scope->resetViewByRedirect();
			}

			$this->views->add(ViewsFactory::newCardPane());
			$this->views->add($List);
		}
		return true;
	}

	/**
	 *
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	function onBeforeSaveComms($data, $form){
		$data['Comments']['klient_id'] = $form->scope->parent->recordId;
		$data['Comments']['owner_id'] = AdminUserClass::$userId;
		return $data;
	}
}