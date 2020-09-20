<?php

namespace App\Models\ABO;

class GPCFileParser{

	public function parse(
		$handle)
	{
		$ret = [];

		$reader = new GPCParser();

		while(($line = fgets($handle)) !== false){
			if(!$o = $reader->parse($line)){
				continue;
			}

			if($o['Type'] == GPCBase::TYPE_REPORT){
				continue;
			}

			$ret[] = $o;
		}

		return $ret;
	}
}