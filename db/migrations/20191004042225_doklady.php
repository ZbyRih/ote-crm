<?php
use Phinx\Migration\AbstractMigration;

class Doklady extends AbstractMigration{

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
		$this->table('doklady')
			->addColumn('platba_id', 'integer')
			->addColumn('created', 'datetime')
			->addColumn('cislo', 'string', [
			'length' => 14
		])
			->addColumn('platba', 'decimal', [
			'precision' => 14,
			'scale' => 2
		])
			->addColumn('dph_coef', 'string', [
			'length' => 16
		])
			->addColumn('den_zdan_pln', 'date')
			->create();
	}
}
