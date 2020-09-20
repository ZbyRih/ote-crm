<?php

namespace App\Modules\OteZpravy\Factories;

use App\Modules\OteZpravy\Grids\OteMessagesGrid;
use App\Modules\OteZpravy\Grids\OteMessagesGridDataSource;
use App\Modules\OteZpravy\UploadForm;

interface IOteMessageGrid{

	/**
	 *
	 * @return OteMessagesGrid
	 */
	public function create();
}

interface IOteMessageGridDataSource{

	/**
	 *
	 * @return OteMessagesGridDataSource
	 */
	public function create();
}

interface IOteMessageUploadForm{

	/**
	 *
	 * @return UploadForm
	 */
	public function create();
}