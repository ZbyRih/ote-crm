<?php

namespace App\Models\Orm\OteGP6Head;

use Nextras\Orm\Mapper\Mapper;

class OteGP6HeadMapper extends Mapper{

	protected $tableName = 'tx_ote_invoice_head';

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Mapper\Dbal\DbalMapper::createStorageReflection()
	 */
	protected function createStorageReflection()
	{
		$sr = parent::createStorageReflection();

		$sr->setMapping('pofId', 'pofId');
		$sr->setMapping('yearReCalculatedValue', 'yearReCalculatedValue');
		$sr->setMapping('attributesCorReason', 'attributes_corReason');
		$sr->setMapping('attributesComplId', 'attributes_complId');
		$sr->setMapping('attributesSCNumber', 'attributes_SCNumber');
		$sr->setMapping('priceTotal', 'priceTotal');
		$sr->setMapping('priceTotalDph', 'priceTotalDph');

		return $sr;
	}
}