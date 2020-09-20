<?php

namespace App\Models\Resources;

abstract class DataFileResource extends \SplFileObject{

	public function __construct(
		$file)
	{
		parent::__construct($file);
	}

	public function getContent()
	{
		return $this->fread($this->getSize());
	}

	public function getFormatedContent()
	{
		return $this->fread($this->getSize());
	}
}