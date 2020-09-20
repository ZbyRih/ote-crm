<?php

namespace App\Extensions\Utils;

use Carbon\Carbon;
use Exception;
use Nette\Utils\ArrayHash;

class DateTime extends \Nette\Utils\DateTime{

	const DATE_REGEXP = '#^\s*([1-9]|[12][0-9]|3[01])\s*\.\s*(0?[1-9]|1[0-2])\s*\. \s*([0-9]{4})\s*$#';

	/**
	 * @param string $s
	 * @return DateTime|false
	 */
	public static function parseDate(
		$s)
	{
		try{
			if(!($m = \Nette\Utils\Strings::match($s, static::DATE_REGEXP))){
				return false;
			}

			return new DateTime("{$m[3]}-{$m[2]}-{$m[1]}");
		}catch(Exception $e){
		}

		return false;
	}

	public static function parseHM(
		$v)
	{
		if(!$v){
			return 0;
		}

		$e = explode(':', $v);
		if(count($e) < 2){
			return 0;
		}

		return ((int) $e[0] * 3600) + ((int) $e[1] * 60);
	}

	/**
	 * get seconds from time format MM:SS '99:99'
	 * @param string $string
	 * @return int - time in seconds
	 */
	public static function parseTime(
		$string)
	{
		if(false !== ($ts = strtotime('1970-01-01 ' . $string . '+0'))){
			return $ts;
		}

		$parts = explode(':', $string);
		if(count($parts) < 2){
			return 0;
		}

		return (((int) $parts[0] * 60) + (int) $parts[1]) * 60;
	}

	/**
	 * @param int $t - seconds
	 */
	public static function formatInterval(
		$t)
	{
		$d = (int) gmdate('z', $t);
		return ArrayHash::from([
			'd' => $d,
			't' => gmdate('H:i:s', $t - ($d * 3600 * 24))
		], false);
	}

	public static function jsFormat(
		\DateTimeInterface $d)
	{
		if(!$d){
			return null;
		}
		return $d->format('Y-m-d H:i:s');
	}

	/**
	 * @param \DateTimeInterface $date
	 * @param \DateTimeInterface|string $time
	 * @return \Carbon\Carbon
	 */
	public static function combine(
		\DateTimeInterface $date,
		$time)
	{
		$date = Carbon::instance($date);
		$time = $time instanceof \DateTimeInterface ? Carbon::instance($time) : Carbon::parse($time);

		$date->hour = $time->hour;
		$date->minute = $time->minute;
		$date->second = $time->second;

		return $date;
	}

	/**
	 * @param DateTime $d1
	 * @param DateTime $d2
	 */
	public static function compare(
		$d1,
		$d2)
	{
		$d1 = clone $d1;
		$d2 = clone $d2;
		$d1->setTime(0, 0, 0);
		$d2->setTime(0, 0, 0);
		if($d1 == $d2){
			return 0;
		}else if($d1 < $d2){
			return -1;
		}else if($d1 > $d2){
			return 1;
		}
	}

	public static function now()
	{
		return new static();
	}

	public static function firstDayOfYear(
		$year)
	{
		$d = Carbon::now();
		$d->year = $year;
		$d->month = 1;
		$d->day = 1;
		$d->setTime(0, 0, 0);
		return $d;
	}

	public static function lastDayOfYear(
		$year)
	{
		$d = Carbon::now();
		$d->year = $year;
		$d->month = 12;
		$d->day = $d->daysInMonth;
		$d->setTime(0, 0, 0);
		return $d;
	}
}