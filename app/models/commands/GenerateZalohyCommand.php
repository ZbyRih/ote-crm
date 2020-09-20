<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\Orm\Orm;
use Carbon\Carbon;
use App\Models\Strategies\SteppedDatesRangeStrategy;
use App\Models\Orm\Zalohy\ZalohaEntity;

class GenerateZalohyCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var int */
	private $interval;

	/** @var Carbon */
	private $from;

	/** @var Carbon */
	private $to;

	/** @var int */
	private $klientId;

	/** @var int */
	private $omId;

	/** @var float */
	private $amount;

	/** @var string */
	private $vs;

	public function __construct(
		Orm $orm)
	{
		$this->orm = $orm;
	}

	/**
	 * @param number $interval
	 */
	public function setInterval(
		$interval)
	{
		$this->interval = $interval;
	}

	/**
	 * @param \DateTimeInterface $from
	 */
	public function setFrom(
		\DateTimeInterface $from)
	{
		$this->from = Carbon::instance($from);
	}

	/**
	 * @param \DateTimeInterface $to
	 */
	public function setTo(
		\DateTimeInterface $to)
	{
		$this->to = Carbon::instance($to);
	}

	/**
	 * @param number $klientId
	 */
	public function setKlientId(
		$klientId)
	{
		$this->klientId = $klientId;
	}

	/**
	 * @param number $omId
	 */
	public function setOmId(
		$omId)
	{
		$this->omId = $omId;
	}

	/**
	 * @param number $amount
	 */
	public function setAmount(
		$amount)
	{
		$this->amount = $amount;
	}

	/**
	 * @param string $vs
	 */
	public function setVs(
		$vs)
	{
		$this->vs = $vs;
	}

	public function execute()
	{
		$str = new SteppedDatesRangeStrategy();
		$str->setInterval($this->interval);
		$str->setFrom($this->from);
		$str->setTo($this->to);

		foreach($str->generate() as $i){
			$zal = new ZalohaEntity();
			$zal->od = $i['od'];
			$zal->do = $i['do'];
			$zal->klientId = $this->klientId;
			$zal->odberMistId = $this->omId;
			$zal->vyse = $this->amount;
			$zal->vs = $this->vs;

			$this->orm->persist($zal);
		}
	}
}