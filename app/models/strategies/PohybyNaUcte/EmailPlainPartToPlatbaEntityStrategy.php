<?php

namespace App\Models\Strategies;

use App\Models\Orm\Platby\PlatbaEntity;
use Carbon\Carbon;

class EmailPlainPartToPlatbaEntityStrategy{

	/** @var float */
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
	 * @return NULL|PlatbaEntity
	 */
	public function get(
		$text)
	{
		$date = [];

		if(!preg_match('/^(\d{1,2}\.\d{1,2}\.\d{4})/', $text, $date)){
			return null;
		}

		$castka = $this->extract('Částka', $text);
		$castka = strtr($castka, [
			'CZK' => '',
			"\x20" => '',
			" " => '',
			',' => '.'
		]);

		$p = new PlatbaEntity();
		$p->platba = $castka;
		$p->when = Carbon::createFromFormat('d.m.Y', $date[0]);
		$p->fromCu = $this->extract('Účet protistrany', $text);
		$p->subject = $this->extract('Název protistrany', $text);
		$p->ks = $this->extract('Konstantní symbol', $text);
		$p->vs = $this->extract('Variabilní symbol', $text);
		$p->ss = $this->extract('Specifický symbol', $text);
		$p->msg = $this->extract('Zpráva příjemci', $text);
		$p->dphCoef = $this->dphCoef;

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