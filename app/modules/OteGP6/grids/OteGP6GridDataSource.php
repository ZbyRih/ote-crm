<?php

namespace App\Modules\OteGP6\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\OteHeadTable;
use Nette\Database\Table\Selection;
use Nette\Database\Context;

class OteGP6GridDataSource extends DataSourceGridBoo{

	/** @var OteHeadTable */
	private $tbl;

	public function __construct(
		Context $db,
		OteHeadTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	protected function build()
	{
		$s = $this->tbl->select('id')
			->select('from, to, type, pofId, attributes_segment, attributes_corReason, priceTotal, depricated, cancelled, IF(faktura_id, 1, 0) AS vyfak')
			->select('(SELECT `odb`.`com` FROM `tx_odber_mist` AS `odb` WHERE `odb`.`odber_mist_id` = `tx_ote_invoice_head`.`odber_mist_id`) AS com')
			->select('(SELECT `tmo`.`ote_kod` FROM `tx_mails_ote` AS `tmo` WHERE `tmo`.`ote_id` = `tx_ote_invoice_head`.`ote_id` LIMIT 1) AS ote_kod')
			->select(
			'(SELECT CONCAT_WS(\', \', `tom`.`eic`, `adr`.`city`, `adr`.`street`, CONCAT(`adr`.`cp`, \'/\' , `adr`.`co`)) COLLATE utf8_general_ci
				FROM `es_address` AS `adr`, `tx_odber_mist` AS `tom`
				WHERE `tom`.`odber_mist_id` = `tx_ote_invoice_head`.`odber_mist_id`
					AND `adr`.`address_id` = `tom`.`address_id`
				LIMIT 1
				 ) AS adr');

		return $s;
	}

	public function filterObdobi(
		Selection $s,
		$value)
	{
		$s->where('DATE(?) BETWEEN DATE(from) AND DATE(to)', $value);
	}

	public function filterAdr(
		Selection $s,
		$value)
	{
		$s->having('adr LIKE ?', '%' . $value . '%');
	}

	public function filterCom(
		Selection $s,
		$value)
	{
		$s->having('com LIKE ? ', '%' . $value . '%');
	}

	public function filterVyfak(
		Selection $s,
		$value)
	{
		$s->having('vyfak = ?', $value);
	}
}