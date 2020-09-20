<?php
namespace App\Extensions\Interfaces;

interface ITableFind{

	/**
	 *
	 * @param integer|string $id
	 * @return \stdClass|null
	 */
	public function find($id);
}
