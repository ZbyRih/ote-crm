<?php
use Phinx\Migration\AbstractMigration;

class RoleTable extends AbstractMigration{

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
	 * renameColumn
	 * addIndex
	 * addForeignKey
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function up(){
		$t = $this->table('role');
		$t->addColumn('role', 'string', [
			'limit' => 20
		])
			->addColumn('nazev', 'string', [
			'limit' => 30
		])
			->addColumn('home', 'string', [
			'limit' => 30
		])
			->addColumn('deleted', 'datetime', [
			'null' => true
		])
			->addColumn('super', 'boolean', [
			'default' => 0
		])
			->addColumn('perms', 'json')
			->save();
	}
}
