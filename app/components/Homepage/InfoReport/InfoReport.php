<?php

namespace App\Components\Homepage;

use App\Extensions\Components\BaseComponent;
use App\Models\Enums\InfoEnums;
use App\Models\Orm\Info\InfoEntity;
use App\Components\InfoReport as Report;

class InfoReport extends BaseComponent{

	/** @var string */
	private $type;

	/** @var InfoEntity */
	private $info;

	/**
	 * @param string $type
	 */
	public function setType(
		$type)
	{
		$this->type = $type;
	}

	/**
	 * @param InfoEntity $info
	 */
	public function setInfo(
		InfoEntity $info = null)
	{
		$this->info = $info;
	}

	public function createComponentReport()
	{
		$rep = new Report();

		if(!$this->info){
			return $rep;
		}

		$date = $this->info->created->format('m.d. Y H:m');
		$name = InfoEnums::$TYPE_LABELS[$this->info->type];

		$rep->setHeader('Staženy ' . $name . ' ' . $date);
		$rep->setItems(json_decode($this->info->data));

		return $rep;
	}
}