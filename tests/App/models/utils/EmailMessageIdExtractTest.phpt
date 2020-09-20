<?php

namespace Tests\App\Models\Utils;

require_once __DIR__ . '/../../../bootstrap.php';

use Tester\TestCase;
use App\Models\Utils\EmailMessageIdExtract;
use Tester\Assert;

class EmailMessageIdExtractTest extends TestCase{

	public function __construct()
	{
	}

	public function testExtract()
	{
		$a1 = '25957.3804541541314983863';
		$a2 = '-21381310.1513021549474365776';

		$b1 = new EmailMessageIdExtract('<25957.3804541541314983863.JavaMail.xums@CZASP0W138>');
		$b2 = new EmailMessageIdExtract('<-21381310.1513021549474365776.JavaMail.mqsi@s08986ue.cz.srv.sys>');

		Assert::same($a1, $b1->__toString());
		Assert::same($a2, $b2->__toString());
	}
}

(new EmailMessageIdExtractTest())->run();