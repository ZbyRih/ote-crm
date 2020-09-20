<?php

namespace App\Models\ABO;

/**
 * @property string $AccountNumber
 * @property string $OffsetAccount
 * @property string $RecordNumber
 * @property string $Value
 * @property string $Code
 * @property int $VariableSymbol
 * @property string $BankCode
 * @property int $ConstantSymbol
 * @property int $SpecificSymbol
 * @property string $Valut
 * @property string $ClientName
 * @property string $CurrencyCode
 * @property \DateTime $DueDate
 * @property string $CheckSum
 *
 */
class GPCItem extends GPCBase{

	public function __construct()
	{
		$this->Type = GPCBase::TYPE_ITEM;
	}
}
