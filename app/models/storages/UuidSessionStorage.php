<?php

namespace App\Models\Storages;

use Nette\Http\Session;
use Nette\Http\SessionSection;
use Ramsey\Uuid\UuidInterface;
use Nextras\Orm\Entity\AbstractEntity;

class UuidSessionStorage{

	/** @var SessionSection */
	private $data;

	public function __construct(
		Session $session,
		$sectionName)
	{
		$this->data = $session->getSection($sectionName);
		if(!$this->data->offsetExists('items')){
			$this->data->items = [];
		}
	}

	/**
	 * @param UuidInterface $uuid
	 * @return NULL
	 */
	public function get(
		UuidInterface $uuid)
	{
		$key = $uuid->toString();

		if(!array_key_exists($key, $this->data->items)){
			return null;
		}

		$i = $this->data->items[$key];

		$o = new $i['meta']();

		$o->unserialize($i['data']);

		return $o;
	}

	public function put(
		UuidInterface $uuid,
		AbstractEntity $item)
	{
		$this->data->items[$uuid->toString()] = [
			'meta' => get_class($item),
			'data' => $item->serialize()
		];
	}

	public function drop(
		UuidInterface $uuid)
	{
		unset($this->data->items[$uuid->toString()]);
	}
}