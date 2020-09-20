<?php

namespace App\Models\Resources;

class ConfigFile{

	/** @var string */
	private $file;

	public function __construct(
		$file)
	{
		$this->file = APP_DIR . 'config/files/' . $file;
	}

	public function __toString()
	{
		return $this->file;
	}
}