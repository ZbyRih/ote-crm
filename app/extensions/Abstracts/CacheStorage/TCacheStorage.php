<?php
namespace App\Extensions\Abstracts;

use Nette\Caching\Cache;
use App\Extensions\Utils\Arrays;

trait TCacheStorage{

	/** @var Cache */
	private $cache;

	/** @var string */
	private $key;

	/** @var [] */
	private $dependencies;

	/** @var [] */
	private $data;

	abstract protected function fallback();

	/**
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		return $this->data[$name];
	}

	/**
	 *
	 * @param Cache $cache
	 * @param string $key
	 * @param array $dependencies
	 * @return self
	 */
	protected function init(Cache $cache, $key, array $dependencies = []){
		$this->cache = $cache;
		$this->key = $key;

		if(!$tags = Arrays::remove(Cache::TAGS, $dependencies)){
			$tags = [];
		}

		$this->dependencies = [
			Cache::TAGS => array_merge([
				$key
			], $tags)
		] + $dependencies;
		return $this;
	}

	protected function load(){
		$this->data = $this->cache->load($this->key,
			function (&$dependencies){
				$dependencies = $this->dependencies;
				return call_user_func([
					$this,
					'fallback'
				]);
			});
	}

	/**
	 * odstranění z cache a clean
	 * @return self
	 */
	protected function remove(){
		$this->cache->remove($this->key);
		$this->clean();
		return $this;
	}

	/**
	 * maže podle kodicí (tagů čili vše co s tím souvisí)
	 * @param array $conditions
	 * @return self
	 */
	protected function clean($conditions = []){
		if(!$tags = Arrays::remove(Cache::TAGS, $conditions)){
			$tags = [];
		}

		$tags = array_merge($this->dependencies[Cache::TAGS], $tags);

		$this->cache->clean([
			Cache::TAGS => $tags
		] + $conditions);

		return $this;
	}
}