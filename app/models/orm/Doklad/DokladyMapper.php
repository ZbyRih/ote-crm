<?php

namespace App\Models\Orm\Doklad;

use Nextras\Orm\Mapper\Mapper;

class DokladyMapper extends Mapper{

	protected $tableName = 'doklady';

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Mapper\Dbal\DbalMapper::createStorageReflection()
	 */
	protected function createStorageReflection()
	{
		$sr = parent::createStorageReflection();

		$sr->setMapping('platbaId', 'platba_id');

		return $sr;
	}
}