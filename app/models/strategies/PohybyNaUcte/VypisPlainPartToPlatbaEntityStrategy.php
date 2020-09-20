<?php

namespace App\Models\Strategies;

use App\Models\Orm\Platby\PlatbaEntity;
use Carbon\Carbon;

class VypisPlainPartToPlatbaEntityStrategy{

	/** @var string */
	private $dphCoef;

	/**
	 * @param number $dphCoef
	 */
	public function __construct(
		$dphCoef)
	{
		$this->dphCoef = (string) $dphCoef;
	}

	/**
	 * @param string $text
	 * @return PlatbaEntity|NULL
	 */
	public function convert(
		$text)
	{
		$date = $this->extract('datum zaúčtování', $text);

		if(!$date){
			return null;
		}

		$p = new PlatbaEntity();
		$p->dphCoef = $this->dphCoef;
		$p->platba = (float) $this->extract('částka', $text);
		$p->when = Carbon::createFromFormat('d.m.Y', $date);
		$p->fromCu = $this->extract('protiúčet', $text);
		$p->subject = $this->extract('název protiúčtu', $text);
		$p->ks = $this->extract('konstantní symbol', $text);
		$p->vs = $this->extract('variabilní symbol', $text);
		$p->ss = $this->extract('specifický symbol', $text);

		$matches = [];
		preg_match('/poznámka\: (.*)/is', $text, $matches);

		$p->msg = trim($matches[1]);

		return $p;
	}

	private function extract(
		$key,
		$subject)
	{
		$matches = [];
		if(preg_match('/' . $key . '\: (.*)\n/i', $subject, $matches)){
			return trim($matches[1]);
		}
		return null;
	}
}