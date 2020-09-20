<?php
namespace App\Extensions\App;

use Nette\Utils\ArrayHash;

/**
 *
 * @property int $id
 * @property string $group
 * @property string $nazev
 * @property string $value
 * @property string $value2
 * @property string $value3
 * @property int $deleted
 */
class CiselnikEntity extends ArrayHash{

	public function __construct($data){
		foreach($data as $k => $v){
			$this->$k = $v;
		}
	}
}