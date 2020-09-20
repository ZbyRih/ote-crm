<?php

namespace App\Extensions\Interfaces;

interface IEnumSet{

	public static function label($key);

	public static function labels();

	public static function values();
}