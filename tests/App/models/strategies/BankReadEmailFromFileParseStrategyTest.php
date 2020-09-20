<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Enums\InfoEnums;
use App\Models\Events\DBBeginEvent;
use App\Models\Events\DBRollbackEvent;
use App\Models\Orm\Orm;
use App\Models\Selections\FakturaSelection;
use App\Models\Selections\ZalohaSelection;
use App\Models\Services\InfoService;
use App\Models\Strategies\BankPlatbyParseEmailPlainStrategy;
use App\Models\Strategies\BankaFilterDuplicatesStrategy;
use App\Models\Strategies\EmailPlainPartToPlatbaEntityStrategy;
use App\Models\Strategies\PlatbyZaraditStrategy;
use App\Models\Tables\PlatbaTable;
use Contributte\EventDispatcher\EventDispatcher;
use Phemail\MessageParser;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

class BankReadEmailFromFileParseStrategyTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	/** @var PlatbaTable */
	private $tbl;

	/** @var InfoService */
	private $info;

	/** @var ZalohaSelection */
	private $selZals;

	/** @var FakturaSelection  */
	private $selFaks;

	/** @var EventDispatcher */
	private $dispatcher;

	const DPH_COEF = 0.1458;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->tbl = $c->getByType(PlatbaTable::class);
		$this->info = $c->getByType(InfoService::class);

		$this->selZals = $c->getByType(ZalohaSelection::class);
		$this->selFaks = $c->getByType(FakturaSelection::class);
		$this->dispatcher = $c->getByType(EventDispatcher::class);
	}

	public function setUp()
	{
		$this->dispatcher->dispatch(DBBeginEvent::NAME);
	}

	public function tearDown()
	{
		$this->dispatcher->dispatch(DBRollbackEvent::NAME);
	}

	public function getMail()
	{
		return [
			[
				__DIR__ . '....eml',
				2,
				1
			]
		];
	}

	/**
	 * @dataProvider getMail
	 */
	public function testRead(
		$file,
		$countR,
		$countW)
	{
		$parser = new MessageParser();
		$message = $parser->parse($file);

		$subject = quoted_printable_decode($message->getHeaderValue('subject'));
		$plain = quoted_printable_decode($message->getContents());

		$parts = new \ArrayObject([]);

		$str = new BankPlatbyParseEmailPlainStrategy();
		$promise = $str->parse($subject, $plain);

		$promise->then(function (
			$result) use (
		$parts)
		{
			foreach($result as $p){
				$parts->append($p);
			}
		});
		$promise->done();

		$plas = [];
		$str = new EmailPlainPartToPlatbaEntityStrategy(self::DPH_COEF);
		foreach($parts as $par){
			$plas[] = $str->get($par);
		}

		Assert::same($countR, count($plas));

		$plas = array_filter($plas);

		$str = new BankaFilterDuplicatesStrategy($this->tbl);
		$plas = $str->filter($plas);

		$info = $this->info->createObj(InfoEnums::TYPE_BANK);

		$str = new PlatbyZaraditStrategy($this->orm, $info, $this->selFaks, $this->selZals);
		$platby = $str->zaradit($plas);

		foreach($platby as $p){
			$this->orm->persist($p);
		}

		ddProperty($platby, 'when');
		ddProperty($platby, 'vs');

		Assert::same($countW, count($plas));
	}
}

(new BankReadEmailFromFileParseStrategyTest())->run();