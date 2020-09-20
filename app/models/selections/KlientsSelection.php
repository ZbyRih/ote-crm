<?php


namespace App\Models\Selections;

use App\Models\Tables\KlientsTable;

class KlientsSelection
{

	/** @var KlientsTable */
	private $tbl;

	public function __construct(
		KlientsTable $tbl
	) {
		$this->tbl = $tbl;
	}

	public function getList(
		$include = null,
		$ownerId = null
	) {
		$sel = $this->getSelect($include, $ownerId);

		$sel->select('CONCAT_WS(\', \', IF(kind = 0, CONCAT(firstname, \' \', lastname), firm_name), IF(kind = 0, \'FO\', \'PO\'), ico, city, street, CONCAT(cp, \'/\', co)) AS label');

		return $sel->fetchPairs('id', 'label');
	}

	public function getSelect(
		$include = null,
		$ownerId = null
	) {
		$sel = $this->tbl->table('klient_address')
			->select('klient_id AS id')
			->select('kind')
			->select('firstname, lastname, firm_name, ico, city, street, cp, co, cu')
			->where('fakturacni', 0)
			->whereOr([
				'deleted' => 0
			] + ($include ? [
				'klient_id' => $include
			] : []));

		if ($ownerId) {
			$sel->where('owner_id', $ownerId);
		}

		return $sel;
	}
}
