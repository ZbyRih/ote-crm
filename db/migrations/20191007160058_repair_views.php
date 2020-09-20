<?php
use Phinx\Migration\AbstractMigration;

class RepairViews extends AbstractMigration{

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
			'ALTER ALGORITHM = TEMPTABLE
    			VIEW `klient_address` AS (SELECT
  					k.`klient_id`, k.`deleted`, k.active, k.`disabled`, k.`owner_id`, k.`fakturacni`,
					kd.`firstname`, kd.`lastname`, kd.`kind`, kd.`firm_name`, kd.`organ`, kd.`ico`, `kd`.`cu`,
					`a`.`city`, `a`.`street`, `a`.`cp`, `a`.`co`
				FROM `es_klients` AS k, `es_klient_details` AS kd, `es_address` AS `a`
				WHERE `a`.`address_id` = k.`address_id`
	  				AND kd.`klient_detail_id` = k.`klient_detail_id`);');
	}
}
