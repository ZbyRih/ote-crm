<?php
namespace App\Extensions\App;

use Nette\Caching\IStorage;

class Cache extends \Nette\Caching\Cache{

	public function __construct(IStorage $storage, $namespace = null){
		parent::__construct($storage, 'app-runtime-cache');
	}
}