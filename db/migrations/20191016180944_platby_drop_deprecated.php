<?php
use Phinx\Migration\AbstractMigration;

class PlatbyDropDeprecated extends AbstractMigration{

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
		$pls = $this->fetchAll('SELECT platba_id AS id FROM tx_platby WHERE deprecated = 1');
		foreach($pls as $p){
			$this->execute('UPDATE tx_platby SET type = \'o\' WHERE platba_id = ' . $p['id'] . ';');
		}

		$this->table('tx_platby')
			->removeColumn('deprecated')
			->update();
	}
}
