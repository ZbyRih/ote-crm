<?php
use Phinx\Migration\AbstractMigration;

class PlatbyDphCoef extends AbstractMigration{

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
		$this->table('tx_platby')
			->addColumn('dph_coef', 'string', [
			'length' => 16,
			'after' => 'msg'
		])
			->update();

		$this->execute('UPDATE tx_platby SET dph_coef = "0.1736" WHERE type = "z" AND DATE(`when`) < DATE("2019-09-01")');
		$this->execute('UPDATE tx_platby SET dph_coef = "0.17355371900826" WHERE type = "z" AND DATE(`when`) >= DATE("2019-09-01")');
	}
}
