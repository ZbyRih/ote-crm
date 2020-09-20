<?php

namespace App\Modules\Tags\Factories;

use App\Modules\Tags\Grids\TagsGrid;
use App\Modules\Tags\Grids\TagsGridDataSource;

interface ITagsGrid{

	/**
	 * @return TagsGrid
	 */
	function create();
}

interface ITagsGridDataSource{

	/**
	 * @return TagsGridDataSource
	 */
	function create();
}
