<?php

namespace App\Modules\Activity\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;
use App\Models\Selections\UserSelection;
use App\Extensions\Utils\Arrays;
use App\Models\Backward\LegacyMap;

class ActivityGrid extends BaseGridBoo{

	/** @var UserSelection */
	private $selUser;

	public function __construct(
		Translator $translator,
		UserSelection $selUser)
	{
		parent::__construct($translator);
		$this->selUser = $selUser;
	}

	protected function build()
	{
		$users = $this->selUser->getNames();

		$legacyModules = LegacyMap::getResource();

		$this->addColumnDateTime('kdy', 'Vloženo', 'kdy')
			->setFormat('j.n. Y H:i:s')
			->setSortable()
			->setFilterDate();
		$this->addColumnText('user_id', 'Uživatel')
			->setReplacement($users)
			->setFilterSelect(Arrays::shiftNull('- vše -', $users));

		$this->addColumnText('modul', 'Modul')
			->setRenderer(
			function (
				$row) use (
			$legacyModules){
				if($row->resource){
					return $this->translator->translate('app.menu.' . $row->resource);
				}else{
					if(array_key_exists($row->modul, $legacyModules)){
						return $this->translator->translate('app.menu.' . $legacyModules[$row->modul]);
					}
					return 'uknown';
				}
			})
			->setFilterText();

		$this->addColumnText('aktivita', 'Událost');
		$this->addColumnText('popis', 'Popis');
		$this->addColumnText('master', 'Detail');

		$this->setDefaultSort([
			'kdy' => 'DESC'
		]);
	}
}