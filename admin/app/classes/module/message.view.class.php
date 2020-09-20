<?php

class MessageViewClass extends ViewElementClass{
	public function __construct($type = 'raw'){
		if($type !== NULL){
			$this->type = $type;
		}
	}
}