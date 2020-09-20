<?php
use Phinx\Migration\AbstractMigration;

class SmlouvyZalohyView extends AbstractMigration{

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
			'CREATE ALGORITHM = TEMPTABLE VIEW `smlouvy_zalohy` AS
			(SELECT DISTINCT
				som.id,
				som.interval,
				som.od AS s_od,
				som.do AS s_do,
				som.`klient_id`,
				om.`address_id`,
				om.odber_mist_id,
				om.com,
				z.`zaloha_id`,
				z.`od` AS z_od,
				z.`do` AS z_do,
				z.`vyse`,
				z.`uhrazeno`
				FROM `tx_odber_mist` AS `om`
				INNER JOIN `tx_sml_om` AS `som` ON (om.odber_mist_id = som.odber_mist_id)
				INNER JOIN `tx_zalohy` AS `z` ON (om.odber_mist_id = som.odber_mist_id AND som.`klient_id` = z.`klient_id` AND z.`od` BETWEEN som.`od` AND som.`do`)
				);');
	}
}
