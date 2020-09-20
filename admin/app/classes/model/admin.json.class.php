<?php

class AdminJsonClass extends OBE_Json{
	public function decode($file, $toArray = true){
		parent::decode(APP_DIR_OLD .'/config/json/' . $file . '.json');
	}
}