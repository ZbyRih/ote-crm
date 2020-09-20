<?php

namespace App\Models\Orm\SmlOMs;

use Nextras\Orm\Repository\Repository;
use Nextras\Orm\Collection\ICollection;

/**
 * @method ICollection|Book[] getByFakSkupInYear()
 * @method ICollection|Book[] getByOmInYear()
 */
class SmlOMsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			SmlOMEntity::class
		];
	}

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return SmlOMEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}

	/**
	 * @param int $omId
	 * @param int $klientId
	 * @param \DateTimeInterface $from
	 * @param \DateTimeInterface $to
	 * @return NULL|SmlOMEntity[]
	 */
	public function getByOmIdAndRange(
		$omId,
		$klientId,
		\DateTimeInterface $from,
		\DateTimeInterface $to)
	{
		return $this->findBy([
			'odberMistId' => $omId,
			'klientId' => $klientId,
			'od<=' => $to,
			'do>=' => $from
		])
			->orderBy('id', ICollection::ASC)
			->fetchAll();
	}

	/**
	 * @param int $fakSkupId
	 * @param int $klientId
	 * @param \DateTimeInterface $from
	 * @param \DateTimeInterface $to
	 * @return NULL|SmlOMEntity[]
	 */
	public function getByFakSkupAndRange(
		$fakSkupId,
		$klientId,
		\DateTimeInterface $from,
		\DateTimeInterface $to)
	{
		return $this->findBy([
			'fakSkupId' => $fakSkupId,
			'klientId' => $klientId,
			'od<=' => $to,
			'do>=' => $from
		])
			->orderBy('id')
			->fetchAll();
	}
}