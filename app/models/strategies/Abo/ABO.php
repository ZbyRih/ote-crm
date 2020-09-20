<?php

namespace App\Models\ABO;

class ABO{

	const TYPE_UHRADA = 1501;

	const TYPE_INKASO = 1502;

	const VARIANT_1 = 1;

	const VARIANT_2 = 2;

	/** @var int typ uhrady */
	private $type = self::TYPE_UHRADA;

	/** @var int - nepouziva se */
	private $souborNumber = 0;

	/** @var int - nepouziva se */
	private $pobocka = 0;

	/** @var int - cislo banky*/
	private $bank = null;

	/** @var ABOGroup[] - polozky */
	private $groups = [];

	private $variant = self::VARIANT_1;

	public function __construct()
	{
	}

	/**
	 *
	 * @param string $type
	 */
	public function setType(
		$type)
	{
		$this->type = $type;
	}

	/**
	 *
	 * @param string $type
	 */
	public function setVariant(
		$variant)
	{
		$this->variant = $variant;
	}

	/**
	 *
	 * @param number $number
	 */
	public function setSouborNumber(
		$number)
	{
		$this->souborNumber = $number;
	}

	/**
	 *
	 * @param number $bankDepartment
	 */
	public function setPobocka(
		$bankDepartment)
	{
		$this->pobocka = $bankDepartment;
	}

	/**
	 *
	 * @param number $bank
	 */
	public function setBank(
		$bank)
	{
		$this->bank = $bank;
	}

	public function addGroup(
		ABOGroup $group)
	{
		$this->groups[] = $group;
	}

	/**
	 * Generate string
	 * @return string
	 */
	public function create()
	{
		if(!$this->type){
			throw new \Exception('Typ není definován.');
		}

		if(!$this->bank){
			throw new \Exception('Kód banky není definován.');
		}

		$res = "UHL1\r\n";
		$res .= sprintf("1 %04d %03d%03d %04d\r\n", $this->type, $this->souborNumber, $this->pobocka, $this->bank);

		foreach($this->groups as $group){
			$r = new ABOGroupRender($group, $this->variant);
			$res .= (string) $r;
		}

		$res .= "5 +\r\n";
		return $res;
	}
}