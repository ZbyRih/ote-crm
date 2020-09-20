<?php

namespace App\Models\Utils;

class CertException extends \Exception{
}

class CertUtils{

	/** @var string */
	private $cnt;

	public function __construct(
		$cnt)
	{
		$this->cnt = $cnt;
	}

	public function test()
	{
		$lastXmlErr = libxml_use_internal_errors(true);
		libxml_clear_errors();

		try{
			$x509_data = openssl_x509_parse($this->cnt);

			if(is_array($x509_data) && !empty($x509_data)){
				return;
			}

			if($e = $this->collectErrors()){
				throw new CertException($e);
			}
		}finally{
			libxml_clear_errors();
			libxml_use_internal_errors($lastXmlErr);
		}
	}

	public function getTo()
	{
		$lastXmlErr = libxml_use_internal_errors(true);
		libxml_clear_errors();

		try{
			if($x509_data = openssl_x509_parse($this->cnt)){
				return date_create('@' . $x509_data['validTo_time_t']);
			}

			if($e = $this->collectErrors()){
				throw new CertException($e);
			}
		}finally{
			libxml_clear_errors();
			libxml_use_internal_errors($lastXmlErr);
		}
		return null;
	}

	private function collectErrors()
	{
		$all = [];
		while($e = openssl_error_string()){
			$all[] = $e;
		}
		return implode(', ', $all);
	}
}