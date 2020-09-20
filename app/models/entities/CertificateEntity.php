<?php

namespace App\Models\Entities;

class CertificateEntity{

	public $public;

	public $private;

	public function __construct(
		$certContent,
		$pass)
	{
		$this->public = $certContent;
		$this->private = [
			$this->public,
			$pass
		];
	}

	public static function createFromFileName(
		$certFile,
		$pass)
	{
		$cnt = file_get_contents($certFile);
		return new static($cnt, $pass);
	}
}