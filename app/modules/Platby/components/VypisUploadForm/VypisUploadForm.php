<?php

namespace App\Modules\Platby\Components;

use App\Components\Controls\MAUploadControl;
use App\Extensions\Components\BaseComponent;
use App\Models\Services\FormFactoryService;
use App\Models\Enums\PlatbyImportEnums;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\RadioList;
use Nette\Utils\ArrayHash;

class VypisUploadForm extends BaseComponent{

	/** @var FormFactoryService */
	private $facForm;

	/** @var [] */
	public $onUpload = [];

	public function __construct(
		FormFactoryService $facForm)
	{
		$this->facForm = $facForm;
	}

	public function createComponentForm()
	{
		$f = $this->facForm->create();

		$c = (new MAUploadControl('Výpis z banky'))->setRequired();
		$f->addComponent($c, 'file');

		$c = new RadioList('Omezení',
			[
				PlatbyImportEnums::FILTER_NONE => 'žádné',
				PlatbyImportEnums::FILTER_IN => 'jen přípisy',
				PlatbyImportEnums::FILTER_OUT => 'jen odpisy'
			]);
		$c->itemLabelPrototype->class('radio-inline radio-styled');
		$c->setDefaultValue(PlatbyImportEnums::FILTER_NONE);
		$f->addComponent($c, 'limit');

		$f->addSubmit('save', 'Zpracovat');

		$f->onSuccess[] = [
			$this,
			'onSuccess'
		];

		return $f;
	}

	public function onSuccess(
		Form $form,
		ArrayHash $vals)
	{
		$this->onUpload($vals->file, $vals->limit);
	}
}