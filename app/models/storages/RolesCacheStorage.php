<?php
namespace App\Models\Storages;

use App\Extensions\Abstracts\CacheStorage;
use App\Extensions\App\Cache;
use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Tables\RoleTable;

/**
 *
 * @property-read \Cake\Collection\Collection $all
 * @property-read \Cake\Collection\Collection $active
 * @property-read array $indexed
 */
class RolesCacheStorage extends CacheStorage{

	/** @var RoleTable */
	private $tbl;

	static $dependencies = 'role';

	public function __construct(
		RoleTable $tbl,
		Cache $cache)
	{
		$this->tbl = $tbl;
		parent::__construct($cache, self::$dependencies);
	}

	public function onChanged()
	{
		$this->invalidate();
	}

	protected function fallback()
	{
		$all = collection($this->tbl->all())->map(
			function (
				$v,
				$k){
				$r = ArrayHash::from($v->toArray(), false);
				$r->perms = RoleTable::permsUnpack($r->perms);
				$r->denorm = RoleTable::permsDenorm($r->perms);
				return $r;
			})
			->indexBy('role')
			->buffered();

		$aktivni = $all->filter(function (
			$v,
			$k){
			return $v->deleted == null;
		})->buffered();

		return [
			'all' => $all,
			'indexed' => $all->toArray(),
			'active' => $aktivni
		];
	}
}