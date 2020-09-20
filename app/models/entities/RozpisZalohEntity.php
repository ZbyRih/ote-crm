<?php

namespace App\Models\Entities;

use App\Extensions\Utils\Strings;
use App\Models\Orm\OdberMists\OdberMistEntity;
use App\Components\Tisk\Zaloha\ZalohaSestavaEntry;

class RozpisZalohEntity{

	/** @var \DateTime */
	public $vystaveno;

	/** @var OdberatelEntity */
	public $odberatel;

	/** @var OdberMistEntity */
	public $odberMist;

	/** @var string */
	public $faSkupCis;

	/** @var \DateTime */
	public $do;

	/** @var \DateTime */
	public $od;

	/** @var ZalohaSestavaEntry[] */
	public $sestavy;

	public function __construct()
	{
		$this->odberatel = new OdberatelEntity();
	}

	public function getFileName()
	{
		return 'Zalohy_' . Strings::webalize($this->odberatel->faIdent) . '_' . ($this->faSkupCis ?: $this->odberMist->com) . '_' . $this->od->format('Y') . '_' . date(
			'm.d.') . 'pdf';
	}
}