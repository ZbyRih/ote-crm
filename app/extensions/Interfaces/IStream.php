<?php

namespace App\Extensions\Interfaces;

interface IStream{

	public function put(
		$data);

	public function fetch(
		$length);

	public function dump();

	public function isEmpty();
}