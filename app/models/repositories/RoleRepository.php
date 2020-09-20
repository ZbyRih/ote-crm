<?php
namespace App\Models\Repositories;

use App\Models\Storages\RolesCacheStorage;

class RoleRepository{

	public static $modulPravaOpt = [
		'0' => 'odepřeno',
		'view' => 'view',
		'edit' => 'add + edit',
		'delete' => 'add + edit + delete',
		'all' => 'all'
	];

	public static $privilegiaOpts = [
		'0' => 'Ne',
		'1' => 'Ano'
	];

	/** @var RolesCacheStorage */
	private $storage;

	public function __construct(RolesCacheStorage $storage){
		$this->storage = $storage;
	}

	public function getNames($diff = [], $active = false){
		$c = $active ? $this->storage->active : $this->storage->all;

		return array_diff_key($c->extract('nazev')->toArray(), array_combine($diff, $diff));
	}

	public function getAll(){
		return $this->storage->all;
	}

	public function getRoles(){
		return $this->storage->all->extract('role')->toArray();
	}

	public function get($key){
		return array_key_exists($key, $this->storage->indexed) ? $this->storage->indexed[$key] : null;
	}
}