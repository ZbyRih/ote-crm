<?php

namespace App\Models\Strategies\KlientDetail;

use App\Models\Orm\KlientDetails\KlientDetailEntity;
use App\Models\Enums\KlientEnums;

class KlientDetailIdentityNameStrategy{

	public function get(
		KlientDetailEntity $kd)
	{
		if($kd->kind == KlientEnums::KIND_PO){
			return $kd->firmName;
		}else{
			return implode(' ', [
				$kd->title,
				$kd->firstname,
				$kd->lastname
			]);
		}
	}
}