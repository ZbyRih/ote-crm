<?php

namespace App\Models\Entities;

use App\Models\Orm\Address\AddressEntity;

class OdberatelEntity{

	/** @var string */
	public $identity;

	/** @var string */
	public $konIdent;

	/** @var AddressEntity */
	public $kontaktni;

	/** @var string */
	public $faIdent;

	/** @var AddressEntity */
	public $fakturacni;

	public function __construct()
	{
	}
}