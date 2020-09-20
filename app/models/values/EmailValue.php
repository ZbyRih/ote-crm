<?php
namespace App\Models\Values;

use Nette\Utils\Validators;

class EmailValue{

	private $email;

	public function __construct($email){
		$email = trim($email);

		if(!$email){
			return;
		}

		if(!Validators::isEmail($email)){
			throw new \InvalidArgumentException('\'' . $email . '\' není validní email.');
		}

		$this->email = $email;
	}

	public function __toString(){
		if($this->email){
			return $this->email;
		}
		return '';
	}
}