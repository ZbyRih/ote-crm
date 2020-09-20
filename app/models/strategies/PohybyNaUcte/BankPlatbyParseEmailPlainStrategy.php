<?php

namespace App\Models\Strategies;

use Nette\SmartObject;
use React\Promise\Deferred;
use React\Promise\Promise;

class BankPlatbyParseEmailPlainStrategy{

	use SmartObject;

	const SUBJECT = 'CEB_Info:_Zaúčtování_platby';

	/** @var [] */
	public $onFail = [];

	public function __construct()
	{
	}

	/**
	 * @param string $subject
	 * @param string $text
	 * @return Promise
	 */
	public function parse(
		$subject,
		$text)
	{
		$deferred = new Deferred();

		if(empty($text)){
			$deferred->reject('prázdný email');
			return $deferred->promise();
		}

		$subject = str_replace(' ', '_', $subject);

		if(mb_stripos($subject, self::SUBJECT) === false){
			$deferred->reject('nesedí předmět');
			return $deferred->promise();
		}

		$parts = explode('dne ', $text);
		if(count($parts) < 1){
			$deferred->reject('nenalezen datum');
			return $deferred->promise();
		}

		$deferred->resolve($parts);
		return $deferred->promise();
	}
}