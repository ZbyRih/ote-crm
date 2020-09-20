<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Strategies\IZalohyDoFakturyStrategy;
use Tests\Utils\IntegrationTestCase;
use Tester\Assert;

class ZalohyDoFakturyStrategyTest extends IntegrationTestCase{

	/** @var IZalohyDoFakturyStrategy */
	private $fac;

	const KLIENT_ID = 97;

	const ODBER_MIST_ID = 236;

	const SKUP_ODBER_MIST_ID = 216;

	public function __construct()
	{
		$c = $this->getContainer();

		$this->fac = $c->getByType(IZalohyDoFakturyStrategy::class);
	}

	public function testNormal()
	{
		$from = new \DateTime('2018-01-01');
		$to = new \DateTime('2018-12-31');

		$str = $this->fac->create();
		$str->setFrom($from);
		$str->setTo($to);
		$str->setKlientId(self::KLIENT_ID);
		$str->setOmId(self::ODBER_MIST_ID);
		$ret = $str->create();

		Assert::true(count($ret) > 0);
	}

	public function testSkup()
	{
		$from = new \DateTime('2019-01-01');
		$to = new \DateTime('2019-01-31');

		$str = $this->fac->create();
		$str->setFrom($from);
		$str->setTo($to);
		$str->setKlientId(self::KLIENT_ID);
		$str->setOmId(self::SKUP_ODBER_MIST_ID);
		$ret = $str->create();

		Assert::true(count($ret) > 0);
	}
}

(new ZalohyDoFakturyStrategyTest())->run();