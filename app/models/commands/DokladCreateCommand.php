<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\Orm\Orm;
use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Series\DokladSeries;
use App\Models\Strategies\Doklad\CreateDokladStrategy;
use App\Models\Strategies\Doklad\NezarazenaPlatbaException;

class DokladCreateCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var DokladSeries */
	private $serDoklad;

	/** @var PlatbaEntity */
	private $platba;

	public function __construct(
		Orm $orm,
		DokladSeries $serDoklad)
	{
		$this->orm = $orm;
		$this->serDoklad = $serDoklad;
	}

	/**
	 * @param PlatbaEntity $platba
	 */
	public function setPlatba(
		PlatbaEntity $platba)
	{
		$this->platba = $platba;
	}

	/**
	 * @throws NezarazenaPlatbaException
	 */
	public function execute()
	{
		$str = new CreateDokladStrategy();
		$str->setCislo(function ()
		{
			return $this->serDoklad->next($this->platba->when->format('Y'));
		});

		try{

			if($this->platba->hasDoklad()){
				return;
			}

			$doklad = $str->create($this->platba);

			$this->platba->doklad = $doklad;

			$this->orm->persist($this->platba);
		}catch(NezarazenaPlatbaException $e){
			throw $e;
		}
	}
}