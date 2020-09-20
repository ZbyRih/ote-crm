<?php

namespace Tests\App\Models\Strategies;

use App\Models\Orm\Orm;
use App\Models\Strategies\EmailPlainPartToPlatbaEntityStrategy;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

require_once __DIR__ . '/../../../bootstrap.php';

class PlainPlatbaToEntityStrategyTest extends IntegrationTestCase{

	public function __construct()
	{
		$this->getContainer()->getByType(Orm::class);
	}

	public function testConvert()
	{
		$text = file_get_contents(__DIR__ . '/data/single.txt');

		$str = new EmailPlainPartToPlatbaEntityStrategy(0.1754);
		$p = $str->get($text);

		Assert::true($p !== null, '$p je NULL');
		Assert::same(-4470.0, $p->platba);
	}
}

(new PlainPlatbaToEntityStrategyTest())->run();