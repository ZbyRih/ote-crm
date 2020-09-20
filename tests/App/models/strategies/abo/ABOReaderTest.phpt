<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../../bootstrap.php';

use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use App\Models\ABO\GPCFileParser;
use App\Models\Strategies\ABO\GPCItemsStornoFilterStrategy;

class ABOReaderTest extends IntegrationTestCase{

	const DIR = DATA_DIR . '/banka/abo';

	public function getFileHnadler()
	{
		foreach(scandir(self::DIR) as $file){
			if(is_dir($file)){
				continue;
			}

			if(!$handle = fopen(self::DIR . '/' . $file, "r")){
				continue;
			}

			yield [
				'handle' => $handle
			];
		}
	}

	/**
	 * @dataProvider getFileHnadler
	 */
	public function testIncome(
		$handle)
	{
		$reader = new GPCFileParser();

		$items = $reader->parse($handle);

		fclose($handle);
		Assert::true((bool) count($items));
	}

	/**
	 * @dataProvider getFileHnadler
	 */
	public function testFilter(
		$handle)
	{
		$reader = new GPCFileParser();
		$filter = new GPCItemsStornoFilterStrategy();

		$items = $reader->parse($handle);

		$ret = $filter->filter($items);

		fclose($handle);
		Assert::true((bool) count($items));
	}
}

(new ABOReaderTest())->run();