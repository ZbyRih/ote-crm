<?php

namespace App\Models\Strategies\Ote;

class XmlUtils{

	public static function extractAttribute(
		$node,
		$key,
		$default = null)
	{
		return isset($node->attributes[$key]) ? (string) $node->attributes[$key] : $default;
	}

	public static function dateTimeToString(
		$in)
	{
		return date('Y-m-d H:i:s', strtotime($in));
	}
}