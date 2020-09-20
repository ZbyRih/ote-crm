<?php

namespace App\Models\Entities;

use App\Extensions\Utils\Strings;
use App\Models\Orm\OdberMists\OdberMistEntity;

class DokladEntity{

	/** @var string */
	public $vs;

	/** @var string */
	public $cislo;

	/** @var \DateTime */
	public $vystaveno;

	/** @var bool */
	public $fakSkup = false;

	public $dphCoef = 0;

	/** @var OdberatelEntity */
	public $odberatel;

	/** @var PlatbaEntity */
	public $platba;

	/** @var OdberMistEntity */
	public $odberMist;

	public function __construct()
	{
		$this->platba = new PlatbaEntity();
		$this->odberatel = new OdberatelEntity();
	}

	public function getFileName()
	{
		return 'Doklad_' . Strings::webalize($this->odberatel->faIdent) . '_' . $this->odberMist->com . '_' . date('Y') . '_' . date('m') . '.' . date('d') . '.pdf';
	}
}