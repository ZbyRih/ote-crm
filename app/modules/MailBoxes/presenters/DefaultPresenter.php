<?php
namespace App\Modules\MailBoxes\Presenters;

use App\Extensions\Components\NavTabs;
use App\Modules\MailBoxes\Factories\IBoxContentComponent;
use App\Models\Repositories\SettingsRepository;

class DefaultPresenter extends BasePresenter{

	/** @var IBoxContentComponent @inject */
	public $comBox;

	/** @var string @persistent */
	public $box;

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		$this->box = !$this->box ? SettingsRepository::BOX_OTE : $this->box;
	}

	public function actionDefault()
	{
	}

	public function createComponentBox()
	{
		$com = $this->comBox->create();
		$com->setBox($this->box);

		return $com;
	}

	public function createComponentBoxes()
	{
		$com = new NavTabs($this->dispatcher);
		$com->setItems([
			SettingsRepository::BOX_OTE => 'Ote Box',
			SettingsRepository::BOX_PLATBY => 'Platby Box'
		]);

		$com->setTab($this->box);
		$com->onChange[] = function (
			$tc,
			$tab){
			$this->box = $tab;
			$this->redirect('this');
		};

		return $com;
	}
}