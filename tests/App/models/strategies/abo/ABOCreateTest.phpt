<?php

namespace Tests\App\Models\ABO;

use Tester\TestCase;
use App\Models\ABO\ABO;
use App\Models\ABO\ABOGroup;
use App\Models\Values\AccountValue;
use App\Models\ABO\ABOItem;
use Tester\Assert;
use Nette\Utils\DateTime;

require_once __DIR__ . '/../../../../bootstrap.php';

class ABOCreateTest extends TestCase{

	public function testCreateVar1()
	{
		$abo = new ABO();
		$abo->setBank('0300');

		$g = new ABOGroup();
		$g->setDate(DateTime::from('+1 day'));

		$i = new ABOItem();
		$i->setAmount(1025.87);
		$i->srcAccount = new AccountValue('230499396/0300');
		$i->destAccount = new AccountValue('4829080207/0100');
		$i->variableSym = '21190133';

		$g->addItem($i);

		$abo->addGroup($g);

		$r = $abo->create();

		Assert::true(true);
	}

	public function testCreateVar2()
	{
		$abo = new ABO();
		$abo->setBank('0300');
		$abo->setVariant(ABO::VARIANT_2);

		$g = new ABOGroup();
		$g->setSrcAccount(new AccountValue('230499396/0300'));
		$g->setDate(DateTime::from('+1 day'));

		$i = new ABOItem();
		$i->setAmount(1025.87);
		$i->destAccount = new AccountValue('4829080207/0100');
		$i->variableSym = '21190133';

		$g->addItem($i);

		$abo->addGroup($g);

		$r = $abo->create();

		Assert::true(true);
	}
}

(new ABOCreateTest())->run();