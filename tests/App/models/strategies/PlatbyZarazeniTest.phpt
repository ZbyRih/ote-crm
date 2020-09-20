<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use Tests\Utils\IntegrationTestCase;
use App\Models\Orm\Orm;
use App\Models\Selections\FakturaSelection;
use App\Models\Selections\ZalohaSelection;
use App\Models\Strategies\PlatbyZaraditStrategy;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use Tester\TestCaseException;
use Tester\Assert;
use Contributte\EventDispatcher\EventDispatcher;
use App\Models\Events\DBBeginEvent;
use App\Models\Events\DBRollbackEvent;
use App\Models\Orm\Platby\PlatbaEntity;

class PlatbyZarazeniTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	/** @var FakturaSelection */
	private $selFaks;

	/** @var ZalohaSelection */
	private $selZals;

	/** @var EventDispatcher */
	private $dispatcher;

	const PLAS = [
		1,
		12909
	];

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->selFaks = $c->getByType(FakturaSelection::class);
		$this->selZals = $c->getByType(ZalohaSelection::class);
		$this->dispatcher = $c->getByType(EventDispatcher::class);
	}

	public function tearUp()
	{
		$this->dispatcher->dispatch(DBBeginEvent::NAME);
	}

	public function tearDown()
	{
		$this->dispatcher->dispatch(DBRollbackEvent::NAME);
	}

	public function testPar()
	{
		$plas = $this->orm->platby->findById(self::PLAS);

		$zarazeni = [];
		$types = [];

		foreach($plas as $p){
			$zarazeni[$p->platbaId] = [
				'k' => $p->zarazeni->klientId,
				'o' => $p->zarazeni->omId
			];
			$types[$p->platbaId] = $p->type;

			$p->type = null;
			$this->orm->platbyZarazeni->remove($p->zarazeni, false);
		}

		$info = new InfoData(InfoEnums::TYPE_BANK);

		$str = new PlatbyZaraditStrategy($this->orm, $info, $this->selFaks, $this->selZals);
		$plas = $str->zaradit($plas);

		foreach($plas as $p){
			if(!$p->zarazeni){
				throw new TestCaseException('nedoslo k zarazeni `' . $p->platbaId . '`');
			}

			if($p->type != $types[$p->platbaId]){
				throw new TestCaseException('nesedi novy typ `' . $p->type . '` se starym typem `' . $types[$p->platbaId] . '`');
			}

			$z = $zarazeni[$p->platbaId];

			if($p->zarazeni->klientId != $z['k']){
				throw new TestCaseException('klient nepripojen');
			}

			if($p->zarazeni->omId != $z['o']){
				throw new TestCaseException('odbermist nepripojeno');
			}
		}

		Assert::true(true);
	}

	public function testLinda()
	{
		$plas = [];

		$pi = new PlatbaEntity();
		$pi->fromCu = '35-0050990217/0100';
		$pi->platba = 4000;
		$pi->vs = '9301823704';
		$pi->when = new \DateTime('2020-01-13 05:10:00');

		$plas[] = $pi;

		$info = new InfoData(InfoEnums::TYPE_BANK);

		$str = new PlatbyZaraditStrategy($this->orm, $info, $this->selFaks, $this->selZals);
		$plas = $str->zaradit($plas);

		Assert::true($pi->zarazeni->hasValue('klientId'));
		Assert::true($pi->zarazeni->hasValue('omId'));
	}
}

(new PlatbyZarazeniTest())->run();