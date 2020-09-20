<?php

namespace App\Models\Utils;

class EmailMessageIdExtract{

	/** @var string */
	private $data;

	public function __construct(
		$messageId)
	{
		$messageId = strtr($messageId, [
			'<' => ''
		]);

		$this->data = substr($messageId, 0, strpos($messageId, '.JavaMail'));
	}

	public function __toString()
	{
		return $this->data;
	}
}