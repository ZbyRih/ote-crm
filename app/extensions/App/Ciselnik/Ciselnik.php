<?php
namespace App\Extensions\App;

use App\Extensions\Interfaces\ITableFind;
use Cake\Collection\Collection;

class Ciselnik implements ITableFind{

	const ID = 'id', VALUE = 'value', VALUE2 = 'value2', VALUE3 = 'value3', NAZEV = 'nazev';

	/** @var [][] */
	private $indexes;

	/**
	 * @param Collection $items
	 */
	public function __construct(
		Collection $items)
	{
		$this->indexes[self::ID] = $items->indexBy(self::ID)->toArray();
		$this->indexes[self::VALUE] = $items->indexBy(self::VALUE)->toArray();
		$this->indexes[self::VALUE2] = $items->indexBy(self::VALUE2)->toArray();
		$this->indexes[self::VALUE3] = $items->indexBy(self::VALUE3)->toArray();
	}

	/**
	 * @param string $keyName
	 * @param string $valueName
	 * @return []
	 */
	public function getPairs(
		$keyName = 'value',
		$valueName = 'nazev')
	{
		return collection($this->indexes[self::ID])->indexBy($keyName)
			->extract($valueName)
			->toArray();
	}

	public function find(
		$id)
	{
		return $this->_byVal($id);
	}

	/**
	 * @param int $id
	 * @param mixed $default
	 * @param string $prop
	 * @return string
	 */
	public function byId(
		$id,
		$default = null,
		$prop = self::NAZEV)
	{
		if($e = $this->_byId($id)){
			return $e[$prop];
		}
		return $default;
	}

	/**
	 * @param int $id
	 * @param mixed $default
	 * @param string $prop
	 * @return CiselnikEntity|null
	 */
	public function byIdEntity(
		$id,
		$default = null,
		$prop = self::NAZEV)
	{
		return $this->_byId($id);
	}

	/**
	 * @param string $val
	 * @param mixed $default
	 * @param string $prop
	 * @return string
	 */
	public function byVal(
		$val,
		$default = null,
		$prop = self::NAZEV)
	{
		if($e = $this->_byVal($val)){
			return $e[$prop];
		}
		return $default;
	}

	/**
	 * @param string $val
	 * @return CiselnikEntity|null
	 */
	public function byValEntity(
		$val)
	{
		return $this->_byVal($val);
	}

	/**
	 * @param string $prop
	 * @param string $value
	 * @return CiselnikEntity
	 */
	public function byProp(
		$prop,
		$value)
	{
		if(!array_key_exists($prop, $this->indexes[$prop])){
			throw new \Exception('Prop of `' . $prop . '` don`t exist');
		}

		if(!array_key_exists($value, $this->indexes[$prop])){
			throw new \Exception('Item of `' . $prop . '` with value `' . $value . '` don`t exist');
		}

		return $this->indexes[$prop][$value];
	}

	/**
	 * @param int $id
	 * @return CiselnikEntity|null
	 */
	private function _byId(
		$id)
	{
		if(!array_key_exists($id, $this->indexes[self::ID])){
			return null;
		}

		return $this->indexes[self::ID][$id];
	}

	/**
	 * @param string $val
	 * @return CiselnikEntity|null
	 */
	private function _byVal(
		$val)
	{
		if(!array_key_exists($val, $this->indexes[self::VALUE])){
			return null;
		}
		return $this->indexes[self::VALUE][$val];
	}
}