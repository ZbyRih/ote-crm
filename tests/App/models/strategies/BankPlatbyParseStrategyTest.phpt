<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Orm\Orm;
use App\Models\Strategies\BankPlatbyParseEmailPlainStrategy;
use Nette\Utils\AssertionException;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

class BankPlatbyParseStrategyTest extends IntegrationTestCase{

	public function __construct()
	{
		$this->getContainer()->getByType(Orm::class);
	}

	public function testIncome()
	{
		$str = new BankPlatbyParseEmailPlainStrategy();

		$text = file_get_contents(__DIR__ . '/data/platby-in.eml');
		$str->parse(BankPlatbyParseEmailPlainStrategy::SUBJECT, $text)->then(function (
			$result)
		{
		});

		$text = file_get_contents(__DIR__ . '/data/platby-out.eml');
		$str->parse(BankPlatbyParseEmailPlainStrategy::SUBJECT, $text)->then(function (
			$result)
		{
		});

		Assert::true(true);
	}

	public function testSubject()
	{
		Assert::throws(
			function ()
			{
				$str = new BankPlatbyParseEmailPlainStrategy();
				$str->parse('Náhodný subject', 'a')
					->then(function ()
				{
				})
					->otherwise(function ()
				{
					throw new AssertionException();
				})
					->done();
			}, AssertionException::class);

		Assert::throws(
			function ()
			{
				$str = new BankPlatbyParseEmailPlainStrategy();
				$str->parse('Náhodný subject', '')
					->then(function ()
				{
				})
					->otherwise(function ()
				{
					throw new AssertionException();
				})
					->done();
			}, AssertionException::class);
	}
}

(new BankPlatbyParseStrategyTest())->run();