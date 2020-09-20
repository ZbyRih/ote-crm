<?php

namespace App\Extensions\Interfaces;

use Ramsey\Uuid\UuidInterface;

interface IRepositoryUuid extends IRepository{

	public function getByUuid(UuidInterface $uuid);
}