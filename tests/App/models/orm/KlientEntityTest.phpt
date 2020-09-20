<?php

namespace Tests\App\Models\Orm;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Orm\Orm;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

class KlientEntityTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	public function __construct()
	{
		$this->orm = $this->getContainer()->getService('nextras.orm.model');
	}

	/**
	 */
	public function testOrm()
	{
		$this->orm->klients->getById(1);

		Assert::true(true);
	}
}
(new KlientEntityTest())->run();