<?php

namespace App\Extensions\Helpers;

use Ramsey\Uuid\Uuid;

class UuidHelper{

	public static function get(){
		return Uuid::uuid4();
	}
}