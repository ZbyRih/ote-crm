<?php

namespace App\Models\Strategies\Fakturace;

use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Orm\Orm;
use App\Models\Orm\Klients\KlientEntity;
use App\Models\Values\AccountValue;

class KlientsCisloUctuStrategy{

	/** @var Orm */
	private $orm;

	public function __construct(
		Orm $orm)
	{
		$this->orm = $orm;
	}

	public function get(
		$ids)
	{
		$cus = new ArrayHash();

		$uniqIds = array_unique($ids);

		$klis = $this->orm->klients->findById($uniqIds);

		collection($klis)->indexBy('klientId')
			->map(function (
			KlientEntity $v)
		{
			try{
				return new AccountValue($v->klientDetailId->cu);
			}catch(\InvalidArgumentException $e){
			}
			return null;
		})
			->filter(function (
			$v)
		{
			return (bool) $v;
		})
			->each(function (
			$v,
			$k) use (
		$cus)
		{
			$cus[$k] = $v;
		})
			->compile();

		return $cus;
	}
}