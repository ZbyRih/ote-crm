<?php
namespace App\Models\Repositories;

use App\Models\Tables\UserTable;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use App\Extensions\Helpers\Helpers;
use App\Extensions\Utils\DateTime;

class WrongToken extends \Exception{
}

class UserRepository{

	/** @var UserTable */
	private $tbl;

	public function __construct(UserTable $user){
		$this->tbl = $user;
	}

	public function findByLogin($login){
		return $this->tbl->findOne('login', $login);
	}

	/**
	 *
	 * @param array $row
	 */
	public function updateLast($row){
		$row = new ActiveRow($row, $this->tbl->table());
		$row->update([
			'activity' => new DateTime()
		]);
	}

	public function changePass($u, $pass){
		$u->update([
			'token' => null,
			'token_exp' => null,
			'pass' => Helpers::passwordHash($pass)
		]);
	}

	/**
	 *
	 * @param ActiveRow $u
	 * @return string
	 */
	public function createToken($u){
		$token = md5(uniqid((string) rand(), true));
		$u->update([
			'token' => $token,
			'token_exp' => new SqlLiteral('NOW() + INTERVAL 1 DAY')
		]);
		return $token;
	}

	public function checkToken($token){
		if(empty($token)){
			throw new WrongToken('Není uveden token.');
		}

		if(!$u = $this->tbl->findOne('token', $token)){
			throw new WrongToken('Token nebyl nalezen.');
		}

		if($u->token_exp < new DateTime()){
			$u->update([
				'token' => null,
				'token_exp' => null
			]);
			throw new WrongToken('Tokenu vypršela platnost.');
		}

		return $u;
	}

	public function checkLogin($login, $id){
		$chs = $this->tbl->select()
			->where('login', $login)
			->where('deleted', 0);

		if($id !== null && $id !== ''){
			$chs->where('id != ?', $id);
		}

		return $chs->count() < 1;
	}

	public function getActiveUsersSum($minutes){
		if($num = $this->tbl->select('COUNT(*) AS num')
			->where('activity BETWEEN (NOW() - INTERVAL ' . $minutes . ' MINUTE) AND NOW()')
			->where('deleted', 0)
			->where('role != ?', 'super')
			->fetch()){
			return $num->num;
		}
		return 0;
	}
}