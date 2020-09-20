<?php

namespace App\Components;

use App\Components\Tisk\TiskDanDoklad;
use App\Components\Tisk\TiskFaktura;
use App\Components\Tisk\TiskRozpisZaloh;

interface ITiskDanDoklad{

	/**
	 * @return TiskDanDoklad
	 */
	public function create();
}

interface ITiskFaktura{

	/**
	 * @return TiskFaktura
	 */
	public function create();
}

interface ITiskRozpisZaloh{

	/**
	 * @return TiskRozpisZaloh
	 */
	public function create();
}