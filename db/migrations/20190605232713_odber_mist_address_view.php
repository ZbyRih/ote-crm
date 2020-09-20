<?php
use Phinx\Migration\AbstractMigration;

class OdberMistAddressView extends AbstractMigration{

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
			'CREATE ALGORITHM = TEMPTABLE
	    	VIEW `odbermist_address` AS
			(SELECT o.`odber_mist_id`, o.`com`, o.`eic`, o.owner_id, o.deprecated, o.address_id , `a`.`city`, `a`.`street`, `a`.`cp`,`a`.`co`, `a`.`byt_cis`, `a`.`patro`
			FROM `tx_odber_mist` AS o, `es_address` AS `a`
			WHERE `a`.`address_id` = o.`address_id` ORDER BY o.com);
		');
	}
}
