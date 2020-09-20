<?php
use Phinx\Migration\AbstractMigration;

class SmlouvyZalohyViewAdresyOdberatel extends AbstractMigration{

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
			'CREATE ALGORITHM = TEMPTABLE VIEW `smlouvy_zalohy_adresy_odberatel` AS
			(SELECT DISTINCT
			  v.*,
			  CONCAT_WS(\', \', a.city, a.street, a.cp, a.co) AS adresa,
			  TRIM(BOTH \', \' FROM CONCAT(CONCAT(ka.firm_name, ka.firstname), \', \', ka.lastname)) AS odberatel
			FROM
			  `smlouvy_zalohy` AS `v`
			  LEFT JOIN `odbermist_address` AS `a`
			    ON (v.address_id = a.address_id)
			  LEFT JOIN `klient_address` AS `ka`
			    ON (v.klient_id = ka.klient_id));');
	}
}
