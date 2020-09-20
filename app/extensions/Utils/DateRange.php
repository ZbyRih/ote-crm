<?php

namespace App\Models\Core;

use Carbon\Carbon;

class InvalidDateRangeException extends \Exception{
}

/**
 * @property-read Carbon $od
 * @property-read Carbon $do
 *
 */
final class DateRange{

	/** @var Carbon */
	private $od;

	/** @var Carbon */
	private $do;

	public function __construct(
		\DateTimeInterface $od,
		\DateTimeInterface $do)
	{
		if($od > $do){
			throw new InvalidDateRangeException('od nemůže být větší než do.');
		}

		$this->od = $od instanceof Carbon ? clone $od : Carbon::instance($od);
		$this->do = $do instanceof Carbon ? clone $do : Carbon::instance($do);
	}

	public function __clone()
	{
		$this->od = clone $this->od;
		$this->do = clone $this->do;
	}

	public function __get(
		$key)
	{
		return clone $this->$key;
	}

	public function flip()
	{
		return new static($this->do, $this->od);
	}

	public function toArray()
	{
		return [
			'od' => clone $this->od,
			'do' => clone $this->do
		];
	}

	public function intersection(
		DateRange $dr)
	{
		$drOd = $dr->od;
		$drDo = $dr->do;

		if($this->od > $drDo || $this->do < $drOd){
			return null;
		}

		$od = $this->od < $drOd ? $drOd : $this->od;
		$do = $this->do > $drDo ? $drDo : $this->do;

		return new DateRange($od, $do);
	}
}