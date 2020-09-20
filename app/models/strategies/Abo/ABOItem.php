<?php

namespace App\Models\ABO;

use App\Models\Values\AccountValue;

class ABOItem{

	public $amount;

	public $variableSym = 0;

	public $specSym = null;

	public $constSym = 0;

	/** @var AccountValue */
	public $srcAccount = null;

	/** @var AccountValue */
	public $destAccount = null;

	public $message = '';

	public function __construct()
	{
	}

	/**
	 * Set the amount to transfer
	 * @param int $float
	 */
	public function setAmount(
		$amount)
	{
		$this->amount = (int) ($amount * 100);
	}
}