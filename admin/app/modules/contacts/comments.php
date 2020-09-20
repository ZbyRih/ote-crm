<?php

class CommentsContactSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'editComms',
		self::CREATE => 'editComms',
		ListAction::EDIT => 'editComms'
	];

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editComms($info){
		if(!$info->scope->parent->isEmptyRecId()){

			$Comments = new MComments();

			$Form = ViewsFactory::createModelForm($Comments, $info, 'poznamka');

			$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
				$this,
				'onBeforeSaveComms'
			]);

			if($info->scope->action == ListAction::EDIT && $info->scope->isSetRecId()){
				$Form->processForm();
				if($Form->isSaved()){
					$info->scope->resetViewByRedirect();
				}
				$this->views->add($Form);
			}else{
				$Form->buttons->addSubmit(FormButton::CREATE, 'Přidat');
				$Form->processForm(true);
				$this->views->add($Form);

				$List = ViewsFactory::createModelList($info);
				$model = new MCommentsWUsers();
				$model->conditions['klient_id'] = $info->scope->parent->recordId;
				$model->conditions[] = '!file_id IS NULL';
				$List->configByArray(
					[
						'form' => 'poznamka',
						'model' => $model,
						'actions' => [
							ListAction::EDIT,
							ListAction::DELETE
						],
						'spcCols' => [
							'Comments' => [
								'text' => 'Poznámka',
								'DATE_FORMAT(inserted, \'%d.%m. %Y\')' => 'Zapsáno'
							],
							'User.jmeno' => 'Zapsal'
						]
					]);

				if($List->handleActions()){
					$info->scope->resetViewByRedirect();
				}

				$this->views->add(ViewsFactory::newCardPane());
				$this->views->add($List);
			}
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