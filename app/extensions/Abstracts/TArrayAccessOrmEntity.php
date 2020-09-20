<?php

namespace App\Extensions\Abstracts;

trait TArrayAccessOrmEntity{
	use TArrayAccess;

	public function offsetExists(
		$offset)
	{
		return $this->hasValue($offset);
	}
}