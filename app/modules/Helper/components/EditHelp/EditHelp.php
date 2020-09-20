<?php
namespace App\Modules\Helper\Components;

use App\Extensions\App\User\Authorizator;
use App\Extensions\Components\BaseComponent;
use App\Extensions\Components\BaseForm;
use App\Models\Orm\Orm;
use App\Models\Orm\Faktury\FakturaEntity;
use App\Models\Orm\Helps\HelpEntity;
use App\Components\Controls\WswgTextAreaControl;
use App\Extensions\Utils\Arrays;

class EditHelp extends BaseComponent{

	/** @var Orm */
	private $orm;

	/** @var Authorizator */
	private $acl;

	/** @var HelpEntity */
	private $help;

	/** @var [] */
	public $onSave = [];

	/** @var [] */
	public $onCancel = [];

	public function __construct(
		Orm $orm,
		Authorizator $acl)
	{
		$this->acl = $acl;
		$this->orm = $orm;
	}

	/**
	 *
	 * @param FakturaEntity|NULL $fa
	 */
	public function setHelp(
		$help)
	{
		$this->help = $help;
	}

	public function createComponentEditForm()
	{
		$f = $this->createForm();
		$f->addHidden('id');

		$resources = Arrays::kvFromA($this->acl->getResources());

		$f->addSelect('resource', 'Modul', $resources)->setRequired();

		$wswg = new WswgTextAreaControl('Obsah');
		$f->addComponent($wswg, 'desc');

		$f->addSubmit('save', 'Uložit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([])->onClick[] = function (){
			$this->onCancel();
		};

		$f->onAnchor[] = function (
			$f){
			$f->setDefaults($this->help ? $this->help->toArray() : []);
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
		$help = $this->help ?: new HelpEntity();

		$help->desc = $vals['desc'];
		$help->resource = $vals['resource'];

		$this->orm->persist($help);

		$this->onSave($help);
	}
}