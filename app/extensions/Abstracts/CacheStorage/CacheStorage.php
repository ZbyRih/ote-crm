<?php
namespace App\Extensions\Abstracts;

use App\Extensions\Interfaces\ICacheStorage;
use Nette\Caching\Cache;

abstract class CacheStorage implements ICacheStorage{

	use TCacheStorage;

	public function __construct(Cache $cache, $key, array $dependencies = []){
		$this->init($cache, $key, $dependencies);
		$this->load();
	}

	public function invalidate(){
		$this->clean();
	}
}