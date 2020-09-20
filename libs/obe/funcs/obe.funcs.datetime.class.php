<?php

class OBE_DateTime{

	const USER_FORMAT = 'd.m. Y';

	const DB_FORMAT = 'Y-m-d';

	const INFINITE = '31.12. 9999';

	public static function getWeekStartEnd(
		$week,
		$year,
		$dateF = 'd. m. Y',
		$thursday_rule = TRUE)
	{
		/* zjisteni dne v tydnu, kdy je 1. 1. daneho roku */
		$year_start = date('w', mktime(0, 0, 0, 1, 1, $year));
		if($year_start == 0){ //if nedele
			$year_start = 7;
		}
		/* ----- */
		if($thursday_rule && $year_start > 4){ //if kontrolovat "pravidlo ctvrtku" A tyden zacina v patek, sobotu, ci nedeli
			$week++;
		}

		$date_start = date($dateF, mktime(0, 0, 0, 1, ($week * 7) - 6 - $year_start + 1, $year));
		$date_end = date($dateF, mktime(0, 0, 0, 1, ($week * 7) - $year_start + 1, $year));
		return [
			'start' => $date_start,
			'end' => $date_end
		];
	}

	/**
	 * převede DateTime 2015' na db date 'YYYY-MM-DD'
	 * @param DateTime $date
	 * @param string $date
	 */
	public static function convertDTToDB(
		$date)
	{
		if($date instanceof DateTime){
			return $date->format('Y-m-d H:i:s');
		}
		return $date;
	}

	/**
	 * převede humane date '11.08.
	 * 2015' na db date 'YYYY-MM-DD'
	 * @param string $date
	 */
	public static function convertToDB(
		$date)
	{
		if($date instanceof DateTime){
			return $date->format('Y-m-d H:i:s');
		}
		if($date){
			$chunks = explode('.', $date);
			if(count($chunks) < 3){
				throw new OBE_Exception('Formát datumu ' . $date . ' pro fci convertToDB není správný');
			}
			return trim($chunks[2]) . '-' . trim($chunks[1]) . '-' . trim($chunks[0]);
		}
		return NULL;
	}

	public static function convertFromDB(
		$date)
	{
		if($date){
			$datetime = explode(' ', $date);
			$date = explode('-', $datetime[0]);
			return $date[2] . '.' . $date[1] . '. ' . $date[0];
		}
		return NULL;
	}

	public static function toDateUsr(
		$str)
	{
		$chunks = explode('.', $str);
		return strtotime(trim($chunks[0]) . '-' . trim($chunks[1]) . '-' . trim($chunks[2]));
	}

	public static function toDT(
		$str)
	{
		$chunks = explode('.', $str);
		return new DateTime(trim($chunks[0]) . '-' . trim($chunks[1]) . '-' . trim($chunks[2]));
	}

	public static function toDateDB(
		$str)
	{
		return strtotime($str);
	}

	public static function timeToUsr(
		$date)
	{
		return date(self::USER_FORMAT, $date);
	}

	public static function getMonth(
		$date = NULL)
	{
		if($date){
			$chunks = explode('.', $date);
			return (int) $chunks[1];
		}
		return date('m');
	}

	public static function getMonthDB(
		$date = NULL)
	{
		if($date){
			$datetime = explode(' ', $date);
			$chunks = explode('-', $datetime[0]);
			return (int) $chunks[1];
		}
		return date('m');
	}

	public static function getYear(
		$date = NULL)
	{
		if($date){
			$chunks = explode('.', $date);
			return (int) $chunks[2];
		}
		return date('Y');
	}

	public static function getYearDB(
		$date = NULL)
	{
		if($date){
			$datetime = explode(' ', $date);
			$chunks = explode('-', $datetime[0]);
			return (int) $chunks[0];
		}
		return date('Y');
	}

	public static function now(
		$dDays = NULL)
	{
		if($dDays){
			return date(self::USER_FORMAT, strtotime($dDays . ' day'));
		}
		return date(self::USER_FORMAT);
	}

	public static function nowDB(
		$dDays = NULL)
	{
		if($dDays){
			return date(self::DB_FORMAT, strtotime($dDays . ' day'));
		}
		return date(self::DB_FORMAT);
	}

	public static function getLastDayOfMonth(
		$year,
		$month)
	{
		return date('t', strtotime($year . '-' . $month . '-1'));
	}

	public static function addMonth(
		&$month,
		&$year,
		$step = 1)
	{
		$month += $step;
		if($month > 12){
			$year += floor($month / 12);
			$month = $month % 12;
		}
	}

	public static function addYearDB(
		$db_date,
		$yn = 1)
	{
		$e = explode('-', $db_date);
		return ($e[0] + $yn) . '-' . $e[1] . '-' . $e[2];
	}

	/**
	 * @param string $db_date
	 * @return DateTime
	 */
	public static function getDBToDate(
		$db_date)
	{
		$e = explode('-', $db_date);
		return new DateTime($db_date);
	}

	public static function modifyClone(
		$d,
		$mod)
	{
		$n = clone $d;
		$n->modify($mod);
		return $n;
	}
}