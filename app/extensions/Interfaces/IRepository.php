<?php

namespace App\Extensions\Interfaces;

interface IRepository{

	public function get($id);

	public function save($data);
}