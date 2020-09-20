<?php

namespace App\Models\ABO;

use App\Models\Values\AccountValue;

class ABOGroup{

	/** @var AccountValue */
	public $srcAccount = null;

	/** @var \DateTime */
	public $dueDate = null;

	/** @var ABOItem[] */
	public $items = [];

	/**
	 * Set date of the execution
	 * @param $date
	 */
	public function setDate(
		\DateTime $date = null)
	{
		$this->dueDate = $date == null ? new \DateTime() : $date;
	}

	/**
	 * source account
	 * @param AccountValue $account
	 */
	public function setSrcAccount(
		AccountValue $account)
	{
		$this->srcAccount = $account;
	}

	/**
	 *
	 * @param ABOItem $item
	 */
	public function addItem(
		ABOItem $item)
	{
		$this->items[] = $item;
	}

	/**
	 * Get the amount in halere
	 * @return int
	 */
	public function getAmount()
	{
		$res = 0;
		foreach($this->items as $item){
			$res += $item->amount;
		}
		return $res;
	}
}