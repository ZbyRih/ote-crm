<?php

namespace App\Models\Values;

class PlatbaValue{

	/** @var float */
	public $zaklad;

	/** @var float */
	public $dph;

	/** @var float */
	public $suma;

	/** @var float */
	public $dphKoef;

	public function __construct(
		$suma,
		$dph,
		$zaklad = false)
	{
		$suma = (float) $suma;
		$this->dphKoef = $dph = (float) $dph;

		if($zaklad){
			$this->zaklad = $suma;
			$this->dph = ($suma * $dph) - $suma;
			$this->suma = $suma * $dph;
		}else{
			$this->zaklad = $suma - ($suma * $dph);
			$this->dph = $suma * $dph;
			$this->suma = $suma;
		}
	}
}