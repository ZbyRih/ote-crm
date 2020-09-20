<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
namespace App\Components\Controls;

use Nette;
use Nette\Utils\Html;
use Nette\Forms\Controls\UploadControl;

/**
 * Material admin styled uplaod control
 */
class MAUploadControl extends UploadControl{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\BaseControl::getControl()
	 */
	public function getControl()
	{
		$this->setOption('rendered', true);

		$name = $this->getHtmlName();

		$el = Html::el('div')->class('input-group')
			->addHtml(
			Html::el('span')->class('input-group-btn')
				->addHtml(
				Html::el('span')->class('btn btn-default btn-file btn-primary')
					->addText('Vybrat ...')
					->addHtml(
					Html::el('input')->addAttributes(
						[
							'type' => 'file',
							'name' => $name,
							'id' => $this->getHtmlId(),
							'class' => 'form-control'
						]))))
			->addHtml(
			Html::el('input')->addAttributes(
				[
					'type' => 'text',
					'class' => 'form-control',
					'readonly' => '',
					'name' => $name . '_name',
					'value' => $this->getValue()
				]));

		return $el;
	}

	/**
	 *
	 * @return static
	 * @internal
	 */
	public function setValue(
		$value)
	{
		return $this->value = $value;
	}
}