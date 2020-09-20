<?php
use Phinx\Migration\AbstractMigration;

class UserTable extends AbstractMigration{

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
		$t = $this->table('user');
		$t->addColumn('role', 'string', [
			'limit' => 20
		])
			->addColumn('login', 'string', [
			'limit' => 120
		])
			->addColumn('pass', 'string', [
			'limit' => 255
		])
			->addColumn('jmeno', 'string', [
			'limit' => 120
		])
			->addColumn('deleted', 'boolean', [
			'default' => 0
		])
			->addColumn('activity', 'datetime', [
			'null' => true
		])
			->addColumn('perms', 'json')
			->addColumn('token', 'string', [
			'limit' => 255,
			'null' => true
		])
			->addColumn('token_exp', 'datetime', [
			'null' => true
		])
			->save();
	}
}
