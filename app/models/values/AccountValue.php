<?php

namespace App\Models\Values;

class AccountValue{

	/** @var string */
	private $pre;

	/** @var string */
	private $number;

	/** @var string */
	private $bank;

	/**
	 *
	 * @param string $account
	 */
	public function __construct(
		$account)
	{
		if(!is_string($account)){
			throw new \InvalidArgumentException('\'' . $account . '\' není validní string.');
		}

		if(!strpos($account, '/')){
			throw new \InvalidArgumentException('\'' . $account . '\' není validní číslo účtu.');
		}

		if(strpos($account, '-')){
			$els = explode('-', $account);
			$this->pre = trim(array_shift($els));
			$account = trim(array_shift($els));

			if(!is_numeric($this->pre)){
				throw new \InvalidArgumentException('\'' . $account . '\' není validní číslo účtu.');
			}
		}

		$els = explode('/', $account);
		$this->number = trim(array_shift($els));
		$this->bank = trim(array_shift($els));

		if(!is_numeric($this->number)){
			throw new \InvalidArgumentException('\'' . $account . '\' není validní číslo účtu.');
		}

		if(!is_numeric($this->bank)){
			throw new \InvalidArgumentException('\'' . $account . '\' není validní číslo banky.');
		}
	}

	public function toDelim()
	{
		return ($this->pre ? $this->pre . '-' : '') . $this->number;
	}

	public function toBank()
	{
		return $this->bank;
	}

	public function __toString()
	{
		return ($this->pre ? $this->pre . '-' : '') . $this->number . '/' . $this->bank;
	}
}