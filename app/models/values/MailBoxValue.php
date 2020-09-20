<?php

namespace App\Models\Values;

class MailBoxValue{

	/** @var string */
	private $server;

	/** @var string */
	private $folder;

	public function __construct(
		$full)
	{
		$this->server = strpos($full, '}') ? substr($full, 0, strpos($full, '}') + 1) : '';
		$this->folder = strpos($full, '}') ? substr($full, strpos($full, '}') + 1) : $full;
	}

	public function __toString()
	{
		return $this->server . $this->folder;
	}

	public function getServer()
	{
		return $this->server;
	}

	public function getFolder()
	{
		return $this->folder;
	}
}