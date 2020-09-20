<?php

class MSettings extends AdminJsonClass{

	public function __construct($input = []){
		$this->decode('settings');
	}
}
