<?php
use Phinx\Migration\AbstractMigration;

class HelperModulToResource extends AbstractMigration{

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
		$this->table('obe_help')
			->renameColumn('help_id', 'id')
			->update();

		$this->table('obe_help')
			->addColumn('resource', 'string', [
			'length' => 15
		])
			->update();

		$this->query('UPDATE obe_help SET resource = \'Klients\' WHERE module_id = 22');
		$this->query('UPDATE obe_help SET resource = \'Tags\' WHERE module_id = 40');

		$this->table('obe_help')
			->removeColumn('module_id')
			->update();
	}
}