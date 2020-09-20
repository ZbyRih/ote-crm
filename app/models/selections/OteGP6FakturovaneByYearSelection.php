<?php

namespace App\Models\Selections;

use Nette\Database\Context;

class OteGP6FakturovaneByYearSelection{

	/** @var Context */
	private $db;

	public function __construct(
		Context $db)
	{
		$this->db = $db;
	}

	public function get(
		$year)
	{
		return $this->db->query(
			'
			SELECT
				o.received,
				o.ote_kod,
				o.ote_id,
				o.msg_uid,
				o.file_eml
			FROM
				tx_mails_ote AS o,
				tx_ote_invoice_head AS h,
				tx_faktury AS f
			WHERE
				f.storno = 0 AND
				YEAR(f.od) = ? AND
				h.faktura_id = f.id AND
				o.ote_id = h.ote_id
			GROUP BY o.ote_id
		', $year);
	}
}