<?php

namespace App\Models\DTO;

use App\Models\Enums\InfoEnums;
use App\Models\Orm\Info\InfoEntity;

class InfoData{

	/** @var string */
	public $type;

	/** @var \DateTime */
	public $created;

	/** @var [] */
	private $data;

	public function __construct(
		$type)
	{
		$this->created = new \DateTime();
		$this->type = $type;
		$this->data = [];
	}

	public function addInfo(
		$msg)
	{
		$this->add($msg, InfoEnums::INFO);
	}

	public function addSuccess(
		$msg)
	{
		$this->add($msg, InfoEnums::SUCCESS);
	}

	public function addWarning(
		$msg)
	{
		$this->add($msg, InfoEnums::WARNING);
	}

	public function addError(
		$msg)
	{
		$this->add($msg, InfoEnums::ERROR);
	}

	/**
	 * @param string $msg
	 * @param string $type - InfoEnums
	 */
	public function add(
		$msg,
		$type = InfoEnums::INFO)
	{
		$this->data[] = (object) [
			'type' => $type,
			'msg' => (string) $msg
		];
	}

	public function clear()
	{
		$this->data = [];
	}

	public function hasData()
	{
		return !empty($this->data);
	}

	public function getEntity()
	{
		$ie = new InfoEntity();
		$ie->created = $this->created;
		$ie->type = $this->type;
		$ie->data = json_encode($this->data);

		return $ie;
	}
}