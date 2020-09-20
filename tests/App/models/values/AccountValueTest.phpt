<?php

namespace Tests\App\Models\Values;

use Tester\TestCase;
use App\Models\Values\AccountValue;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

class AccountValueTest extends TestCase{

	public function __construct()
	{
	}

	public function testValid()
	{
		$u = new AccountValue('123-123/0600');
		Assert::same('0600', $u->toBank());
		Assert::same('123-123', $u->toDelim());
		Assert::same('123-123/0600', $u->__toString());

		$u = new AccountValue('123/0600');
		Assert::same('123/0600', $u->__toString());
	}

	public function testInvalid()
	{
		Assert::throws(function ()
		{
			new AccountValue('a123-123/0600');
		}, \InvalidArgumentException::class);

		Assert::throws(function ()
		{
			new AccountValue('123-a123/0600');
		}, \InvalidArgumentException::class);

		Assert::throws(function ()
		{
			new AccountValue('123-123/a0600');
		}, \InvalidArgumentException::class);

		Assert::throws(function ()
		{
			new AccountValue('a');
		}, \InvalidArgumentException::class);

		Assert::throws(function ()
		{
			new AccountValue('123');
		}, \InvalidArgumentException::class);

		Assert::throws(function ()
		{
			new AccountValue('123-123');
		}, \InvalidArgumentException::class);
	}
}

(new AccountValueTest())->run();