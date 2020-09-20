<?php

namespace App\Models\ABO;

class ABOItemRender{

	/** @var ABOItem */
	private $item;

	/** @var string */
	private $variant;

	public function __construct(
		ABOItem $item,
		$variant = ABO::VARIANT_1)
	{
		$this->item = $item;
		$this->variant = $variant;
	}

	public function __toString()
	{
		$res = "";

		if($this->variant == ABO::VARIANT_1){
			$res .= $this->item->srcAccount->toDelim() . " ";
		}

		$res .= sprintf("%s %d %s %s%04d ", $this->item->destAccount->toDelim(), $this->item->amount, $this->item->variableSym,
			$this->item->destAccount->toBank(), $this->item->constSym);

		$res .= ($this->item->specSym ? $this->item->specSym : ' ') . ' ';
		$res .= ($this->item->message ? substr('AV:' . iconv('UTF-8', 'CP1250', $this->item->message), 0, 38) : ' ');
		$res .= "\r\n";

		return $res;
	}
}