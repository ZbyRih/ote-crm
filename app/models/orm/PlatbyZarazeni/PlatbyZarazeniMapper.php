<?php

namespace App\Models\Orm\PlatbyZarazeni;

use Nextras\Orm\Mapper\Mapper;

class PlatbyZarazeniMapper extends Mapper{

	protected $tableName = 'platby_zarazeni';

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Mapper\Dbal\DbalMapper::createStorageReflection()
	 */
	protected function createStorageReflection()
	{
		$sr = parent::createStorageReflection();

		$sr->setMapping('platba', 'platba_id');

		return $sr;
	}
}