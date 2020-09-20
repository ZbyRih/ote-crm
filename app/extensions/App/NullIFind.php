<?php

namespace App\Extensions\App;

use App\Extensions\Interfaces\ITableFind;

class NullIFind implements ITableFind{

	public function find($id){
		return null;
	}
}