<?php
use Phinx\Migration\AbstractMigration;

class Tags extends AbstractMigration{

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
		$this->table('tags')
			->addColumn('user_id', 'integer')
			->addColumn('name', 'string', [
			'length' => 25
		])
			->addColumn('color', 'string', [
			'length' => 6,
			'null' => true
		])
			->create();

		$this->table('tags_to_objects')
			->addColumn('tag_id', 'integer')
			->addColumn('type', 'string', [
			'length' => 1
		])
			->addColumn('o_id', 'integer')
			->create();
	}
}
