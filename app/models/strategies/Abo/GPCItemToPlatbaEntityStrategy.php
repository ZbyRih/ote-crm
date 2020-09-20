<?php

namespace App\Models\Strategies\ABO;

use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\ABO\GPCItem;
use App\Models\Values\AccountValue;
use App\Models\ABO\GPCBase;

class GPCItemToPlatbaEntityStrategy{

	/** @var string */
	private $dphCoef;

	public function __construct(
		$dphCoef)
	{
		$this->dphCoef = (string) $dphCoef;
	}

	/**
	 * @param GPCItem $item
	 * @return PlatbaEntity
	 */
	public function convert(
		GPCItem $item)
	{
		try{
			$p = new PlatbaEntity();
			$p->dphCoef = $this->dphCoef;

			$from = new AccountValue(ltrim($item->OffsetAccount, '0') . '/' . $item->BankCode);

			$p->platba = ($item->Code == GPCBase::CODE_DEBET ? -1 : 1) * (float) $item->Value;
			$p->when = $item->DueDate;
			$p->fromCu = (string) $from;
			$p->subject = trim($item->ClientName);
			$p->ks = $item->ConstantSymbol;
			$p->vs = ltrim($item->VariableSymbol, 0);
			$p->ss = ltrim($item->SpecificSymbol, 0);
		}catch(\InvalidArgumentException $e){
			return null;
		}

		return $p;
	}
}