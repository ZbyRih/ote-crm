<?php

namespace App\Models\Strategies\Zalohy;

use App\Models\Entities\RozpisZalohEntity;
use App\Models\Orm\Orm;
use App\Models\Strategies\IZalohyDoRozpisuZalohStrategy;
use App\Models\Strategies\Odberatel\CreateOdberatelIdentityStrategy;
use App\Extensions\Utils\DateTime;
use Carbon\Carbon;
use App\Models\DTO\TiskRospisZalohDTO;

class RopisZalohException extends \Exception{
}

class CreateRozpisZalohEntityStrategy{

	/** @var Orm */
	private $orm;

	/** @var IZalohyDoRozpisuZalohStrategy */
	private $facRozpis;

	/** @var TiskRospisZalohDTO */
	private $params;

	public function __construct(
		Orm $orm,
		IZalohyDoRozpisuZalohStrategy $facRozpis)
	{
		$this->orm = $orm;
		$this->facRozpis = $facRozpis;
	}

	/**
	 * @param TiskRospisZalohDTO $params
	 */
	public function setParams(
		$params)
	{
		$this->params = $params;
	}

	/**
	 * @return RozpisZalohEntity
	 */
	public function create()
	{
		$rozpis = new RozpisZalohEntity();
		$rozpis->od = DateTime::firstDayOfYear($this->params->year);
		$rozpis->do = DateTime::lastDayOfYear($this->params->year);
		$rozpis->vystaveno = Carbon::now();

		if(!$this->params->fakSkupId){
			$rozpis->odberMist = $this->orm->odberMist->getById($this->params->omId);
		}else{
			$fa = $this->orm->fakSkups->getById($this->params->fakSkupId);
			$rozpis->faSkupCis = $fa->cis;
		}

		$str = $this->facRozpis->create();
		$str->setParams($this->params);
		$rozpis->sestavy = $str->create();

		$strOdb = new CreateOdberatelIdentityStrategy();
		$strOdb->setOrm($this->orm);
		$rozpis->odberatel = $strOdb->create($this->params->klientId, $this->params->fakSkupId);

		return $rozpis;
	}
}