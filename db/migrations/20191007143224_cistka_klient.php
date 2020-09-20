<?php
use Phinx\Migration\AbstractMigration;

class CistkaKlient extends AbstractMigration{

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
		$this->table('es_klients')
			->removeColumn('groupid')
			->removeColumn('password')
			->removeColumn('email')
			->removeColumn('regcode')
			->removeColumn('subscribecode')
			->removeColumn('unsubscribecode')
			->removeColumn('newpasswdtoken')
			->removeColumn('discount')
			->removeColumn('tags')
			->update();

		$this->table('es_klient_details')
			->removeColumn('description')
			->update();

		$this->table('es_klients_flags')
			->removeColumn('wholeseler')
			->removeColumn('sendnews')
			->removeColumn('short_reg')
			->update();

		$this->table('es_address')
			->removeColumn('country')
			->update();
	}
}
