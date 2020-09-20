<?php

namespace App\Models\Orm\OteGP6Body;

use Nextras\Orm\Mapper\Mapper;

class OteGP6BodyMapper extends Mapper{

	protected $tableName = 'tx_ote_invoice_body';

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Mapper\Dbal\DbalMapper::createStorageReflection()
	 */
	protected function createStorageReflection()
	{
		$sr = parent::createStorageReflection();
		return $sr;
	}
}