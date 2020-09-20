<?php

namespace App\Extensions\Components;

use Kdyby\Translation\Translator;
use Ublaboo\DataGrid\DataGrid;

/**
 * @property BasePresenter $presenter
 */
abstract class BaseGridBoo extends DataGrid{

	const BUTTON_ICON = 'btn btn-icon-toggle ink-reaction';

	const BUTTON_TEXT = 'btn btn-xs';

	use TBaseGridPresenterExtension;

	/**
	 * @param Translator $translator
	 */
	function __construct(
		Translator $translator)
	{
		parent::__construct();

		$this->setTranslator($translator);
	}

	abstract protected function build();

	public function attached(
		$presenter)
	{
		parent::attached($presenter);
		$this->setTemplateFile(__DIR__ . '/datagrid-ext.latte');
		$this->build();
	}

	public function getDSCallback(
		$fce)
	{
		return [
			$this->getDataSource(),
			$fce
		];
	}

	/**
	 * {@inheritdoc}
	 * @see \Ublaboo\DataGrid\DataGrid::setDataSource()
	 */
	public function setDataSource(
		$source)
	{
		if($source instanceof DataSourceGridBoo){
			$source->create();
		}
		return parent::setDataSource($source);
	}
}