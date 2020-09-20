<?php

namespace App\Models\Strategies\Fakturace;

use App\SmlProFakturuSelection;
use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Orm\Orm;
use App\Models\Tables\SmlOmTable;
use App\Models\Values\AccountValue;
use Cake\Collection\Collection;

class LoadFakskupUcetStrategy{

	/** @var Orm */
	private $orm;

	/** @var SmlOmTable  */
	private $tbl;

	/** @var SmlProFakturuSelection */
	private $sel;

	/** @var ArrayHash */
	private $faksCu;

	public function __construct(
		Orm $orm,
		SmlOmTable $tbl)
	{
		$this->orm = $orm;
		$this->tbl = $tbl;
		$this->sel = new SmlProFakturuSelection($this->tbl);
	}

	public function get(
		Collection $c)
	{
		$this->faksCu = new ArrayHash();

		$c->each(
			function (
				$v)
			{
				if(!$ret = $this->sel->get($v->klientId, $v->omId, $v->od, $v->do)){
					return;
				}

				$ret = reset($ret);

				if(!$ret->fak_skup_id){
					return;
				}

				$fs = $this->orm->fakSkups->getById($ret->fak_skup_id);
				$fskli = $this->orm->klients->getById($fs->faKlientId);

				if(!$fskli->klientDetailId->cu){
					return;
				}

				try{
					$this->faksCu[$v->id] = new AccountValue($fskli->klientDetailId->cu);
				}catch(\InvalidArgumentException $e){
				}
			});

		return $this->faksCu;
	}
}