<?php

namespace Tests\App\Extensions;

use Tester\TestCase;
use App\Extensions\App\StringStream;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

class StringStreamTest extends TestCase{

	public function __construct()
	{
	}

	public function testFetch()
	{
		$str = new StringStream();
		$str->put('abcdefgh');
		$a = $str->fetch(4);
		$b = $str->dump();

		Assert::same('abcd', $a);
		Assert::same('efgh', $b);
	}
}

(new StringStreamTest())->run();