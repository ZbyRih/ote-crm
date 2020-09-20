<?php

namespace App\Extensions\Interfaces;

use App\Extensions\App\Ciselnik;
use Cake\Collection\Collection;

interface ICiselnikRepository{

	/**
	 *
	 * @param string $group
	 * @return Collection
	 */
	public function getAll($group);

	/**
	 *
	 * @param string $group
	 * @return Collection
	 */
	public function getValid($group);

	/**
	 *
	 * @param string $group
	 * @return Ciselnik
	 */
	public function getCiselnik($group);

	/**
	 *
	 * @param string $group
	 * @param string $key
	 * @return []
	 */
	public function getAllPairs($group, $key = 'value', $extract = 'nazev');

	/**
	 *
	 * @param string $group
	 * @param string $key
	 * @param mixed $possibleValues
	 * @return []
	 */
	public function getValidPairs($group, $key = 'value', $extract = 'nazev');
}