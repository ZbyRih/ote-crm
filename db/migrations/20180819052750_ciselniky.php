<?php
use Phinx\Migration\AbstractMigration;

class Ciselniky extends AbstractMigration{

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
		$this->table('ciselniky')
			->addColumn('group', 'string', [
			'length' => 20
		])
			->addColumn('nazev', 'string', [
			'length' => 35
		])
			->addColumn('value', 'string', [
			'length' => 60
		])
			->addColumn('value2', 'string', [
			'length' => 60,
			'null' => true
		])
			->addColumn('value3', 'string', [
			'length' => 60,
			'null' => true
		])
			->addColumn('deleted', 'boolean', [
			'default' => 0
		])
			->save();
	}
}
