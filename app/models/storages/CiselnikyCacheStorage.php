<?php

namespace App\Models\Storages;

use App\Extensions\Abstracts\CacheStorage;
use App\Extensions\App\Cache;
use App\Extensions\App\CiselnikEntity;
use App\Extensions\App\Ciselnik;
use App\Models\Tables\CiselnikyValuesTable;

/**
 *
 * @property \Cake\Collection\Collection[] $indexed
 * @property \Cake\Collection\Collection[] $indexedValids
 * @property Ciselnik[] $ciselniky
 *
 */
class CiselnikyCacheStorage extends CacheStorage{

	/** @var CiselnikyValuesTable */
	private $tbl;

	static $dependencies = 'ciselniky';

	public function __construct(
		CiselnikyValuesTable $tbl,
		Cache $cache)
	{
		$this->tbl = $tbl;
		parent::__construct($cache, self::$dependencies);
	}

	protected function fallback()
	{
		$all = collection($this->tbl->all())->map(function (
			$v){
			return new CiselnikEntity($v);
		})->buffered();

		$groups = $all->groupBy('group')->compile();

		$ciselniky = $groups->map(function (
			$v){
			return new Ciselnik(collection($v));
		})->toArray();

		$cGroups = $groups->map(function (
			$v){
			return collection($v)->buffered();
		})
			->toArray();

		$cValids = $all->match([
			'deleted' => 0
		])
			->groupBy('group')
			->map(function (
			$v){
			return collection($v)->buffered();
		})
			->toArray();

		return [
			'indexed' => $cGroups,
			'indexedValids' => $cValids,
			'ciselniky' => $ciselniky
		];
	}
}
