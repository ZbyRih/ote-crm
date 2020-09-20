<?php

namespace App\Models\Resources;

abstract class DataFile{

	/** @var string */
	private $file;

	public function __construct(
		$file)
	{
		$this->file = DATA_DIR . '/' . $file;
	}

	public function __toString()
	{
		return $this->file;
	}
}