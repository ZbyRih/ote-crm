<?php

namespace App\Modules\Faktury\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\OteHeadTable;
use Nette\Database\Context;

/**

 */
class FakturaOteGridDataSource extends DataSourceGridBoo{

	/** @var OteHeadTable */
	private $tbl;

	/** @var string */
	private $year;

	/** @var string */
	private $view;

	/** @var integer */
	private $fakturaId;

	protected $primary_key = 'id';

	public function __construct(
		Context $db,
		OteHeadTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	/**
	 * @param number $fakturaId
	 */
	public function setFakturaId(
		$fakturaId)
	{
		$this->fakturaId = $fakturaId;
	}

	protected function build()
	{
		$s = $this->tbl->select('id')
			->select('from, to, pofId, attributes_segment, attributes_corReason, priceTotal, cancelled, IF(faktura_id, 1, 0) AS vyfak')
			->select('(SELECT `tmo`.`ote_kod` FROM `tx_mails_ote` AS `tmo` WHERE `tmo`.`ote_id` = `tx_ote_invoice_head`.`ote_id` LIMIT 1) AS ote_kod')
			->select(
			'(SELECT CONCAT_WS(\', \', `tom`.`eic`, `adr`.`city`, `adr`.`street`, CONCAT(`adr`.`cp`, \'/\' , `adr`.`co`))
				FROM `es_address` AS `adr`, `tx_odber_mist` AS `tom`
				WHERE `tom`.`odber_mist_id` = `tx_ote_invoice_head`.`odber_mist_id`
					AND `adr`.`address_id` = `tom`.`address_id`
				LIMIT 1
				 ) AS adr');

		$s->where('faktura_id', $this->fakturaId);

		return $s;
	}
}