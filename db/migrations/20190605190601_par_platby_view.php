<?php
use Phinx\Migration\AbstractMigration;

class ParPlatbyView extends AbstractMigration{

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
			'CREATE
				ALGORITHM = TEMPTABLE
				VIEW `par_platby` AS
				(SELECT
				  ppf.`id`, ppf.`faktura_id`, ppf.`suma`, p.`from_cu`, p.`vs`, p.`when`, p.`platba`
				FROM
				  `tx_pv_par_fz` AS ppf, `tx_platby` AS p
				WHERE ppf.`platba_id` = p.`platba_id`
				ORDER BY `when` DESC) ;
			');
	}
}
