<?php

namespace App\Models\Services;

use Kdyby\Translation\Translator;
use App\Extensions\Components\BaseForm;
use App\Presenters\MaterialButtonsFormRenderer;

class FormFactoryService{

	/** @var Translator */
	private $translator;

	public function __construct(
		Translator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @return BaseForm
	 */
	public function create()
	{
		$f = new BaseForm();
		$f->setTranslator($this->translator);
		$f->setButtonRendererExtension(new MaterialButtonsFormRenderer());
		return $f;
	}
}