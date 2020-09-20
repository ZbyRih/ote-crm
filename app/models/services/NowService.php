<?php

namespace App\Models\Services;

use Carbon\Carbon;

class NowService{

	/** @var \DateTime */
	private $now;

	public function __construct(
		$now = null)
	{
		$this->now = $now ?: Carbon::now();
	}

	public function get()
	{
		return $this->now;
	}
}