<?php

namespace Tests\App\Models\Components;

require_once __DIR__ . '/../../bootstrap.php';

use App\Components\ITiskDanDoklad;
use App\Models\Orm\Orm;
use App\Models\Strategies\Doklad\CreateDokladStrategy;
use App\Models\Strategies\Doklad\CreateDokladTiskStrategy;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use Tests\Utils\TPresenterMock;
use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Strategies\Doklad\CreateDokladEntityStrategy;

class TiskDanDokladTest extends IntegrationTestCase{

	use TPresenterMock;

	/** @var Orm */
	private $orm;

	/** @var ITiskDanDoklad */
	private $facDoklad;

	/** @var PlatbaEntity */
	private $pla;

	const PLATBA_ID = 2436;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->init($c);

		$this->orm = $c->getByType(Orm::class);
		$this->facDoklad = $c->getByType(ITiskDanDoklad::class);
	}

	public function setUp()
	{
	}

	public function tearDown()
	{
	}

	public function testCreate()
	{
		$this->pla = $this->orm->platby->getById(self::PLATBA_ID);

		$str = new CreateDokladStrategy();
		$str->setCislo(function ()
		{
			return $this->pla->hasDoklad() ? $this->pla->doklad->cislo : ' - náhled - ';
		});
		$pla = $str->create($this->pla);

		$str = new CreateDokladEntityStrategy();
		$str->setOrm($this->orm);
		$doklad = $str->create($pla);

		$tdd = $this->facDoklad->create();
		$tdd->setDoklad($doklad);

		$html = $this->catchTemplateRender($tdd);

		Assert::true(!empty($html));
	}
}

(new TiskDanDokladTest())->run();