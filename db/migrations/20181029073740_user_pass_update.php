<?php
use Phinx\Migration\AbstractMigration;

class UserPassUpdate extends AbstractMigration{

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
		$rows = $this->fetchAll('SELECT * FROM user');

		foreach($rows as $r){
			$hash = @password_hash($r['pass'], PASSWORD_BCRYPT, []); // @ is escalated to exception
			$this->execute('UPDATE user SET pass=\'' . $hash . '\' WHERE id = ' . $r['id']);
		}
	}
}
