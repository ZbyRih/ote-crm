<?php

namespace App\Extensions\Abstracts;

use App\Extensions\Utils\Arrays;
use App\Extensions\Interfaces\ITableFind;
use Cake\Collection\Collection;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class DatabaseDataNotFoundException extends \Exception{
}

/**
 *
 * @tracySkipLocation
 */
abstract class Table implements ITableFind{
	use \Nette\SmartObject;

	/** @var Context */
	private $database;

	/** @var string */
	protected $table = 'table';

	/** @var string */
	protected $pk = 'id';

	public function __construct(Context $database){
		$this->database = $database;
	}

	public function countBy($vs, $keys, $excludePk = false){
		$s = $this->table()->select($this->pk);

		$any = false;
		foreach(Arrays::toArray($keys) as $k){
			if(array_key_exists($k, $vs) && $vs[$k]){
				$s->where($k, $vs[$k]);
				$any = true;
			}
		}

		if(!$any){
			return true;
		}

		if($excludePk !== false){
			$s->where($this->pk . ' != ?', $excludePk);
		}

		return $s->count();
	}

	/**
	 * Inserts row in a table.
	 * @param array|\Traversable|Selection array($column => $value)|\Traversable|Selection for INSERT ... SELECT
	 * @return \Nette\Database\IRow|int|bool Returns IRow or number of affected rows for Selection or table without primary key
	 */
	public function insert($array){
		return $this->table()->insert($array);
	}

	public function update($id, $array){
		if(!$row = $this->find($id)){
			throw new DatabaseDataNotFoundException();
		}
		return $row->update($array);
	}

	public function delete($id){
		if($o = $this->find($id)){
			return $o->delete();
		}
		return false;
	}

	public function fetchPairs($key, $val, $conditions = []){
		$s = $this->select($key . ',' . $val);
		foreach($conditions as $k => $v){
			$s->where($k, $v);
		}
		return $s->fetchPairs($key, $val);
	}

	/**
	 *
	 * @param string|null $cols
	 * @return Selection
	 */
	public function select($cols = null){
		$s = $this->table();
		$s = $cols ? $s->select($cols) : $s;
		return $s;
	}

	/**
	 *
	 * @return ActiveRow[]
	 */
	public function all($cols = '*'){
		return $this->table()
			->select($cols)
			->fetchAll();
	}

	/**
	 *
	 * @param integer|string $id
	 * @return ActiveRow|null
	 */
	public function find($id){
		return $this->table()
			->select('*')
			->where($this->pk, $id)
			->fetch();
	}

	/**
	 *
	 * @param string $col
	 * @param mixed $val
	 * @return ActiveRow|null
	 */
	public function findOne($col, $val){
		return $this->table()
			->select('*')
			->where($col, $val)
			->fetch();
	}

	/**
	 *
	 * @param string $col
	 * @param mixed $val
	 * @return Selection
	 */
	public function findAll($col, $val = null){
		return $this->table()
			->select('*')
			->where(($val === null) ? $this->pk : $col, ($val === null) ? $col : $val);
	}

	/**
	 *
	 * @param Selection $select
	 * @param string $index - sloupec pro indexaci
	 * @return Collection
	 */
	public function fetchCollection($select, $index = 'id'){
		$c = collection($select->fetchAll());
		if(!$index){
			return $c;
		}
		return $c->indexBy($index);
	}

	/**
	 *
	 * @param string $how
	 * @return Selection
	 */
	public function count($how = null){
		return $this->table()->select(($how ? $how : 'COUNT(' . $this->pk . ') AS num'));
	}

	/**
	 *
	 * @param string $table
	 * @return Selection
	 */
	public function table($table = null){
		return $this->database->table(($table) ? $table : $this->table);
	}

	public function truncate($table = null){
		$this->database->query('TRUNCATE ' . $table ? $table : $this->table);
	}

	public function beginTransaction(){
		$this->database->beginTransaction();
	}

	public function commit(){
		$this->database->commit();
	}

	public function rollback(){
		$this->database->rollBack();
	}

	public function getTable(){
		return $this->table;
	}
}