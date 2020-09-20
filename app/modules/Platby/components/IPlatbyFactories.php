<?php

namespace App\Modules\Platby\Factories;

use App\Modules\Platby\Components\PlatbaEdit;
use App\Modules\Platby\Components\PlatbyGrid;
use App\Modules\Platby\Components\PlatbyGridDataSource;
use App\Modules\Platby\Components\VypisUploadForm;

interface IPlatbyGrid{

	/**
	 *
	 * @return PlatbyGrid
	 */
	public function create();
}

interface IPlatbyGridDataSource{

	/**
	 *
	 * @return PlatbyGridDataSource
	 */
	public function create();
}

interface IPlatbaEdit{

	/**
	 *
	 * @return PlatbaEdit
	 */
	public function create();
}

interface IVypisUploadForm{

	/**
	 *
	 * @return VypisUploadForm
	 */
	public function create();
}