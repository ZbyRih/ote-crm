<?php
use Phinx\Migration\AbstractMigration;

class KlientBasicViewAlter2 extends AbstractMigration{

	/**
	 * Change Method.
	 * Write your reversible migrations using this method.
	 * More information on writing migrations is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 * createTable
	 * renameTable
	 * addColumn
	 * addCustomColumn
	 * renameColumn
	 * addIndex
	 * addForeignKey
	 * Any other destructive changes will result in an error when trying to
	 * rollback the migration.
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change()
	{
		$this->execute(
			'
			ALTER ALGORITHM = TEMPTABLE VIEW `klient_basic` AS
			(SELECT
			    k.klient_id AS id, IF (
			        kd.`kind` = 0, CONCAT (
			            kd.`firstname`, \' \', kd.`lastname`
			        ), kd.firm_name
			    ) AS odberatel, kd.`kind`, k.`fakturacni`, k.`deleted`, k.`disabled`, k.`active`
			FROM
			    `es_klients` AS `k`, `es_klient_details` AS `kd`
			WHERE k.`klient_id` = kd.`klient_detail_id`);
		');
	}
}
