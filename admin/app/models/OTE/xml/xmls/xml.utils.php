<?php

class XML_Utils{

	public static function date(
		$dt)
	{
		return date('Y-m-d H:i:s', strtotime($dt));
	}
}