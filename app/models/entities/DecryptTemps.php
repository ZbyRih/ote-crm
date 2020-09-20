<?php

namespace App\Models\Entities;

class DecryptTemps{

	public $raw;

	public $decrypted;

	public $recivedCert;

	public $unsigned;

	public function __construct()
	{
		$tmp = sys_get_temp_dir();

		$this->raw = tempnam($tmp, 'eml');
		$this->unsigned = tempnam($tmp, 'uns');
		$this->decrypted = tempnam($tmp, 'dec');
		$this->recivedCert = tempnam($tmp, 'cer');
	}
}