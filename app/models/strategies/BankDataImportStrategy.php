<?php

namespace App\Models\Strategies;

class BankDataImportStrategy{

	public $dir = DATA_DIR . '/banka/import/%s';

	public function file(
		$fileName)
	{
		return sprintf('%s.%s', time(), $fileName);
	}

	public function full(
		$fileName)
	{
		return sprintf($this->dir, $fileName);
	}
}