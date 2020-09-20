<?php

namespace Tests\App\Models\Components;

use App\Extensions\ITiskComponent;
use App\Components\ITiskDanDoklad;
use App\Models\Orm\Orm;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use Tests\Utils\TPresenterMock;
use App\Models\Strategies\Doklad\CreateDokladStrategy;
use App\Models\Strategies\Doklad\CreateDokladEntityStrategy;

require_once __DIR__ . '/../../bootstrap.php';

class TiskComponentTest extends IntegrationTestCase{

	use TPresenterMock;

	/** @var Orm */
	private $orm;

	/** @var ITiskComponent */
	private $facTisk;

	/** @var ITiskDanDoklad */
	private $facDoklad;

	const PLATBA_ID = 2436;

	const DPH_COEF = 0.1458;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->init($c);
		$this->orm = $c->getByType(Orm::class);
		$this->facTisk = $c->getByType(ITiskComponent::class);
		$this->facDoklad = $c->getByType(ITiskDanDoklad::class);
	}

	public function setUp()
	{
	}

	public function tearDown()
	{
	}

	public function testToString()
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

		$tdd = $this->facDoklad->create();
		$tdd->setDoklad($doklad);

		$tisk = $this->facTisk->create();
		$tisk->addPage($tdd);

		$html = $this->catchTemplateRender($tisk);

		Assert::true(!empty($html));
	}
}

(new TiskComponentTest())->run();