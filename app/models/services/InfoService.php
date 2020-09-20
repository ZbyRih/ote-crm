<?php

namespace App\Models\Services;

use App\Models\Orm\Orm;
use App\Models\Orm\Info\InfoEntity;
use App\Models\DTO\InfoData;

class InfoService{

	/** @var Orm */
	private $orm;

	/** @var InfoData[] */
	private $entitys;

	public function __construct(
		Orm $orm)
	{
		$this->orm = $orm;
	}

	/**
	 * @param string $type
	 * @return InfoData
	 */
	public function createObj(
		$type)
	{
		return $this->entitys[] = new InfoData($type);
	}

	/**
	 * @param InfoData $e
	 * @return NULL|InfoEntity
	 */
	public function persist(
		InfoData $e)
	{
		if(!$e->hasData()){
			return null;
		}

		$ee = $e->getEntity();
		$this->orm->persist($ee);
		return $ee;
	}
}