<?php


namespace App\Models\Selections;

use App\Models\Tables\ZalohaTable;
use Nette\Database\Context;
use App\Models\Core\DateRange;

class ZalohaExportSelection
{


	/** @var Context */
	private $context;

	/** @var ZalohaTable */
	private $tbl;

	public function __construct(
		Context $context,
		ZalohaTable $tbl
	) {
		$this->tbl = $tbl;
		$this->context = $context;
	}

	public function get($year)
	{
		$sql = $this->context->query('
			SELECT zz.*, 
				(
					SELECT SUM(p.platba) 
					FROM tx_platby AS p, platby_zarazeni AS pz
					WHERE
						zz.odber_mist_id = pz.om_id AND pz.`fakskup_id` IS NULL AND pz.platba_id = p.platba_id AND p.type = \'z\' AND p.platba IS NOT NULL AND YEAR(p.when) = ?
				) AS platby
			FROM (SELECT 
				z.vs, SUM(z.vyse) AS vyse, COUNT(z.odber_mist_id) AS pocet, MIN(z.od) AS od, MAX(z.do) AS DO,
				om.com, om.eic,
				a.street, a.cp, a.co, a.city, a.zip, a.byt_cis, om.`odber_mist_id`
			FROM
				tx_zalohy AS z,
				tx_odber_mist AS om,
				es_address AS a
			WHERE
			om.odber_mist_id = z.odber_mist_id AND
				a.address_id = om.address_id AND
				YEAR(z.od) = ?
			GROUP BY z.`odber_mist_id`) AS zz
		', $year, $year);

		return $sql->fetchAll();
	}
}
