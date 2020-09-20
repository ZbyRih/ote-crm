<?php

class OTEMailCert{

	public $public;

	public $private;

	public function __construct(
		$options)
	{
		$this->public = file_get_contents(APP_DIR . $options['cert_priv_ote']);
		$this->private = [
			$this->public,
			$options['cert_priv_pass']
		];
	}
}