<?php

namespace App\Models\Strategies;

use Carbon\Carbon;

class SteppedDatesRangeStrategy{

	/** @var Carbon */
	private $from;

	/** @var Carbon */
	private $to;

	/** @var int */
	private $interval;

	public function __construct()
	{
	}

	/**
	 *
	 * @param \Carbon\Carbon $from
	 */
	public function setFrom(
		$from)
	{
		$this->from = Carbon::instance($from);
	}

	/**
	 *
	 * @param \Carbon\Carbon $to
	 */
	public function setTo(
		$to)
	{
		$this->to = Carbon::instance($to);
	}

	/**
	 *
	 * @param number $interval
	 */
	public function setInterval(
		$interval)
	{
		$this->interval = $interval;
	}

	public function generate()
	{
		$dtFrom = Carbon::instance($this->from);
		$dtFrom->month = 1;
		$dtFrom->day = 1;

		$dtTo = Carbon::instance($this->to);
		$dtTo->month = 12;
		$dtTo->day = 31;

		$stepping = new YearIntrvalStepStrategy();
		$step = $stepping->get($this->interval);

		$parts = $dtFrom->diffInMonths($dtTo) + 1;

		$ff = $this->from;

		$items = [];
		for($i = 0; $i < ($parts / $step); $i++){
			$tt = Carbon::instance($ff);
			$tt->addMonth($step - 1);
			$tt->day = $tt->daysInMonth;

			$items[] = [
				'od' => $ff,
				'do' => $tt
			];

			$ff = Carbon::instance($ff)->addMonth($step);
		}

		return collection($items)->filter(
			function (
				$v,
				$k){
				if($v['do'] < $this->from){
					return false;
				}
				if($v['od'] > $this->to){
					return false;
				}
				return true;
			})
			->map(
			function (
				$v,
				$k){
				if($v['od'] < $this->from){
					$v['od'] = $this->from;
				}
				if($v['do'] > $this->to){
					$v['do'] = $this->to;
				}
				return $v;
			})
			->toArray();
	}
}