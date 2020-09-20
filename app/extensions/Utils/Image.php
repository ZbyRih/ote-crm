<?php
namespace App\Extensions\Utils\Helpers;

class Image extends \Nette\Utils\Image{

	public function toBase64(){
		return 'data:image/png;base64,' . base64_encode($this->toString(static::PNG, 9));
	}
}