<?php
namespace App\Modules\Faktury\Components;

use App\Extensions\Components\BaseComponent;
use App\Extensions\Components\BaseForm;
use App\Models\Orm\Faktury\FakturaEntity;
use App\Models\Orm\Orm;

class EditGenerated extends BaseComponent{

	/** @var Orm */
	private $orm;

	/** @var FakturaEntity */
	private $fa;

	/** @var int */
	private $userId;

	/** @var [] */
	public $onSave = [];

	/** @var [] */
	public $onCancel = [];

	public function __construct(
		Orm $orm)
	{
		$this->orm = $orm;
	}

	/**
	 *
	 * @param FakturaEntity $fa
	 */
	public function setFa(
		FakturaEntity $fa)
	{
		$this->fa = $fa;
	}

	public function setUserId(
		$usrId)
	{
		$this->userId = $usrId;
	}

	public function createComponentEditForm()
	{
		$f = $this->createForm();
		$f->addHidden('id');

		$f->addFloat('nakupMwh', 'Nákup MWh')->setDefaultValue(0.0);
		$f->addDate('uhrazenoDzp', 'Uhrazena dan z plynu dne')->setDefaultValue(new \DateTime());

		$f->addSubmit('save', 'Uložit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([])->onClick[] = function (){
			$this->onCancel();
		};

		$f->onAnchor[] = function (
			$f){
			$f->setDefaults($this->fa ? $this->fa->toArray() : []);
		};

		$f->onValidate[] = [
			$this,
			'onValidate'
		];

		$f->onSuccess[] = [
			$this,
			'onSuccess'
		];

		return $f;
	}

	public function onValidate(
		BaseForm $f,
		$vals)
	{
	}

	/**
	 *
	 * @param BaseForm $form
	 * @param array $vals
	 */
	public function onSuccess(
		BaseForm $form,
		$vals)
	{
		$this->fa->nakupMwh = $vals['nakupMwh'];
		$this->fa->uhrazenoDzp = $vals['uhrazenoDzp'];

		$this->orm->persistAndFlush($this->fa);

		$this->onSave($this->fa);
	}
}