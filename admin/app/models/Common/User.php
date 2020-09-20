<?php

class MUser extends ModelClass{

	var $name = 'User';

	var $alias = 'User';

	var $table = 'user';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'login',
		'role',
		'pass',
		'jmeno',
		'email',
// 		'superuser',
// 		'cookieid',
// 		'session',
		'perms',
		'activity',
		'lastloc',
		'delegate',
		'onlyown',
		'theme',
		'platby',
		'chowner',
		'info_ote',
		'info_banka'
	];

	var $defaults = [
		'delegate' => 0,
		'onlyown' => 0,
		'theme' => 0,
		'platby' => 0,
		'chowner' => 0
	];

	static $ACCESS = [
		'0' => 'nepřístupné',
		'1' => 'prohlížet',
		'2' => 'měnit',
		'3' => 'mazat'
	];

	public function getUsersFor($for){
		$all = $this->FindAll([
			$for => 1
		]);
		return MArray::getKeyValsFromModels($all, $this->name, 'id');
	}
}