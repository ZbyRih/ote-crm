<?php

namespace App\Models\Tables;

use App\Extensions\Abstracts\Table;
use App\Extensions\Utils\DateTime;
use Nette\Security\Permission;

class RoleTable extends Table{

	protected $table = 'role';

	public function delete(
		$id)
	{
		$this->update($id, [
			'deleted' => new DateTime()
		]);
	}

	public static function permsPack(
		$perms)
	{
		return json_encode($perms, JSON_OBJECT_AS_ARRAY);
	}

	public static function permsUnpack(
		$perms)
	{
		return $perms ? json_decode($perms, true, 512, JSON_OBJECT_AS_ARRAY) : [];
	}

	public static function permsDenorm(
		$perms)
	{
		$denorm = [];

		foreach((($perms) ? $perms : []) as $r => $i){
			if(is_array($i) || $i instanceof \ArrayAccess){
				$s = explode('_', $r);

				if(count($s) < 2){
					continue;
				}

				$r = $s[0];

				foreach($i as $k => $pr){
					$denorm[] = [
						'res' => $s[0],
						'perm' => $k,
						'allow' => $pr
					];
				}
			}else{
				$denorm[] = [
					'res' => $r,
					'perm' => ($i === 0) ? Permission::DENY : (($i == 'all') ? Permission::ALL : $i),
					'allow' => true
				];
			}
		}

		return $denorm;
	}
}