<?php

namespace App\Extensions\App;

use App\Extensions\Utils\Helpers\ArrayHash;
use Nette\Http\Session;
use Nette\Http\SessionSection;

class PersistentParameterSessionStorage{

	/** @var SessionSection */
	private $store;

	public function __construct(Session $session){
		$this->store = $session->getSection('persistence');
	}

	public function create($key, $context, &$property = null){
		if(!$this->store->offsetExists($context)){
			$this->store->$context = new ArrayHash();
		}

		if(!$this->store->$context->offsetExists($key)){
			$this->store->$context->$key = null;
		}

		return new PersistentItem($key, $context, $property, $this);
	}

	public function get($key, $context){
		return $this->store->$context->$key;
	}

	public function set($value, $key, $context){
		return $this->store->$context->$key = $value;
	}
}