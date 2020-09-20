<?php

namespace Tests\App\Models\Services;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Orm\Orm;
use App\Models\Strategies\Doklad\CreateDokladStrategy;
use App\Models\Strategies\Doklad\CreateDokladEntityStrategy;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

class DokladServiceTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	const PLATBA_ID = 2436;

	const DPH_COEF = 0.1458;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
	}

	public function testCreate()
	{
		$pla = $this->orm->platby->getById(self::PLATBA_ID);

		$str = new CreateDokladStrategy();
		$str->setCislo(function ()
		{
			return 'test';
		});
		$pla = $str->create($pla);

		$str = new CreateDokladEntityStrategy();
		$str->setOrm($this->orm);
		$doklad = $str->create($pla);

		Assert::true(true);
	}
}

(new DokladServiceTest())->run();