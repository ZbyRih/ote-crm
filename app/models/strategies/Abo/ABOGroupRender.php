<?php

namespace App\Models\ABO;

class ABOGroupRender{

	/** @var ABOGroup */
	private $group;

	/** @var string */
	private $variant;

	public function __construct(
		ABOGroup $group,
		$variant = ABO::VARIANT_1)
	{
		$this->group = $group;
		$this->variant = $variant;
	}

	public function __toString()
	{
		if(!$this->group->dueDate){
			throw new \Exception('Due date not set');
		}

		$res = "2 ";
		if($this->variant == ABO::VARIANT_2){
			$res .= $this->group->srcAccount->toDelim() . ' ';
		}

		$res .= sprintf("%014d %s", $this->group->getAmount(), $this->group->dueDate->format('dmy'));
		$res .= "\r\n";

		foreach($this->group->items as $item){
			$r = new ABOItemRender($item, $this->variant);
			$res .= (string) $r;
		}

		$res .= "3 +\r\n";
		return $res;
	}
}