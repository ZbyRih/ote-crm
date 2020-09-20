<?php
use Phinx\Migration\AbstractMigration;

class UserChange extends AbstractMigration{

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
	public function change(){
		$this->table('obe_users')
			->rename('user')
			->renameColumn('userid', 'id')
			->renameColumn('password', 'pass')
			->renameColumn('loginname', 'login')
			->renameColumn('username', 'jmeno')
			->renameColumn('lastaccess', 'activity')
			->renameColumn('rights', 'perms')
			->changeColumn('deleted', 'datetime', [
			'null' => true
		])
			->removeColumn('active')
			->removeColumn('session')
			->removeColumn('cookieid')
			->removeColumn('superuser')
			->save();

		$this->table('user')
			->changeColumn('deleted', 'datetime', [
			'null' => true
		])
			->save();

		$this->table('user_params')
			->addColumn('key', 'string', [
			'length' => 15
		])
			->addColumn('val', 'string', [
			'null' => true,
			'length' => 255
		])
			->create();

		$this->execute('UPDATE user SET role=\'sys\' WHERE id = 1');
		$this->execute('UPDATE user SET role=\'super\' WHERE id = 2');
		$this->execute('UPDATE user SET role=\'sell\' WHERE id = 4');
		$this->execute('UPDATE user SET role=\'admin\' WHERE id = 13');
		$this->execute('UPDATE user SET role=\'sell\' WHERE id = 14');
		$this->execute('UPDATE user SET role=\'ucto\' WHERE id = 15');
	}
}