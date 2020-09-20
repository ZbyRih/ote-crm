<?php

namespace App\Models\Strategies;

use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Enums\PlatbyImportEnums;

class BankaFilterImportStrategy{

	/** @var string */
	private $type;

	/**
	 * @param string $type
	 */
	public function __construct(
		$type)
	{
		$this->type = $type;
	}

	/**
	 * @param PlatbaEntity[] $plas
	 * @return PlatbaEntity[]
	 */
	public function filter(
		$plas)
	{
		if($this->type != PlatbyImportEnums::FILTER_IN && $this->type != PlatbyImportEnums::FILTER_OUT){
			return $plas;
		}

		$plas = collection($plas);

		if($this->type == PlatbyImportEnums::FILTER_IN){
			$plas = $plas->filter(function (
				$v)
			{
				return $v->platba > 0;
			});
		}

		if($this->type == PlatbyImportEnums::FILTER_OUT){
			$plas = $plas->filter(function (
				$v)
			{
				return $v->platba < 0;
			});
		}

		return $plas->toArray();
	}
}