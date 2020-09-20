<?php

namespace App\Models\Repositories;

use App\Extensions\Utils\Arrays;
use App\Models\Storages\CiselnikyCacheStorage;
use App\Models\Tables\CiselnikySkupinyTable;
use App\Models\Tables\CiselnikyValuesTable;
use App\Extensions\Abstracts\DatabaseDataNotFoundException;

class CiselnikSkupinaAlreadyExists extends \Exception{
}

class CiselnikyGroupsRepository{

	/** @var CiselnikySkupinyTable */
	private $tbl;

	/** @var CiselnikyValuesTable */
	private $tblItems;

	/** @var CiselnikyCacheStorage */
	private $storage;

	public function __construct(
		CiselnikySkupinyTable $tbl,
		CiselnikyValuesTable $tblItems,
		CiselnikyCacheStorage $storage)
	{
		$this->tbl = $tbl;
		$this->tblItems = $tblItems;
		$this->storage = $storage;
	}

	public function getValidGroups()
	{
		return $this->tbl->select()
			->where('deleted', 0)
			->fetchPairs('nazev', 'nazev');
	}

	public function save(
		$vals)
	{
		$id = Arrays::remove('id', $vals);

		if($this->getByName($vals['nazev'], $id)){
			throw new CiselnikSkupinaAlreadyExists();
		}

		if(!$id){
			$this->tbl->insert($vals);
			$this->storage->invalidate();
			return;
		}

		if(!$org = $this->tbl->find($id)){
			throw new DatabaseDataNotFoundException();
		}
		$orgNazev = $org->nazev;

		$org->update($vals);

		$this->tblItems->table()
			->where('group', $orgNazev)
			->update([
			'group' => $org->nazev
		]);
		$this->storage->invalidate();
	}

	public function delete(
		$id)
	{
		if(!$group = $this->tbl->find($id)){
			return;
		}

		$group->update([
			'deleted' => 1
		]);

		$this->tblItems->findAll('group', $group->nazev)->update([
			'deleted' => 1
		]);

		$this->storage->invalidate();
	}

	private function getByName(
		$name,
		$excludId = null)
	{
		$s = $this->tbl->table()->where('nazev', $name);

		if($excludId){
			$s->where('id != ?', $excludId);
		}

		return $s->fetch();
	}
}