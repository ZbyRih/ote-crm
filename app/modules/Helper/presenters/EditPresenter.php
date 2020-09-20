<?php

namespace App\Modules\Helper\Presenters;

use App\Models\Orm\Helps\HelpEntity;
use App\Modules\Helper\Factories\IHelpEditForm;

class EditPresenter extends BasePresenter{

	/** @var IHelpEditForm @inject */
	public $comEdit;

	/** @var HelpEntity */
	private $help;

	/** @var int @persistent */
	public $id;

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		$this->help = $this->orm->helps->getById($this->id);
	}

	public function actionDefault(
		$id = null)
	{
	}

	public function createComponentForm()
	{
		$f = $this->comEdit->create();
		$f->setHelp($this->help);

		$f->onSave[] = function (
			HelpEntity $e){
			$this->orm->flush();

			$this->flashSuccess('Nápověda uložena.');
			$this->redirect('Default:');
		};

		$f->onCancel[] = function (){
			$this->redirect('Default:');
		};

		return $f;
	}
}