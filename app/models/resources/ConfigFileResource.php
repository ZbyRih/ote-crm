<?php

namespace App\Models\Resources;

use Nette\Http\FileUpload;
use Nette\FileNotFoundException;

class ConfigFileResource{

	/** @var ConfigFile */
	private $file;

	public function __construct(
		$file)
	{
		$this->file = new ConfigFile($file);

		if(!file_exists((string) $this->file)){
			throw new FileNotFoundException((string) $this->file);
		}
	}

	/**
	 * @param FileUpload $fu
	 * @return self
	 */
	public static function fromFileUpload(
		FileUpload $fu)
	{
		$name = $fu->getSanitizedName();

		$dest = new ConfigFile($name);

		$fu->move((string) $dest);

		return new self($name);
	}

	public function __toString()
	{
		return $this->file;
	}

	public function getContent()
	{
		return file_get_contents($this->file);
	}
}