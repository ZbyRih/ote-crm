<?php

namespace App\Models\ABO;

class GPCParser{

	/** @var  */
	private $typeFce;

	public function __construct()
	{
		$parseItem = new GPCParseItem();
		$parseReport = new GPCParseReport();

		$this->typeFce[GPCBase::TYPE_REPORT] = [
			$parseReport,
			'parse'
		];

		$this->typeFce[GPCBase::TYPE_ITEM] = [
			$parseItem,
			'parse'
		];
	}

	public function parse(
		$line)
	{
		$line = ' ' . $line;
		$type = substr($line, 1, 3);

		if(!array_key_exists($type, $this->typeFce)){
			return null;
		}

		return $this->typeFce[$type]($line);
	}
}