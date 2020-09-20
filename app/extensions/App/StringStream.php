<?php

namespace App\Extensions\App;

use App\Extensions\Interfaces\IStream;

class StringStream implements IStream{

	/** @var string */
	private $data = '';

	public function fetch(
		$length)
	{
		$chunk = substr($this->data, 0, $length);
		$this->data = substr($this->data, $length);
		return $chunk;
	}

	public function dump()
	{
		return $this->data;
	}

	public function put(
		$data)
	{
		$this->data .= $data;
	}

	public function isEmpty()
	{
		return strlen($this->data) <= 0;
	}
}