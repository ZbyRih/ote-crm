<?php

namespace App\Models\Repositories;

use Cake\Collection\Collection;
use App\Extensions\Interfaces\ICiselnikRepository;
use App\Extensions\Utils\Arrays;
use App\Models\Tables\CiselnikyValuesTable;
use App\Models\Storages\CiselnikyCacheStorage;
use App\Extensions\App\Ciselnik;

class CiselnikValueAlreadyExists extends \Exception{
}

class CiselnikyValuesRepository implements ICiselnikRepository{

	/** @var CiselnikyValuesTable */
	private $tbl;

	/** @var CiselnikyCacheStorage */
	private $storage;

	public function __construct(
		CiselnikyValuesTable $cisl,
		CiselnikyCacheStorage $storage)
	{
		$this->tbl = $cisl;
		$this->storage = $storage;
	}

	/**
	 *
	 * @param string $group
	 * @return Collection
	 */
	public function getAll(
		$group)
	{
		return $this->storage->indexed[$group];
	}

	/**
	 *
	 * @param string $group
	 * @return Collection
	 */
	public function getValid(
		$group)
	{
		return $this->storage->indexedValids[$group];
	}

	/**
	 *
	 * @param string $group
	 * @return Ciselnik
	 */
	public function getCiselnik(
		$group)
	{
		return $this->storage->ciselniky[$group];
	}

	/**
	 *
	 * @param string $group
	 * @param string $key
	 * @return []
	 */
	public function getAllPairs(
		$group,
		$key = 'value',
		$extract = 'nazev')
	{
		$cGroup = $this->storage->indexed[$group];
		return $cGroup->indexBy($key)
			->extract($extract)
			->toArray();
	}

	/**
	 *
	 * @param string $group
	 * @param string $key
	 * @param mixed $possibleValues
	 * @return []
	 */
	public function getValidPairs(
		$group,
		$key = 'value',
		$extract = 'nazev')
	{
		$cGroup = $this->storage->indexedValids[$group];
		return $cGroup->indexBy($key)
			->extract($extract)
			->toArray();
	}

	public function save(
		$vals)
	{
		$id = Arrays::remove('id', $vals);

		if($this->getByValue($vals['value'], $vals['group'], $id)){
			throw new CiselnikValueAlreadyExists();
		}

		if($id && $item = $this->tbl->find($id)){
			$item->update($vals);
		}else{
			$this->tbl->insert($vals);
		}

		$this->storage->invalidate();
	}

	private function getByValue(
		$value,
		$group,
		$exceptId = null)
	{
		$s = $this->tbl->table()
			->where('value', $value)
			->where('group', $group);

		if($exceptId){
			$s->where('id != ?', $exceptId);
		}

		return $s->where('deleted', 0)->fetch();
	}

	public function delete(
		$id)
	{
		if(!$item = $this->tbl->find($id)){
			return;
		}

		$item->update([
			'deleted' => 1
		]);

		$this->storage->invalidate();
	}
}