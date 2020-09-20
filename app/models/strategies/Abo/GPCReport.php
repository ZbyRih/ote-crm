<?php

namespace App\Models\ABO;

/**
 * @property string $AccountNumber
 * @property string $AccountName
 * @property \DateTime $OldBalanceDate
 * @property float $OldBalanceValue
 * @property float $NewBalanceValue
 * @property float $DebitValue
 * @property float $CreditValue
 * @property int $SequenceNumber
 * @property \DateTime $Date
 * @property string $CheckSum
 *
 */
class GPCReport extends GPCBase{

	public function __construct()
	{
		$this->Type = GPCBase::TYPE_REPORT;
	}
}