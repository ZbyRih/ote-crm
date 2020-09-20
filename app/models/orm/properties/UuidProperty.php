<?php

namespace App\Models\Orm;

use App\Extensions\Helpers\UuidHelper;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Entity\IPropertyContainer;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Ramsey\Uuid\Uuid;

class UuidProperty implements IPropertyContainer{

	/** @var Uuid */
	private $value;

	/** @var IEntity */
	private $entity;

	/** @var PropertyMetadata */
	private $propertyMetadata;

	public function __construct(
		IEntity $entity,
		PropertyMetadata $propertyMetadata)
	{
		$this->entity = $entity;
		$this->propertyMetadata = $propertyMetadata;
	}

	public function setRawValue(
		$value)
	{
		if(!$value){
			$this->value = UuidHelper::get();
		}else{
			$this->value = Uuid::fromBytes($value);
		}
	}

	public function getRawValue()
	{
		$this->value->getBytes();
	}

	public function &getInjectedValue()
	{
		return $this->value;
	}

	public function hasInjectedValue()
	{
		return $this->value !== NULL;
	}

	public function setInjectedValue(
		$value)
	{
		if($value !== $this->value){
			$this->entity->setAsModified($this->propertyMetadata->name);
		}
		$this->value = $value;
	}
}