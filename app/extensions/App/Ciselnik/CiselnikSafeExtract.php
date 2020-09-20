<?php

namespace App\Extensions\App;

use App\Extensions\Interfaces\ICiselnikRepository;
use App\Extensions\Utils\Arrays;

class CiselnikSafeExtract{

	/** @var ICiselnikRepository */
	private $cisl;

	public function __construct(ICiselnikRepository $cisl){
		$this->cisl = $cisl;
	}

	public function extractPairs($group, $possibleValues, $key = 'value', $extract = 'nazev'){
		return self::extractPairsFromCisl($this->cisl, $group, $possibleValues, $key, $extract);
	}

	public static function extractPairsFromCisl(ICiselnikRepository $cisl, $group, $possibleValues, $key = 'value', $extract = 'nazev'){
		$aValids = $cisl->getValidPairs($group, $key, $extract);

		if($possibleValues === null){
			return $aValids;
		}

		$entitys = $cisl->getCiselnik($group);

		$possibleValues = Arrays::toArray($possibleValues);
		foreach($possibleValues as $v){
			if(!array_key_exists($v, $aValids)){
				$e = $entitys->byProp($key, $v);
				$aValids[$v] = $e->$extract;
			}
		}

		return $aValids;
	}
}