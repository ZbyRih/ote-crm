<?php

namespace App\Models\Entities;

class PlatbaEntity{

	/** @var \DateTime */
	public $when;

	/** @var string */
	public $vs;

	/** @var string */
	public $cu;

	/** @var float */
	public $sum;

	public function __construct()
	{
	}
}