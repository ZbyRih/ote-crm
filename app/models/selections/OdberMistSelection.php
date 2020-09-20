<?php

namespace App\Models\Selections;

use App\Models\Tables\OdberMistTable;
use Nette\Database\Context;

class OdberMistSelection{

	/** @var Context */
	private $db;

	/** @var OdberMistTable */
	private $tbl;

	public function __construct(
		Context $db,
		OdberMistTable $tbl)
	{
		$this->db = $db;
		$this->tbl = $tbl;
	}

	public function getList(
		$include = null,
		$ownerId = null)
	{
		$sel = $this->getSelect($include, $ownerId);
		$sel->select('CONCAT_WS(\', \', com, eic, city, street, CONCAT(`cp`, \'/\', `co`), `byt_cis`, `patro`) AS label');

		return $sel->fetchPairs('id', 'label');
	}

	public function getLimitList(
		$klientId = null)
	{
		if($klientId){
			$qry = $this->db->query(
				'
				SELECT
					`so`.`odber_mist_id` AS `id`,
					CONCAT_WS(\', \', com, eic, city, street, CONCAT(`cp`, \'/\', `co`), `byt_cis`, `patro`, IF(DATE(so.do) = \'9999-12-31\', \'\', CONCAT(\' (\', DATE_FORMAT(so.do, "%e.%c. %Y") ,\')\') )) AS label
				FROM
					`tx_sml_om` AS `so`,
					`odbermist_address` AS `oa`
				WHERE
					`so`.`klient_id` = ?
				AND
					`so`.`odber_mist_id` = `oa`.`odber_mist_id`
				GROUP BY
					`so`.`odber_mist_id`
				ORDER BY
					`so`.`do` ASC
			', $klientId);
		}else{
			$qry = $this->db->query(
				'
				SELECT
					`oa`.`odber_mist_id` AS `id`,
					CONCAT_WS(\', \', com, eic, city, street, CONCAT(`cp`, \'/\', `co`), `byt_cis`, `patro`) AS label
				FROM
					`odbermist_address` AS `oa`
				WHERE
					deprecated = 0
				ORDER BY
					oa.com ASC
			');
		}

		return $qry->fetchPairs('id', 'label');
	}

	public function getSelect(
		$include = null,
		$ownerId = null)
	{
		$sel = $this->tbl->table('odbermist_address')
			->select('odber_mist_id AS id')
			->select('com, eic, city, street, cp, co, byt_cis, patro')
			->whereOr([
			'deprecated' => 0
		] + ($include ? [
			'odber_mist_id' => $include
		] : []));

		if($ownerId){
			$sel->where('owner_id', $ownerId);
		}

		return $sel;
	}

	public function findByEic(
		$eic)
	{
		return $this->tbl->select()
			->where('eic', $eic)
			->fetchAll();
	}
}