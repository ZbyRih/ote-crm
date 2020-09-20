<?php

namespace Tests\App\Models\Components;

require_once __DIR__ . '/../../bootstrap.php';

use App\Components\ITiskRozpisZaloh;
use App\Models\Strategies\ICreateRozpisZalohEntityStrategy;
use Tests\Utils\IntegrationTestCase;
use Tests\Utils\TPresenterMock;
use App\Models\DTO\TiskRospisZalohDTO;
use Tester\Assert;

class TiskZalohaTest extends IntegrationTestCase{

	use TPresenterMock;

	/** @var ITiskRozpisZaloh */
	private $facZaloha;

	/** @var ICreateRozpisZalohEntityStrategy */
	private $facRozpis;

	const YEAR = 2018;

	const KLIENT_ID = 97;

	const ODBER_MIST_ID = 236;

	const SKUP_ODBER_MIST_ID = 216;

	const FA_SKUP_ID = 1;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->facZaloha = $c->getByType(ITiskRozpisZaloh::class);
		$this->facRozpis = $c->getByType(ICreateRozpisZalohEntityStrategy::class);
	}

	public function testCreateOM()
	{
		$params = new TiskRospisZalohDTO([
			'year' => self::YEAR,
			'klientId' => self::KLIENT_ID,
			'omId' => self::ODBER_MIST_ID,
			'fakSkupId' => null
		]);

		$str = $this->facRozpis->create();
		$str->setParams($params);
		$zalohy = $str->create();

		$tdd = $this->facZaloha->create();
		$tdd->setZalohy($zalohy);

		$out = $this->catchTemplateRender($tdd);
		Assert::true(!empty($out));
	}

	public function testCreateOMInFS()
	{
		$params = new TiskRospisZalohDTO([
			'year' => self::YEAR,
			'klientId' => self::KLIENT_ID,
			'omId' => self::SKUP_ODBER_MIST_ID,
			'fakSkupId' => null
		]);

		$str = $this->facRozpis->create();
		$str->setParams($params);
		$zalohy = $str->create();

		$tdd = $this->facZaloha->create();
		$tdd->setZalohy($zalohy);

		$out = $this->catchTemplateRender($tdd);
		Assert::true(!empty($out));
	}

	public function testCreateFS()
	{
		$params = new TiskRospisZalohDTO([
			'year' => self::YEAR,
			'klientId' => self::KLIENT_ID,
			'omId' => null,
			'fakSkupId' => self::FA_SKUP_ID
		]);

		$str = $this->facRozpis->create();
		$str->setParams($params);
		$zalohy = $str->create();

		$tdd = $this->facZaloha->create();
		$tdd->setZalohy($zalohy);

		$out = $this->catchTemplateRender($tdd);
		Assert::true(!empty($out));
	}
}

(new TiskZalohaTest())->run();